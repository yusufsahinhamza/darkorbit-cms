<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (defined('ROOT')) {
	require ROOT . 'packages/PHPMailer/src/Exception.php';
	require ROOT . 'packages/PHPMailer/src/PHPMailer.php';
	require ROOT . 'packages/PHPMailer/src/SMTP.php';
}

class Functions {
  public static function ObStart()
  {
    function minify_everything($buffer) {
        $buffer = preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/<!--(.|\s)*?-->/', '/\s+/'), array('>','<','\\1','', ' '), $buffer);
        return $buffer;
    }
    ob_start('ob_gzhandler');
    ob_start('minify_everything');
  }

  public static function LoadPage($variable) {
		$mysqli = Database::GetInstance();

		if (Functions::IsLoggedIn()) {
			$player = Functions::GetPlayer();
			$data = json_decode($player['data']);
		}

    $page = explode('/', str_replace('-', '_', Functions::s($variable)));
    $path = '';

    if (isset($page[0])) {
      if ($page[0] == 'api') {
        $path = ROOT . 'api.php';
      } else {
				if (isset($player)) {
					$path = EXTERNALS . $page[0] . '.php';

					if ($player['factionId'] == 0) {
						$page[0] = 'company-select';
						$path = EXTERNALS . 'company_select.php';
					} else if ($page[0] == 'index') {
						$page[0] = 'home';
						$path = EXTERNALS . 'home.php';
					}
				} else {
					$path = EXTERNALS . 'index.php';
				}
      }
    }

    if(!file_exists($path)) {
      $path = EXTERNALS . 'error.php';
    }

    require_once($path);
  }

  public static function Register($username, $password, $email) {
    $mysqli = Database::GetInstance();

		$username = $mysqli->real_escape_string($username);
		$password = $mysqli->real_escape_string($password);
		$email = $mysqli->real_escape_string($email);

    $json = [
      'inputs' => [
        'username' => ['validate' => 'valid', 'error' => 'Enter a valid username!'],
        'password' => ['validate' => 'valid', 'error' => 'Enter a valid password!'],
        'email' => ['validate' => 'valid', 'error' => 'Enter a valid e-mail address!'],
      ],
      'message' => ''
    ];

		if (!preg_match('/^[A-Za-z0-9_.]+$/', $username)) {
      $json['inputs']['username']['validate'] = 'invalid';
      $json['inputs']['username']['error'] = 'Your username is not valid.';
    }

    if (strlen($username) < 4 || strlen($username) > 20) {
      $json['inputs']['username']['validate'] = 'invalid';
      $json['inputs']['username']['error'] = 'Your username should be between 4 and 20 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 260) {
      $json['inputs']['email']['validate'] = 'invalid';
      $json['inputs']['email']['error'] = 'Your e-mail should be max 260 characters.';
    }

    if (strlen($password) < 8 || strlen($password) > 45) {
      $json['inputs']['password']['validate'] = 'invalid';
      $json['inputs']['password']['error'] = 'Your password should be between 8 and 45 characters.';
    }

    if ($json['inputs']['username']['validate'] === 'valid' && $json['inputs']['password']['validate'] === 'valid' && $json['inputs']['email']['validate'] === 'valid') {
      $statement = $mysqli->query('SELECT userId FROM player_accounts WHERE username = "'.$username.'"');

      if ($statement->num_rows <= 0) {
        $ip = Functions::GetIP();
        $sessionId = Functions::GetUniqueSessionId();
        $shipName = $username;

        $statement = $mysqli->query('SELECT userId FROM player_accounts WHERE shipName = "'.$shipName.'"');

        if ($statement->num_rows >= 1) {
          $shipName = Functions::GetUniqueShipName($shipName);
        }

        $mysqli->begin_transaction();

        try {
          $info = [
            'lastIP' => $ip,
            'registerIP' => $ip,
            'registerDate' => date('d.m.Y H:i:s')
          ];

          $verification = [
            'verified' => false,
            'hash' => $sessionId
          ];

          $mysqli->query("INSERT INTO player_accounts (sessionId, username, shipName, email, password, info, verification) VALUES ('".$sessionId."', '".$username."', '".$shipName."', '".$email."',  '".password_hash($password, PASSWORD_DEFAULT)."', '".json_encode($info)."', '".json_encode($verification)."')");

          $userId = $mysqli->insert_id;

          $mysqli->query('INSERT INTO player_equipment (userId) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_settings (userId) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_titles (userID) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_skilltree (userID) VALUES ('.$userId.')');

					Functions::SendMail($email, $username, 'E-mail verification', 'Hi '.$username.', Click this link to activate your account: <a href="'.DOMAIN.'api/verify/'.$userId.'/'.$verification['hash'].'">Activate</a>');

          $json['message'] = 'You successfully registered, please verify your e-mail address.';

          $mysqli->commit();
        } catch (Exception $e) {
          $json['message'] = 'Something went wrong!';
          $mysqli->rollback();
        }

        $mysqli->close();

      } else {
        $json['message'] = 'This username is already taken.';
      }
    }

    return json_encode($json);
  }

