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
						$page[0] = 'company_select';
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

    if (!file_exists($path)) {
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
          $json['message'] = 'An error occurred. Please try again later.';
          $mysqli->rollback();
        }

        $mysqli->close();

      } else {
        $json['message'] = 'This username is already taken.';
      }
    } else {
    	$json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

	public static function Login($username, $password) {
		$mysqli = Database::GetInstance();

		$username = $mysqli->real_escape_string($username);
		$password = $mysqli->real_escape_string($password);

		$json = [
			'status' => false,
			'message' => ''
    ];

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

						$json['status'] = true;

						$mysqli->commit();
					} catch (Exception $e) {
						$json['message'] = 'An error occurred. Please try again later.';
						$mysqli->rollback();
					}

					$mysqli->close();

				} else {
					$json['message'] = 'This account is not verified, please verify it from your e-mail address.';
				}
			} else {
				$json['message'] = 'Wrong password.';
			}
		} else {
			$json['message'] = 'No account with this username/password combination was found.';
		}

		return json_encode($json);
	}

	public static function CompanySelect($company) {
		$mysqli = Database::GetInstance();

		$json = [
			'status' => false,
			'message' => ''
		];

		$player = Functions::GetPlayer();

		$factionId = 0;

		if ($company === 'mmo') {
			$factionId = 1;
		} else if ($company === 'eic') {
			$factionId = 2;
		} else if ($company === 'vru') {
			$factionId = 3;
		}

		if (in_array($factionId, [1, 2, 3], true) && $player['factionId'] != $factionId) {
			if (!in_array($player['factionId'], [1, 2, 3])) {
				$mysqli->begin_transaction();

				try {
					$mysqli->query('UPDATE player_accounts SET factionId = '.$factionId.' WHERE userId = '.$player['userId'].'');
					$json['status'] = true;
					$mysqli->commit();
				} catch (Exception $e) {
					$json['message'] = 'An error occurred. Please try again later.';
					$mysqli->rollback();
				}

				$mysqli->close();
			} else {
				$data = json_decode($player['data']);

				if ($data->uridium >= 5000) {
					$NotOnlineOrOnlineAndInEquipZone = !Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) || (Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) && Socket::Get('IsInEquipZone', array('UserId' => $player['userId'], 'Return' => false)));

					if ($NotOnlineOrOnlineAndInEquipZone) {
						$data->uridium -= 5000;

						if ($data->honor > 0) {
							$data->honor /= 2;
							$data->honor = floor($data->honor);
						}

						$mysqli->begin_transaction();

						try {
							$mysqli->query("UPDATE player_accounts SET factionId = ".$factionId.", data = '".json_encode($data)."' WHERE userId = ".$player['userId']."");

							$json['status'] = true;
							$mysqli->commit();
						} catch (Exception $e) {
							$json['message'] = 'An error occurred. Please try again later.';
							$mysqli->rollback();
						}

						$mysqli->close();
					} else {
						$json['message'] = 'Change of company is not possible. You must be at a location with a hangar facility!';
					}
				} else {
					$json['message'] = "You don't have enough Uridium.";
				}
			}
		} else {
			$json['message'] = 'Something went wrong!';
		}

		if ($json['status']) {
			Socket::Send('ChangeCompany', ['UserId' => $player['userId'], 'UridiumPrice' => 5000, 'HonorPrice' => $data->honor]);
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

	public static function SearchClan($keywords) {
    $mysqli = Database::GetInstance();

		$keywords = $mysqli->real_escape_string($keywords);

		$clans = [];

		foreach ($mysqli->query('SELECT * FROM server_clans WHERE tag like "%'.$keywords.'%" OR name like "%'.$keywords.'%"')->fetch_all(MYSQLI_ASSOC) as $key => $value) {
			$clans[$key]['id'] = $value['id'];
			$clans[$key]['members'] = count($mysqli->query('SELECT userId FROM player_accounts WHERE clanId = '.$value['id'].'')->fetch_all(MYSQLI_ASSOC));
			$clans[$key]['tag'] = $value['tag'];
			$clans[$key]['name'] = $value['name'];
			$clans[$key]['rank'] = $value['rank'];
			$clans[$key]['rankPoints'] = $value['rankPoints'];
		}

		return json_encode($clans);
	}

	public static function SendClanApplication($clanId, $text) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$clanId = $mysqli->real_escape_string($clanId);
		$text = $mysqli->real_escape_string($text);

		$json = [
			'status' => false,
			'message' => ''
		];

		$recruiting = $mysqli->query('SELECT recruiting FROM server_clans WHERE id = '.$clanId.'')->fetch_assoc()['recruiting'];
		$statement = $mysqli->query('SELECT id FROM server_clan_applications WHERE clanId = '.$clanId.' AND userId = '.$player['userId'].'');

		if ($recruiting && $statement->num_rows <= 0 && $player['clanId'] == 0) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('INSERT INTO server_clan_applications (clanId, userId, text) VALUES ('.$clanId.', '.$player['userId'].', "'.$text.'")');

				$json['status'] = true;
				$json['message'] = 'Your application was sent to the clan leader.';

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function FoundClan($name, $tag, $description) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$name = $mysqli->real_escape_string($name);
		$tag = $mysqli->real_escape_string($tag);
		$description = $mysqli->real_escape_string($description);

    $json = [
      'inputs' => [
        'name' => ['validate' => 'valid', 'error' => 'Enter a valid clan name!'],
        'tag' => ['validate' => 'valid', 'error' => 'Enter a valid clan tag!'],
        'description' => ['validate' => 'valid', 'error' => 'Enter a valid clan description!'],
      ],
			'status' => false,
      'message' => ''
    ];

    if (strlen($name) < 1 || strlen($name) > 50) {
      $json['inputs']['name']['validate'] = 'invalid';
      $json['inputs']['name']['error'] = 'Your clan name should be between 1 and 50 characters.';
    }

    if (strlen($tag) < 1 || strlen($tag) > 4) {
      $json['inputs']['tag']['validate'] = 'invalid';
      $json['inputs']['tag']['error'] = 'Your clan tag should be between 1 and 4 characters.';
    }

		if (strlen($description) > 16000) {
			$json['inputs']['description']['validate'] = 'invalid';
			$json['inputs']['description']['error'] = 'Your clan description should be max 16000 characters.';
		}

		if ($json['inputs']['name']['validate'] === 'valid' && $json['inputs']['tag']['validate'] === 'valid' && $json['inputs']['description']['validate'] === 'valid' && $player['clanId'] == 0) {
      $statement = $mysqli->query('SELECT id FROM server_clans WHERE name = "'.$name.'"');

      if ($statement->num_rows <= 0) {
				$statement = $mysqli->query('SELECT id FROM server_clans WHERE tag = "'.$tag.'"');

	      if ($statement->num_rows <= 0) {
					$mysqli->begin_transaction();

					try {
						$join_dates = [
							$player['userId'] => date('Y-m-d H:i:s')
						];

						$mysqli->query("INSERT INTO server_clans (name, tag, description, factionId, recruiting, leaderId, join_dates) VALUES ('".$name."', '".$tag."', '".$description."', ".$player['factionId'].", 1, ".$player['userId'].", '".json_encode($join_dates)."')");

						$clanId = $mysqli->insert_id;

						$mysqli->query('UPDATE player_accounts SET clanId = '.$clanId.' WHERE userId = '.$player['userId'].'');

						$json['status'] = true;

						Socket::Send('CreateClan', ['UserId' => $player['userId'], 'ClanId' => $clanId, 'FactionId' => $player['factionId'], 'Name' => $name, 'Tag' => $tag]);

						$mysqli->commit();
					} catch (Exception $e) {
						$json['message'] = 'An error occurred. Please try again later.';
						$mysqli->rollback();
					}

					$mysqli->close();
				} else {
					$json['message'] = 'This clan tag is already taken.';
				}
			} else {
				$json['message'] = 'This clan name is already taken.';
			}
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function WithdrawPendingApplication($clanId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$clanId = $mysqli->real_escape_string($clanId);

		$json = [
			'status' => false,
			'message' => ''
		];

		$statement = $mysqli->query('SELECT id FROM server_clan_applications WHERE clanId = '.$clanId.' AND userId = '.$player['userId'].'');

		if ($statement->num_rows >= 1) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('DELETE FROM server_clan_applications WHERE clanId = '.$clanId.' AND userId = '.$player['userId'].'');

				$json['status'] = true;
				$json['message'] = 'Application deleted.';

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function DeleteClan() {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan !== NULL && $clan['leaderId'] == $player['userId']) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('DELETE FROM server_clans WHERE id = '.$player['clanId'].' AND leaderId = '.$player['userId'].'');
				$mysqli->query('UPDATE player_accounts SET clanId = 0 WHERE clanId = '.$clan['id'].'');

				$json['status'] = true;

				Socket::Send('DeleteClan', ['ClanId' => $clan['id']]);

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function DismissClanMember($userId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$userId = $mysqli->real_escape_string($userId);
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$userId.'')->fetch_assoc();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan !== NULL && $user !== NULL && $clan['leaderId'] == $player['userId'] && $user['clanId'] != 0) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('UPDATE player_accounts SET clanId = 0 WHERE userId = '.$user['userId'].'');

				$join_dates = json_decode($clan['join_dates']);

				if (property_exists($join_dates, $user['userId'])) {
					unset($join_dates->{$user['userId']});
				}

				$mysqli->query("UPDATE server_clans SET join_dates = '".json_encode($join_dates)."' WHERE id = ".$clan['id']."");

				$json['status'] = true;
				$json['message'] = 'Member deleted.';

				Socket::Send('LeaveFromClan', array('UserId' => $user['userId']));

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function AcceptClanApplication($userId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$userId = $mysqli->real_escape_string($userId);
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$userId.'')->fetch_assoc();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => '',
			'acceptedUser' => [
				'userId' => $user['userId'],
				'shipName' => $user['shipName'],
				'experience' => number_format(json_decode($user['data'])->experience),
				'rank' => [
					'id' => $user['rankID'],
					'name' => Functions::GetRankName($user['rankID'])
				],
				'joined_date' => date('Y.m.d'),
				'company' => $user['factionId'] == 1 ? 'MMO' : ($user['factionId'] == 2 ? 'EIC' : 'VRU')
			]
		];

		if ($clan !== NULL && $user !== NULL && $clan['leaderId'] == $player['userId'] && $user['clanId'] == 0) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('UPDATE player_accounts SET clanId = '.$clan['id'].' WHERE userId = '.$user['userId'].'');

				$join_dates = json_decode($clan['join_dates']);
				$join_dates->{$user['userId']} = date('Y-m-d H:i:s');

				$mysqli->query("UPDATE server_clans SET join_dates = '".json_encode($join_dates)."' WHERE id = ".$clan['id']."");

				$mysqli->query('DELETE FROM server_clan_applications WHERE userId = '.$user['userId'].'');

				$json['status'] = true;
				$json['message'] = 'Clan joined: ' . $user['shipName'];

				if (Socket::Get('IsOnline', ['UserId' => $user['userId'], 'Return' => false])) {
					Socket::Send('JoinToClan', ['UserId' => $user['userId'], 'ClanId' => $clan['id']]);
				}

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function DeclineClanApplication($userId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$userId = $mysqli->real_escape_string($userId);
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$userId.'')->fetch_assoc();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan !== NULL && $user !== NULL && $clan['leaderId'] == $player['userId'] && $user['clanId'] == 0) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('DELETE FROM server_clan_applications WHERE clanId = '.$clan['id'].' AND userId = '.$user['userId'].'');

				$json['status'] = true;
				$json['message'] = 'This user was declined: ' . $user['shipName'];

				$mysqli->commit();
			} catch (Exception $e) {
				$json['message'] = 'An error occurred. Please try again later.';
				$mysqli->rollback();
			}

			$mysqli->close();
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
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
	          $message = 'An error occurred. Please try again later.';
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

	public static function Buy($itemId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$itemId = $mysqli->real_escape_string($itemId);

		$json = [
			'status' => false,
			'message' => ''
		];

		if (in_array($itemId, [1,2])) {
			$items = json_decode($mysqli->query('SELECT items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc()['items']);
			$data = json_decode($player['data']);

			if ($itemId == 1) {
				if (!$items->apis) {
					if ($data->uridium >= 100000) {
						$data->uridium -= 100000;
						$items->apis = true;

						$mysqli->begin_transaction();

						try {
							$mysqli->query("UPDATE player_accounts SET data = '".json_encode($data)."' WHERE userId = ".$player['userId']."");
							$mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."' WHERE userId = ".$player['userId']."");

							$json['status'] = true;

							$json['uridium'] = number_format($data->uridium);

							$json['message'] = 'Drone Apis purchased';

							if (Socket::Get('IsOnline', ['UserId' => $player['userId'], 'Return' => false])) {
								Socket::Send('BuyItem', ['UserId' => $player['userId'], 'ItemType' => 'drone', 'DataType' => 0, 'Amount' => 100000]);
							}

							$mysqli->commit();
						} catch (Exception $e) {
							$json['message'] = 'An error occurred. Please try again later.';
							$mysqli->rollback();
						}

						$mysqli->close();
					} else {
						$json['message'] = "You don't have enough Uridium.";
					}
				} else {
					$json['message'] = 'You already have an Apis Drone.';
				}
			} else if ($itemId == 2) {
				if (!$items->zeus) {
					if ($data->uridium >= 100000) {
						$data->uridium -= 100000;
						$items->zeus = true;

						$mysqli->begin_transaction();

						try {
							$mysqli->query("UPDATE player_accounts SET data = '".json_encode($data)."' WHERE userId = ".$player['userId']."");
							$mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."' WHERE userId = ".$player['userId']."");

							$json['status'] = true;

							$json['uridium'] = number_format($data->uridium);

							$json['message'] = 'Drone Zeus purchased';

							if (Socket::Get('IsOnline', ['UserId' => $player['userId'], 'Return' => false])) {
								Socket::Send('BuyItem', ['UserId' => $player['userId'], 'ItemType' => 'drone', 'DataType' => 0, 'Amount' => 100000]);
							}

							$mysqli->commit();
						} catch (Exception $e) {
							$json['message'] = 'An error occurred. Please try again later.';
							$mysqli->rollback();
						}

						$mysqli->close();
					} else {
						$json['message'] = "You don't have enough Uridium.";
					}
				} else {
					$json['message'] = 'You already have an Zeus Drone.';
				}
			}

		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
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