	public static function Login($username, $password) {
		$mysqli = Database::GetInstance();

		$json = [
      'inputs' => [
        'username' => ['validate' => 'valid', 'error' => 'Enter a valid username!'],
        'password' => ['validate' => 'valid', 'error' => 'Enter a valid password!']
      ],
			'message' => ''
    ];

		if (!preg_match('/^[A-Za-z0-9_.]+$/', $username)) {
      $json['inputs']['username']['validate'] = 'invalid';
    }

    if (strlen($username) < 4 || strlen($username) > 20) {
      $json['inputs']['username']['validate'] = 'invalid';
    }

    if (strlen($password) < 8 || strlen($password) > 45) {
      $json['inputs']['password']['validate'] = 'invalid';
    }

		if ($json['inputs']['username']['validate'] === 'valid' && $json['inputs']['password']['validate'] === 'valid') {
			$statement = $mysqli->query('SELECT userId, password, verification FROM player_accounts WHERE username = "'.$username.'"');
			$fetch = $statement->fetch_assoc();

			if ($statement->num_rows >= 1) {
				if (password_verify($password, $fetch['password'])) {
					if (json_decode($fetch['verification'])->verified) {
						$sessionId = Functions::GenerateRandom(32);

						$_SESSION['account']['id'] = $fetch['userId'];
						$_SESSION['account']['session'] = $sessionId;

						$mysqli->begin_transaction();

		        try {
							$mysqli->query('UPDATE player_accounts SET sessionId = "'.$sessionId.'" WHERE userId = '.$fetch['userId'].'');

		          $mysqli->commit();
		        } catch (Exception $e) {
		          $json['message'] = 'Something went wrong!';
		          $mysqli->rollback();
		        }

		        $mysqli->close();

						$json['message'] = '1';
					} else {
						$json['message'] = 'This account is not verified, please verify it from your e-mail address.';
					}
				} else {
					$json['message'] = 'Wrong password.';
				}
			} else {
				$json['message'] = 'No account with this username/password combination was found.';
			}
		}

		return json_encode($json);
	}

	public static function CompanySelect($company) {
		$mysqli = Database::GetInstance();

		$json = ['message' => 'Something went wrong!'];

		if (Functions::IsLoggedIn()) {
			$player = Functions::GetPlayer();

			$factionId = 0;

			if ($company === 'mmo') {
				$factionId = 1;
			} else if ($company === 'eic') {
				$factionId = 2;
			} else if ($company === 'vru') {
				$factionId = 3;
			}

			if (!in_array($player['factionId'], [1, 2, 3]) && in_array($factionId, [1, 2, 3]) && $factionId !== 0) {
				$mysqli->begin_transaction();

				try {
					$mysqli->query('UPDATE player_accounts SET factionId = '.$factionId.' WHERE userId = '.$player['userId'].'');
					$json['message'] = '1';
					$mysqli->commit();
				} catch (Exception $e) {
					$json['message'] = 'Something went wrong!';
					$mysqli->rollback();
				}

				$mysqli->close();
			} else {
				$json['message'] = 'Something went wrong!';
			}
		}

		return json_encode($json);
	}

	public static function Logout() {
		if (isset($_SESSION['account'])) {
			unset($_SESSION['account']);
			session_destroy();
		}

		header('Location: '.DOMAIN.'');
	}

  public static function GetUniqueSessionId() {
    $mysqli = Database::GetInstance();

		$sessionId = Functions::GenerateRandom(32);

    $statement = $mysqli->query('SELECT userId FROM player_accounts WHERE sessionId = "'.$sessionId.'"');

    if ($statement->num_rows >= 1)
      $sessionId = GetUniqueSessionId();

		return $sessionId;
	}

	public static function VerifyEmail($userId, $hash) {
    $mysqli = Database::GetInstance();

		$userId = $mysqli->real_escape_string($userId);
		$hash = $mysqli->real_escape_string($hash);

		$message = '';

		$statement = $mysqli->query('SELECT userId FROM player_accounts WHERE userId = "'.$userId.'"');

		if ($statement->num_rows >= 1) {
			$verification = json_decode($mysqli->query('SELECT verification FROM player_accounts WHERE userId = '.$userId.'')->fetch_assoc()['verification']);

			if (!$verification->verified) {
				if ($verification->hash === $hash) {
					$verification->verified = true;

					$mysqli->begin_transaction();

	        try {
	          $mysqli->query("UPDATE player_accounts SET verification = '".json_encode($verification)."' WHERE userId = ".$userId."");
	          $message = 'You account is now verified.';
	          $mysqli->commit();
	        } catch (Exception $e) {
	          $message = 'Something went wrong!';
	          $mysqli->rollback();
	        }

	        $mysqli->close();

				} else {
					$message = 'Hash is not matches.';
				}
			} else {
				$message = 'This account is already verified.';
			}
		} else {
			$message = 'User not found.';
		}

		return $message;
	}

  public static function GetUniqueShipName($shipName) {
    $mysqli = Database::GetInstance();

		$newShipName = $shipName .= Functions::GenerateRandom(4, true, false, false);

    $statement = $mysqli->query('SELECT userId FROM player_accounts WHERE shipName = "'.$newShipName.'"');

    if ($statement->num_rows >= 1)
      $newShipName = GetUniqueShipName($shipName);

		return $newShipName;
	}

	public static function SendMail($email, $head, $subject, $message) {
		$mail = new PHPMailer(true);

		try {
		    $mail->isSMTP();
		    $mail->Host       = SMTP_HOST;
		    $mail->SMTPAuth   = true;
		    $mail->Username   = SMTP_USERNAME;
		    $mail->Password   = SMTP_PASSWORD;
		    $mail->SMTPSecure = 'ssl';
		    $mail->Port       = SMTP_PORT;

		    $mail->setFrom(SMTP_USERNAME, SERVER_NAME);
		    $mail->addAddress($email, SERVER_NAME . ' | ' . $head);

		    $mail->isHTML(true);
		    $mail->Subject = SERVER_NAME . ' | ' . $subject;
		    $mail->Body    = $message;

		    $mail->send();
		} catch (Exception $e) {
		    unset($e);
		}
	}

  public static function GetIP() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    	$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    	$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }

    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)) {
      $ip = $client;
    } else if(filter_var($forward, FILTER_VALIDATE_IP)) {
      $ip = $forward;
    } else {
      $ip = $remote;
    }

    return $ip;
	}

  public static function GenerateRandom($length, $numbers = true, $letters = true, $uppercase = true) {
		$chars = '';
		$chars .= ($numbers) ? '0123456789' : '';
		$chars .= ($uppercase) ? 'QWERTYUIOPASDFGHJKLLZXCVBNM' : '';
		$chars .= ($letters) ? 'qwertyuiopasdfghjklzxcvbnm' : '';

		$str = '';
		$c = 0;
		while ($c < $length){
			$str .= substr($chars, rand(0, strlen($chars) -1), 1);
			$c++;
		}

		return $str;
	}

  public static function s($input) {
    return htmlspecialchars(trim($input));
  }

  public static function IsLoggedIn() {
		$mysqli = Database::GetInstance();

		if (isset($_SESSION['account'])) {
			if (isset($_SESSION['account']['id'], $_SESSION['account']['session'])) {
				$id = $mysqli->real_escape_string(Functions::s($_SESSION['account']['id']));
				$fetch = $mysqli->query('SELECT sessionId FROM player_accounts WHERE userId = '.$id.'')->fetch_assoc();

				if ($fetch['sessionId'] === $_SESSION['account']['session']) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
  }

	public static function GetRankName($rankId) {
		$array = [
				'1' => 'Basic Space Pilot',
				'2' => 'Space Pilot',
				'3' => 'Chief Space Pilot',
				'4' => 'Basic Sergeant',
				'5' => 'Sergeant',
				'6' => 'Chief Sergeant',
				'7' => 'Basic Lieutenant',
				'8' => 'Lieutenant',
				'9' => 'Chief Lieutenant',
				'10' => 'Basic Captain',
				'11' => 'Captain',
				'12' => 'Chief Captain',
				'13' => 'Basic Major',
				'14' => 'Major',
				'15' => 'Chief Major',
				'16' => 'Basic Colonel',
				'17' => 'Colonel',
				'18' => 'Chief Colonel',
				'19' => 'Basic General',
				'20' => 'General',
				'21' => 'Administrator',
				'22' => 'Wanted'
		];

		return $array[$rankId];
	}

	public static function GetPlayer() {
		$mysqli = Database::GetInstance();
		$id = $mysqli->real_escape_string(Functions::s($_SESSION['account']['id']));
		return $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$id.'')->fetch_assoc();
	}
}

?>
