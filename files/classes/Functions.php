<?php
class Functions {
  public static function ObStart() {
    function minify_everything($buffer) {
        $buffer = preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/<!--(.|\s)*?-->/', '/\s+/'), array('>','<','\\1','', ' '), $buffer);
        return $buffer;
    }
    ob_start('ob_gzhandler');
    ob_start('minify_everything');
  }

  public static function LoadPage($variable) {
		$mysqli = Database::GetInstance();

		if (!$mysqli->connect_errno && Functions::IsLoggedIn()) {
			$player = Functions::GetPlayer();
			$data = json_decode($player['data']);

			if ($player['clanId'] > 0) {
				$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
			}
		}

    $page = explode('/', str_replace('-', '_', Functions::s($variable)));
    $path = '';

    if (isset($page[0])) {
      if ($page[0] == 'api') {
        $path = ROOT . 'api.php';
      } else if ($page[0] == 'cronjobs') {
				$path = CRONJOBS . $page[1] . '.php';
			} else {
        if (isset($player)) {
          if ($page[0] == 'company_select' && $player['factionId'] != 0) {
            $page[0] = 'home';
          } else if ($player['factionId'] == 0) {
            $page[0] = 'company_select';
          } else if ($page[0] == 'index') {
            $page[0] = 'home';
          } else if ($page[0] == 'clan' && isset($page[2]) && $page[2] == $player['clanId']) {
            $page[0] = 'clan';
            $page[1] = 'informations';
          }
        } else if ($page[0] != 'maintenance') {
          $page[0] = 'index';
        }

        $path = EXTERNALS . $page[0] . '.php';
      }
    }

    if (!file_exists($path)) {
      $path = EXTERNALS . 'error.php';
    }

    require_once($path);
  }

  public static function Register($username, $password, $password_confirm, $email) {
    $mysqli = Database::GetInstance();

		$username = $mysqli->real_escape_string($username);
		$password = $mysqli->real_escape_string($password);
    $password_confirm = $mysqli->real_escape_string($password_confirm);
		$email = $mysqli->real_escape_string($email);

    $json = [
      'inputs' => [
        'username' => ['validate' => 'valid', 'error' => 'Enter a valid username!'],
        'password' => ['validate' => 'valid', 'error' => 'Enter a valid password!'],
        'password_confirm' => ['validate' => 'valid', 'error' => 'Enter a valid password!'],
        'email' => ['validate' => 'valid', 'error' => 'Enter a valid e-mail address!'],
      ],
      'message' => ''
    ];

		if (!preg_match('/^[A-Za-z0-9_.]+$/', $username)) {
      $json['inputs']['username']['validate'] = 'invalid';
      $json['inputs']['username']['error'] = 'Your username is not valid.';
    }

    if (mb_strlen($username) < 4 || mb_strlen($username) > 20) {
      $json['inputs']['username']['validate'] = 'invalid';
      $json['inputs']['username']['error'] = 'Your username should be between 4 and 20 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 260) {
      $json['inputs']['email']['validate'] = 'invalid';
      $json['inputs']['email']['error'] = 'Your e-mail should be max 260 characters.';
    }

    if (mb_strlen($password) < 8 || mb_strlen($password) > 45) {
      $json['inputs']['password']['validate'] = 'invalid';
      $json['inputs']['password']['error'] = 'Your password should be between 8 and 45 characters.';
    }

    if ($password != $password_confirm) {
      $json['inputs']['password_confirm']['validate'] = 'invalid';
      $json['inputs']['password_confirm']['error'] = "Those passwords didn't match. Try again.";
    }

    if ($json['inputs']['username']['validate'] === 'valid' && $json['inputs']['password']['validate'] === 'valid' && $json['inputs']['password_confirm']['validate'] === 'valid' && $json['inputs']['email']['validate'] === 'valid') {
      if ($mysqli->query('SELECT userId FROM player_accounts WHERE username = "'.$username.'"')->num_rows <= 0) {
        $ip = Functions::GetIP();
        $sessionId = Functions::GetUniqueSessionId();
        $pilotName = $username;

        if ($mysqli->query('SELECT userId FROM player_accounts WHERE pilotName = "'.$pilotName.'"')->num_rows >= 1) {
          $pilotName = Functions::GetUniquePilotName($pilotName);
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

          $mysqli->query("INSERT INTO player_accounts (sessionId, username, pilotName, email, password, info, verification) VALUES ('".$sessionId."', '".$username."', '".$pilotName."', '".$email."',  '".password_hash($password, PASSWORD_DEFAULT)."', '".json_encode($info)."', '".json_encode($verification)."')");

          $userId = $mysqli->insert_id;

          $mysqli->query('INSERT INTO player_equipment (userId) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_settings (userId) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_titles (userID) VALUES ('.$userId.')');
          $mysqli->query('INSERT INTO player_skilltree (userID) VALUES ('.$userId.')');

					SMTP::SendMail($email, $username, 'E-mail verification', '<p>Hi '.$username.', <br>Click this link to activate your account: <a href="'.DOMAIN.'api/verify/'.$userId.'/'.$verification['hash'].'">Activate</a></p><p style="font-size:small;color:#666">—<br>You are receiving this because you registered to the '.SERVER_NAME.'.<br>If that was not your request, then you can ignore this email.<br>This is an automated message, please do not reply directly to this email.</p>');

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
    }

    return json_encode($json);
  }

	public static function Login($username, $password) {
		$mysqli = Database::GetInstance();

		$username = $mysqli->real_escape_string($username);
		$password = $mysqli->real_escape_string($password);

		$json = [
			'status' => false,
			'message' => '',
      'toastAction' => ''
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
          if(!isset($_COOKIE['send-link-again-button'])) {
            $json['toastAction'] = '<button id="send-link-again" class="btn-flat waves-effect waves-light toast-action">Send link again</button>';
          }

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

  public static function SendLinkAgain($username) {
    $mysqli = Database::GetInstance();

    $username = $mysqli->real_escape_string($username);

    $json = [
      'message' => ''
    ];

    if (!isset($_COOKIE['send-link-again-button'])) {
      $statement = $mysqli->query('SELECT userId, email, verification FROM player_accounts WHERE username = "'.$username.'"');
      $fetch = $statement->fetch_assoc();

      if ($statement->num_rows >= 1) {
        SMTP::SendMail($fetch['email'], $username, 'E-mail verification', '<p>Hi '.$username.', <br>Click this link to activate your account: <a href="'.DOMAIN.'api/verify/'.$fetch['userId'].'/'.json_decode($fetch['verification'])->hash.'">Activate</a></p><p style="font-size:small;color:#666">—<br>You are receiving this because you registered to the '.SERVER_NAME.'.<br>If that was not your request, then you can ignore this email.<br>This is an automated message, please do not reply directly to this email.</p>');

        $json['message'] = 'Activation link sent again.';
        setcookie('send-link-again-button', true, (time() + (120)), '/');
      } else {
        $json['message'] = 'Something went wrong!';
      }
    } else {
      $json['message'] = 'You need to wait 2 minutes for send link again.';
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
					$notOnlineOrOnlineAndInEquipZone = !Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) || (Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) && Socket::Get('IsInEquipZone', array('UserId' => $player['userId'], 'Return' => false)));

					if ($notOnlineOrOnlineAndInEquipZone) {
						$data->uridium -= 5000;

						if ($data->honor > 0) {
							$data->honor /= 2;
							$data->honor = round($data->honor);
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

        if ($json['status'] && Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false))) {
          Socket::Send('ChangeCompany', ['UserId' => $player['userId'], 'UridiumPrice' => 5000, 'HonorPrice' => $data->honor]);
        }
			}
		} else {
			$json['message'] = 'Something went wrong!';
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

  public static function DiplomacySearchClan($keywords) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
		$keywords = $mysqli->real_escape_string($keywords);

		$clans = [];

		foreach ($mysqli->query('SELECT * FROM server_clans WHERE id != '.$player['clanId'].' AND (tag like "%'.$keywords.'%" OR name like "%'.$keywords.'%")')->fetch_all(MYSQLI_ASSOC) as $key => $value) {
      $clans[$key]['id'] = $value['id'];
      $clans[$key]['tag'] = $value['tag'];
			$clans[$key]['name'] = $value['name'];
		}

		return json_encode($clans);
	}

  public static function RequestDiplomacy($clanId, $diplomacyType) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
		$clanId = $mysqli->real_escape_string($clanId);
    $diplomacyType = $mysqli->real_escape_string($diplomacyType);
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'message' => ''
		];

    if ($clanId != 0) {
      if ($clan != NULL) {
        if ($clan['leaderId'] == $player['userId']) {
          $toClan = $mysqli->query('SELECT * FROM server_clans WHERE id = "'.$clanId.'"')->fetch_assoc();

          if ($toClan != NULL && $clan['id'] != $toClan['id'] && in_array($diplomacyType, [1, 2, 3, 4])) {
            $mysqli->begin_transaction();

            try {
              $statement = $mysqli->query('SELECT id, diplomacyType FROM server_clan_diplomacy WHERE (senderClanId = '.$clan['id'].' AND toClanId = '.$toClan['id'].') OR (toClanId = '.$clan['id'].' AND senderClanId = '.$toClan['id'].')');
              $fetch = $statement->fetch_assoc();

              if ($statement->num_rows <= 0 || $diplomacyType == 4) {
                if ($diplomacyType == 3) {
                  $mysqli->query('INSERT INTO server_clan_diplomacy (senderClanId, toClanId, diplomacyType) VALUES ('.$clan['id'].', '.$toClan['id'].', '.$diplomacyType.')');

                  $declaredId = $mysqli->insert_id;

                  $mysqli->query('DELETE FROM server_clan_diplomacy_applications WHERE senderClanId = '.$clan['id'].' AND toClanId = '.$toClan['id'].'');

                  $json['message'] = 'You declared war on the '.$toClan['name'].' clan.';

                  $json['declared'] = [
                    'id' => $declaredId,
                    'date' => date('d.m.Y'),
                    'form' => ($diplomacyType == 1 ? 'Alliance' : ($diplomacyType == 2 ? 'NAP' : 'War')),
                    'clan' => [
                      'id' => $toClan['id'],
                      'name' => $toClan['name']
                    ]
                  ];

                  Socket::Send('StartDiplomacy', ['SenderClanId' => $clan['id'], 'TargetClanId' => $toClan['id'], 'DiplomacyType' => $diplomacyType]);
                } else {
                  if ($mysqli->query('SELECT id FROM server_clan_diplomacy_applications WHERE senderClanId = '.$clan['id'].' AND toClanId = '.$toClan['id'].'')->num_rows <= 0) {
                    $mysqli->query('INSERT INTO server_clan_diplomacy_applications (senderClanId, toClanId, diplomacyType) VALUES ('.$clan['id'].', '.$toClan['id'].', '.$diplomacyType.')');

                    $requestId = $mysqli->insert_id;

                    $json['message'] = 'Your diplomacy request was sent.';

                    $json['request'] = [
                      'id' => $requestId,
                      'date' => date('d.m.Y'),
                      'form' => ($diplomacyType == 1 ? 'Alliance' : ($diplomacyType == 2 ? 'NAP' : ($diplomacyType == 3 ? 'War' : 'End War'))),
                      'clan' => [
                        'name' => $toClan['name']
                      ]
                    ];

                  } else {
                    $json['message'] = 'You already submitted a diplomacy request to this clan.';
                  }
                }

              } else {
                $currentStatus = $fetch['diplomacyType'] == 1 ? 'Alliance' : ($fetch['diplomacyType'] == 2 ? 'NAP' : 'War');

                $json['message'] = 'You already have a diplomatic status with this clan.<br>Current status: '.$currentStatus.'';
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
        } else {
          $json['message'] = 'Only leaders are can sent a diplomacy request.';
        }
      } else {
        $json['message'] = 'Something went wrong!';
      }
    } else {
      $json['message'] = 'Please select a clan.';
    }

    return json_encode($json);
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

    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = "'.$clanId.'"')->fetch_assoc();

		if ($clan != NULL & $clan['recruiting'] && $mysqli->query('SELECT id FROM server_clan_applications WHERE clanId = '.$clanId.' AND userId = '.$player['userId'].'')->num_rows <= 0 && $player['clanId'] == 0) {
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

    if (mb_strlen($name) < 1 || mb_strlen($name) > 50) {
      $json['inputs']['name']['validate'] = 'invalid';
      $json['inputs']['name']['error'] = 'Your clan name should be between 1 and 50 characters.';
    }

    if (mb_strlen($tag) < 1 || mb_strlen($tag) > 4) {
      $json['inputs']['tag']['validate'] = 'invalid';
      $json['inputs']['tag']['error'] = 'Your clan tag should be between 1 and 4 characters.';
    }

		if (mb_strlen($description) > 16000) {
			$json['inputs']['description']['validate'] = 'invalid';
			$json['inputs']['description']['error'] = 'Your clan description should be max 16000 characters.';
		}

		if ($json['inputs']['name']['validate'] === 'valid' && $json['inputs']['tag']['validate'] === 'valid' && $json['inputs']['description']['validate'] === 'valid' && $player['clanId'] == 0) {
      if ($mysqli->query('SELECT id FROM server_clans WHERE name = "'.$name.'"')->num_rows <= 0) {
	      if ($mysqli->query('SELECT id FROM server_clans WHERE tag = "'.$tag.'"')->num_rows <= 0) {
					$mysqli->begin_transaction();

					try {
						$join_dates = [
							$player['userId'] => date('Y-m-d H:i:s')
						];

            $mysqli->query('DELETE FROM server_clan_applications WHERE userId = '.$player['userId'].'');

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
					$json['message'] = 'Another clan is already using this tag. Please select another one for your clan.';
				}
			} else {
				$json['message'] = 'Another clan is already using this name. Please select another one for your clan.';
			}
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

		if ($mysqli->query('SELECT id FROM server_clan_applications WHERE clanId = "'.$clanId.'" AND userId = '.$player['userId'].'')->num_rows >= 1) {
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

		if ($clan != NULL && $clan['leaderId'] == $player['userId']) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('DELETE FROM server_clans WHERE id = '.$player['clanId'].' AND leaderId = '.$player['userId'].'');

				$mysqli->query('UPDATE player_accounts SET clanId = 0 WHERE clanId = '.$clan['id'].'');

				$mysqli->query('DELETE FROM server_clan_applications WHERE clanId = '.$clan['id'].'');

        $mysqli->query('DELETE FROM server_clan_diplomacy WHERE senderClanId = '.$clan['id'].' OR toClanId = '.$clan['id'].'');

        $mysqli->query('DELETE FROM server_clan_diplomacy_applications WHERE senderClanId = '.$clan['id'].' OR toClanId = '.$clan['id'].'');

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

	public static function LeaveClan() {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		$notOnlineOrOnlineAndInEquipZone = !Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) || (Socket::Get('IsOnline', array('UserId' => $player['userId'], 'Return' => false)) && Socket::Get('IsInEquipZone', array('UserId' => $player['userId'], 'Return' => false)));

		if ($clan != NULL && $clan['leaderId'] != $player['userId']) {
			if ($notOnlineOrOnlineAndInEquipZone) {
				$mysqli->begin_transaction();

				try {
					$mysqli->query('UPDATE player_accounts SET clanId = 0 WHERE userId = '.$player['userId'].'');

					$join_dates = json_decode($clan['join_dates']);

					if (property_exists($join_dates, $player['userId'])) {
						unset($join_dates->{$player['userId']});
					}

					$mysqli->query("UPDATE server_clans SET join_dates = '".json_encode($join_dates)."' WHERE id = ".$clan['id']."");

					$json['status'] = true;

          Socket::Send('LeaveFromClan', ['UserId' => $player['userId']]);

					$mysqli->commit();
				} catch (Exception $e) {
					$json['message'] = 'An error occurred. Please try again later.';
					$mysqli->rollback();
				}

				$mysqli->close();
			} else {
				$json['message'] = 'You must be at your corporate HQ station to leave your Clan.';
			}
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

	public static function DismissClanMember($userId) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$userId = $mysqli->real_escape_string($userId);
    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = "'.$userId.'" AND clanId = "'.$clan['id'].'"')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan != NULL && $user != NULL && $clan['leaderId'] == $player['userId']) {
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
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = "'.$userId.'"')->fetch_assoc();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan != NULL && $user != NULL && $clan['leaderId'] == $player['userId'] && $user['clanId'] == 0) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('UPDATE player_accounts SET clanId = '.$clan['id'].' WHERE userId = '.$user['userId'].'');

				$join_dates = json_decode($clan['join_dates']);
				$join_dates->{$user['userId']} = date('Y-m-d H:i:s');

				$mysqli->query("UPDATE server_clans SET join_dates = '".json_encode($join_dates)."' WHERE id = ".$clan['id']."");

				$mysqli->query('DELETE FROM server_clan_applications WHERE userId = '.$user['userId'].'');

				$json['status'] = true;

        $json['acceptedUser'] = [
          'userId' => $user['userId'],
          'pilotName' => $user['pilotName'],
          'experience' => number_format(json_decode($user['data'])->experience),
          'rank' => [
            'id' => $user['rankId'],
            'name' => Functions::GetRankName($user['rankId'])
          ],
          'joined_date' => date('Y.m.d'),
          'company' => $user['factionId'] == 1 ? 'MMO' : ($user['factionId'] == 2 ? 'EIC' : 'VRU')
        ];

				$json['message'] = 'Clan joined: ' . $user['pilotName'];

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
		$user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = "'.$userId.'"')->fetch_assoc();
		$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();

		$json = [
			'status' => false,
			'message' => ''
		];

		if ($clan != NULL && $user != NULL && $clan['leaderId'] == $player['userId']) {
			$mysqli->begin_transaction();

			try {
				$mysqli->query('DELETE FROM server_clan_applications WHERE clanId = '.$clan['id'].' AND userId = '.$user['userId'].'');

				$json['status'] = true;
				$json['message'] = 'This user was declined: ' . $user['pilotName'];

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

  public static function CancelDiplomacyRequest($requestId) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
    $requestId = $mysqli->real_escape_string($requestId);

    $json = [
      'status' => false,
      'message' => ''
    ];

    if ($clan != NULL) {
      if ($clan['leaderId'] == $player['userId']) {
        $statement = $mysqli->query('SELECT id FROM server_clan_diplomacy_applications WHERE senderClanId = '.$player['clanId'].' AND id = "'.$requestId.'"');
        $fetch = $statement->fetch_assoc();

        if ($statement->num_rows >= 1) {
          $mysqli->begin_transaction();

          try {
            $mysqli->query('DELETE FROM server_clan_diplomacy_applications WHERE id = '.$fetch['id'].'');

            $json['status'] = true;
            $json['message'] = 'Your diplomatic request was withdrawn.';

            $mysqli->commit();
          } catch (Exception $e) {
            $json['message'] = 'An error occurred. Please try again later.';
            $mysqli->rollback();
          }

          $mysqli->close();
        } else {
          $json['message'] = 'Something went wrong!';
        }
      } else {
        $json['message'] = 'Only leaders are can cancel a diplomacy request.';
      }
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

  public static function DeclineDiplomacyRequest($requestId) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
    $requestId = $mysqli->real_escape_string($requestId);

    $json = [
      'status' => false,
      'message' => ''
    ];


    if ($clan != NULL) {
      if ($clan['leaderId'] == $player['userId']) {
        $statement = $mysqli->query('SELECT id, senderClanId FROM server_clan_diplomacy_applications WHERE toClanId = '.$player['clanId'].' AND id = "'.$requestId.'"');
        $fetch = $statement->fetch_assoc();

        if ($statement->num_rows >= 1) {
          $mysqli->begin_transaction();

          try {
            $mysqli->query('DELETE FROM server_clan_diplomacy_applications WHERE id = '.$fetch['id'].'');

            $senderClanName = $mysqli->query('SELECT name FROM server_clans WHERE id = '.$fetch['senderClanId'].'')->fetch_assoc()['name'];

            $json['status'] = true;
            $json['message'] = "You declined the ".$senderClanName." clan's diplomacy request.";

            $mysqli->commit();
          } catch (Exception $e) {
            $json['message'] = 'An error occurred. Please try again later.';
            $mysqli->rollback();
          }

          $mysqli->close();
        } else {
          $json['message'] = 'Something went wrong!';
        }
      } else {
        $json['message'] = 'Only leaders are can cancel a diplomacy request.';
      }
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

  public static function AcceptDiplomacyRequest($requestId) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
    $requestId = $mysqli->real_escape_string($requestId);

    $json = [
      'status' => false,
      'message' => ''
    ];

    if ($clan != NULL) {
      if ($clan['leaderId'] == $player['userId']) {
        $statement = $mysqli->query('SELECT * FROM server_clan_diplomacy_applications WHERE toClanId = '.$player['clanId'].' AND id = "'.$requestId.'"');
        $fetch = $statement->fetch_assoc();

        if ($statement->num_rows >= 1) {
          $mysqli->begin_transaction();

          try {
            $mysqli->query('DELETE FROM server_clan_diplomacy_applications WHERE id = '.$fetch['id'].'');

            if ($fetch['diplomacyType'] == 4) {
              $diplomacyId = $mysqli->query('SELECT id FROM server_clan_diplomacy WHERE (senderClanId = '.$fetch['senderClanId'].' AND toClanId = '.$fetch['toClanId'].') OR (toClanId = '.$fetch['senderClanId'].' AND senderClanId = '.$fetch['toClanId'].')')->fetch_assoc()['id'];

              $mysqli->query('DELETE FROM server_clan_diplomacy WHERE id = '.$diplomacyId.'');

              $json['warEnded'] = [
                'id' => $diplomacyId
              ];

              $json['status'] = true;
              $json['message'] = 'War ended';

              Socket::Send('EndDiplomacy', ['SenderClanId' => $fetch['senderClanId'], 'TargetClanId' => $fetch['toClanId']]);
            } else {
              $mysqli->query('INSERT INTO server_clan_diplomacy (senderClanId, toClanId, diplomacyType) VALUES ('.$fetch['senderClanId'].', '.$fetch['toClanId'].', '.$fetch['diplomacyType'].')');

              $diplomacyId = $mysqli->insert_id;

              $senderClanName = $mysqli->query('SELECT name FROM server_clans WHERE id = '.$fetch['senderClanId'].'')->fetch_assoc()['name'];

              $form = ($fetch['diplomacyType'] == 1 ? 'Alliance' : ($fetch['diplomacyType'] == 2 ? 'NAP' : 'War'));

              $json['acceptedRequest'] = [
                'id' => $diplomacyId,
                'name' => $senderClanName,
                'form' => $form,
                'diplomacyType' => $fetch['diplomacyType'],
                'date' => date('d.m.Y')
              ];

              $json['status'] = true;
              $json['message'] = "You accepted the ".$senderClanName." clan's diplomacy request.<br>New status: ".$form."";

              Socket::Send('StartDiplomacy', ['SenderClanId' => $fetch['senderClanId'], 'TargetClanId' => $fetch['toClanId'], 'DiplomacyType' => $fetch['diplomacyType']]);
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
      } else {
        $json['message'] = 'Only leaders are can cancel a diplomacy request.';
      }
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

  public static function EndDiplomacy($diplomacyId) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$player['clanId'].'')->fetch_assoc();
    $diplomacyId = $mysqli->real_escape_string($diplomacyId);

    $json = [
      'status' => false,
      'message' => ''
    ];

    if ($clan != NULL) {
      if ($clan['leaderId'] == $player['userId']) {
        $statement = $mysqli->query('SELECT * FROM server_clan_diplomacy WHERE id = "'.$diplomacyId.'"');
        $fetch = $statement->fetch_assoc();

        if ($statement->num_rows >= 1 && $fetch['diplomacyType'] != 3) {
          $mysqli->begin_transaction();

          try {
            $mysqli->query('DELETE FROM server_clan_diplomacy WHERE id = '.$diplomacyId.'');

            $json['status'] = true;
            $json['message'] = 'Diplomacy was ended.';

            Socket::Send('EndDiplomacy', ['SenderClanId' => $fetch['senderClanId'], 'TargetClanId' => $fetch['toClanId']]);

            $mysqli->commit();
          } catch (Exception $e) {
            $json['message'] = 'An error occurred. Please try again later.';
            $mysqli->rollback();
          }

          $mysqli->close();
        } else {
          $json['message'] = 'Something went wrong!';
        }
      } else {
        $json['message'] = 'Only leaders are can end a diplomacy.';
      }
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

  public static function GetUniqueSessionId() {
    $mysqli = Database::GetInstance();

		$sessionId = Functions::GenerateRandom(32);

    if ($mysqli->query('SELECT userId FROM player_accounts WHERE sessionId = "'.$sessionId.'"')->num_rows >= 1)
      $sessionId = GetUniqueSessionId();

		return $sessionId;
	}

	public static function VerifyEmail($userId, $hash) {
    $mysqli = Database::GetInstance();

		$userId = $mysqli->real_escape_string($userId);
		$hash = $mysqli->real_escape_string($hash);

		$message = '';

		if ($mysqli->query('SELECT userId FROM player_accounts WHERE userId = "'.$userId.'"')->num_rows >= 1) {
			$verification = json_decode($mysqli->query('SELECT verification FROM player_accounts WHERE userId = '.$userId.'')->fetch_assoc()['verification']);

			if (!$verification->verified) {
				if ($verification->hash === $hash) {
					$verification->verified = true;

					$mysqli->begin_transaction();

	        try {
	          $mysqli->query("UPDATE player_accounts SET verification = '".json_encode($verification)."' WHERE userId = ".$userId."");

	          $message = 'Your account is now verified.';

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

	public static function Buy($itemId, $amount) {
		$mysqli = Database::GetInstance();

		$player = Functions::GetPlayer();
		$itemId = $mysqli->real_escape_string($itemId);
    $amount = $mysqli->real_escape_string($amount);
    $shop = Functions::GetShop();

		$json = [
			'status' => false,
			'message' => ''
		];

		if (isset($shop['items'][$itemId]) && $shop['items'][$itemId]['active']) {
			$items = json_decode($mysqli->query('SELECT items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc()['items']);
			$data = json_decode($player['data']);

      $price = $shop['items'][$itemId]['price'];

      if ($shop['items'][$itemId]['amount'] && $amount <= 0) {
        $amount = 1;
      }

      if ($shop['items'][$itemId]['amount'] && $amount >= 1) {
        $price *= $amount;
      }

      if (($shop['items'][$itemId]['priceType'] == 'uridium' ? $data->uridium : $data->credits) >= $price) {
          $data->{($shop['items'][$itemId]['priceType'] == 'uridium' ? 'uridium' : 'credits')} -= $price;

          $status = false;

          if ($shop['items'][$itemId]['name'] == 'Apis') {
            if (!$items->apis) {
              $items->apis = true;
              $status = true;
            } else {
              $json['message'] = 'You already have an '.$shop['items'][$itemId]['name'].' Drone.';
            }
          } else if ($shop['items'][$itemId]['name'] == 'Zeus') {
            if (!$items->zeus) {
              $items->zeus = true;
              $status = true;
            } else {
              $json['message'] = 'You already have an '.$shop['items'][$itemId]['name'].' Drone.';
            }
          } else if ($shop['items'][$itemId]['name'] == 'Logdisk') {
            $items->skillTree->logdisks += $amount;
            $status = true;
          }

          if ($status) {
            $mysqli->begin_transaction();

						try {
							$mysqli->query("UPDATE player_accounts SET data = '".json_encode($data)."' WHERE userId = ".$player['userId']."");
							$mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."' WHERE userId = ".$player['userId']."");

							$json['newStatus'] = [
                'uridium' => number_format($data->uridium),
                'credits' => number_format($data->credits)
              ];

							if (Socket::Get('IsOnline', ['UserId' => $player['userId'], 'Return' => false])) {
								Socket::Send('BuyItem', ['UserId' => $player['userId'], 'ItemType' => $shop['items'][$itemId]['category'], 'DataType' => ($shop['items'][$itemId]['priceType'] == 'uridium' ? 0 : 1), 'Amount' => $price]);
							}

              $json['message'] = ''.$shop['items'][$itemId]['name'].' '.($amount != 0 ? '('.number_format($amount).')' : '').' purchased';

							$mysqli->commit();
						} catch (Exception $e) {
							$json['message'] = 'An error occurred. Please try again later.';
							$mysqli->rollback();
						}

						$mysqli->close();
          }
      } else {
        $json['message'] = "You don't have enough " . ($shop['items'][$itemId]['priceType'] == 'uridium' ? 'Uridium' : 'Credits');
      }
		} else {
			$json['message'] = 'Something went wrong!';
		}

		return json_encode($json);
	}

  public static function ChangePilotName($newPilotName) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
		$newPilotName = $mysqli->real_escape_string($newPilotName);

    $json = [
      'inputs' => [
        'pilotName' => ['validate' => 'valid', 'error' => 'Enter a valid pilot name!']
      ],
      'message' => ''
    ];

    if (mb_strlen($newPilotName) < 4 || mb_strlen($newPilotName) > 20) {
      $json['inputs']['pilotName']['validate'] = 'invalid';
      $json['inputs']['pilotName']['error'] = 'Your pilot name should be between 4 and 20 characters.';
    }

    if ($json['inputs']['pilotName']['validate'] === 'valid') {
      $oldPilotNames = json_decode($player['oldPilotNames']);

      if (count($oldPilotNames) <= 0 || ((new DateTime(date('d.m.Y H:i:s')))->diff(new DateTime(end($oldPilotNames)->date))->days >= 1) || $player['rankId'] == 21) {
        if ($mysqli->query('SELECT userId FROM player_accounts WHERE pilotName = "'.$newPilotName.'"')->num_rows <= 0) {
          $mysqli->begin_transaction();

          try {
            array_push($oldPilotNames, ['name' => $player['pilotName'], 'date' => date('d.m.Y H:i:s')]);

            $mysqli->query("UPDATE player_accounts SET pilotName = '".$newPilotName."', oldPilotNames = '".json_encode($oldPilotNames, JSON_UNESCAPED_UNICODE)."' WHERE userId = ".$player['userId']."");

            $json['message'] = 'Your Pilot name has been changed.';

            $mysqli->commit();
          } catch (Exception $e) {
            $message = 'An error occurred. Please try again later.';
            $mysqli->rollback();
          }

          $mysqli->close();
        } else {
          $json['message'] = 'This Pilot name is already in use.';
        }
      } else {
        $json['message'] = 'You can only rename your Pilot once every 24 hours. <br> (Your last name change: '.date('d.m.Y H:i', strtotime(end($oldPilotNames)->date)).')';
      }
    }

    return json_encode($json);
  }

  public static function ExchangeLogdisks() {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();

    $equipment = $mysqli->query('SELECT skill_points, items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc();
    $skillPoints = json_decode($equipment['skill_points']);
    $items = json_decode($equipment['items']);
    $requiredLogdisks = Functions::GetRequiredLogdisks((array_sum((array)json_decode($equipment['skill_points'])) + $items->skillTree->researchPoints) + 1);

    $json = [
      'message' => ''
    ];

    if ($items->skillTree->logdisks >= $requiredLogdisks && ((array_sum((array)$skillPoints) + $items->skillTree->researchPoints) < array_sum(array_column(Functions::GetSkills($skillPoints), 'maxLevel')))) {
      $items->skillTree->logdisks -= $requiredLogdisks;
      $items->skillTree->researchPoints++;

      $mysqli->begin_transaction();

      try {
        $mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."' WHERE userId = ".$player['userId']."");

        $json['newStatus'] = [
          'logdisks' => $items->skillTree->logdisks,
          'researchPoints' => $items->skillTree->researchPoints,
          'researchPointsMaxed' => ((array_sum((array)$skillPoints) + $items->skillTree->researchPoints) == array_sum(array_column(Functions::GetSkills($skillPoints), 'maxLevel'))),
          'requiredLogdisks' => Functions::GetRequiredLogdisks((array_sum((array)json_decode($equipment['skill_points'])) + $items->skillTree->researchPoints) + 1)
        ];

        $json['message'] = 'Log disks exchanged.';

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

  public static function ResetSkills() {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();

    $equipment = $mysqli->query('SELECT skill_points, items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc();
    $skillPoints = json_decode($equipment['skill_points']);
    $items = json_decode($equipment['items']);
    $data = json_decode($player['data']);

    $json = [
      'status' => false,
      'message' => ''
    ];

    $cost = Functions::GetResetSkillCost($items->skillTree->resetCount);
    if ($data->uridium >= $cost) {
      $data->uridium -= $cost;
      $items->skillTree->resetCount++;

      $items->skillTree->researchPoints += array_sum((array)$skillPoints);

      foreach ($skillPoints as $key => $value) {
        $skillPoints->$key = 0;
      }

      $mysqli->begin_transaction();

      try {
        $mysqli->query("UPDATE player_accounts SET data = '".json_encode($data)."' WHERE userId = ".$player['userId']."");

        $mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."', skill_points = '".json_encode($skillPoints)."' WHERE userId = ".$player['userId']."");

        $json['status'] = true;
        $json['message'] = 'Research points resetted.';

        if (Socket::Get('IsOnline', ['UserId' => $player['userId'], 'Return' => false])) {
					Socket::Send('ResetSkillTree', ['UserId' => $player['userId']]);
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

    return json_encode($json);
  }

  public static function UseResearchPoints($skill) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $skill = $mysqli->real_escape_string($skill);

    $equipment = $mysqli->query('SELECT skill_points, items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc();
    $skillPoints = json_decode($equipment['skill_points']);
    $items = json_decode($equipment['items']);

    $skills = Functions::GetSkills($skillPoints);

    $json = [
      'message' => ''
    ];

    if (array_key_exists($skill, $skills) && isset($skillPoints->{$skill}) && (!isset($skills[$skill]['baseSkill']) || (isset($skills[$skill]['baseSkill']) && $skills[$skills[$skill]['baseSkill']]['currentLevel'] == $skills[$skills[$skill]['baseSkill']]['maxLevel']))) {
      if ($items->skillTree->researchPoints >= 1 && $skillPoints->{$skill} < $skills[$skill]['maxLevel']) {
        $items->skillTree->researchPoints--;
        $skillPoints->{$skill}++;

        $mysqli->begin_transaction();

        try {
          $mysqli->query("UPDATE player_equipment SET items = '".json_encode($items)."', skill_points = '".json_encode($skillPoints)."' WHERE userId = ".$player['userId']."");

          $json['newStatus'] = [
            'researchPoints' => $items->skillTree->researchPoints,
            'currentLevel' => $skillPoints->{$skill},
            'usedResearchPoints' => array_sum((array)$skillPoints),
            'isMaxed' => $skillPoints->{$skill} == $skills[$skill]['maxLevel'],
            'tooltip' => Functions::GetSkillTooltip($skills[$skill]['name'], $skillPoints->{$skill}, $skills[$skill]['maxLevel'])
          ];

          if ($json['newStatus']['isMaxed'] && isset($skills[$skill]['nextSkill'])) {
            $json['newStatus']['nextSkill'] = $skills[$skill]['nextSkill'];
          }

          if (Socket::Get('IsOnline', ['UserId' => $player['userId'], 'Return' => false])) {
  					Socket::Send('UpgradeSkillTree', ['UserId' => $player['userId'], 'Skill' => $skill]);
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
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

  public static function GetShop() {
    return [
      'categories' => ['drones', 'extras'],
      'items' => [
        [
          'id' => 0,
          'category' => 'drones',
          'name' => 'Apis',
          'price' => 100000,
          'priceType' => 'uridium',
          'amount' => false,
          'image' => 'do_img/global/items/drone/apis-5_top.png',
          'active' => true
        ],
        [
          'id' => 1,
          'category' => 'drones',
          'name' => 'Zeus',
          'price' => 100000,
          'priceType' => 'uridium',
          'amount' => false,
          'image' => 'do_img/global/items/drone/zeus-5_top.png',
          'active' => true
        ],
        [
          'id' => 2,
          'category' => 'extras',
          'name' => 'Logdisk',
          'price' => 50,
          'priceType' => 'uridium',
          'amount' => true,
          'image' => 'do_img/global/items/resource/logfile_100x100.png',
          'active' => true
        ]
      ]
    ];
  }

  public static function GetResetSkillCost($resetCount) {
    $cost = 1000;

    for ($i = 0; $i < $resetCount; $i++) {
      $cost *= 2;
    }

    return $cost;
  }

  public static function GetSkillDescription($skill, $level) {
    $array = [
      'Engineering' => 'Lets your repair bots repair '.($level <= 1 ? '5' : ($level == 2 ? '10' : ($level == 3 ? '15' : ($level == 4 ? '20' : ($level == 5 ? '30' : '0'))))).'% more HP<br> per second',
      'Shield Engineering' => 'Increases your shield strength by '.($level <= 1 ? '4' : ($level == 2 ? '8' : ($level == 3 ? '12' : ($level == 4 ? '18' : ($level == 5 ? '25' : '0'))))).'%',
      'Detonation I' => 'Makes your mines cause '.($level <= 1 ? '7' : ($level == 2 ? '14' : 0)).'% more damage',
      'Detonation II' => 'Makes your mines cause '.($level <= 1 ? '21' : ($level == 2 ? '28' : ($level == 3 ? '50' : 0))).'% more damage',
      'Heat-seeking Missiles' => 'Increases hit probability of your rockets by '.($level <= 1 ? '1' : ($level == 2 ? '2' : ($level == 3 ? '4' : ($level == 4 ? '6' : ($level == 5 ? '10' : '0'))))).'%',
      'Rocket Fusion' => 'Makes your rockets cause '.($level <= 1 ? '2' : ($level == 2 ? '2' : ($level == 3 ? '4' : ($level == 4 ? '6' : ($level == 5 ? '10' : '0'))))).'% more damage',
      'Cruelty I' => 'Gives you '.($level <= 1 ? '4' : ($level == 2 ? '8' : 0)).'% more honor points',
      'Cruelty II' => 'Gives you '.($level <= 1 ? '12' : ($level == 2 ? '18' : ($level == 3 ? '25' : 0))).'% more honor points',
      'Explosives' => 'Increases the radius of mine explosions by '.($level <= 1 ? '4' : ($level == 2 ? '8' : ($level == 3 ? '12' : ($level == 4 ? '18' : ($level == 5 ? '25' : '0'))))).'%',
      'Luck I' => 'Gives you '.($level <= 1 ? '2' : ($level == 2 ? '4' : 0)).'% more bonus-box Uridium',
      'Luck II' => 'Gives you '.($level <= 1 ? '6' : ($level == 2 ? '8' : ($level == 3 ? '12' : 0))).'% more bonus-box Uridium'
    ];

    return $array[$skill];
  }

  public static function GetSkillTooltip($skillName, $currentLevel, $maxLevel) {
    return 'Name: <span style=\'color: #a4d3ef;\'>'.$skillName.'</span><br>Level: <span style=\'color: #a4d3ef;\'>'.$currentLevel.'/'.$maxLevel.'</span>'.($currentLevel != 0 ? '<br>Current Level: <span style=\'color: #a4d3ef;\'>'.Functions::GetSkillDescription($skillName, $currentLevel).'</span>' : '').''.($currentLevel != $maxLevel ? '<br>Next Level: <span style=\'color: #a4d3ef;\'>'.Functions::GetSkillDescription($skillName, $currentLevel + 1).'</span>' : '').'';
  }

  public static function GetSkills($skillPoints) {
    return [
      'engineering' => [
        'name' => 'Engineering',
        'currentLevel' => $skillPoints->engineering,
        'maxLevel' => 5
      ],
      'shieldEngineering' => [
        'name' => 'Shield Engineering',
        'currentLevel' => $skillPoints->shieldEngineering,
        'maxLevel' => 5
      ],
      'detonation1' => [
        'name' => 'Detonation I',
        'currentLevel' => $skillPoints->detonation1,
        'maxLevel' => 2,
        'nextSkill' => 'detonation2'
      ],
      'detonation2' => [
        'name' => 'Detonation II',
        'currentLevel' => $skillPoints->detonation2,
        'maxLevel' => 3,
        'baseSkill' => 'detonation1'
      ],
      'heatseekingMissiles' => [
        'name' => 'Heat-seeking Missiles',
        'currentLevel' => $skillPoints->heatseekingMissiles,
        'maxLevel' => 5
      ],
      'rocketFusion' => [
        'name' => 'Rocket Fusion',
        'currentLevel' => $skillPoints->rocketFusion,
        'maxLevel' => 5
      ],
      'cruelty1' => [
        'name' => 'Cruelty I',
        'currentLevel' => $skillPoints->cruelty1,
        'maxLevel' => 2,
        'nextSkill' => 'cruelty2'
      ],
      'cruelty2' => [
        'name' => 'Cruelty II',
        'currentLevel' => $skillPoints->cruelty2,
        'maxLevel' => 3,
        'baseSkill' => 'cruelty1'
      ],
      'explosives' => [
        'name' => 'Explosives',
        'currentLevel' => $skillPoints->explosives,
        'maxLevel' => 5
      ],
      'luck1' => [
        'name' => 'Luck I',
        'currentLevel' => $skillPoints->luck1,
        'maxLevel' => 2,
        'nextSkill' => 'luck2'
      ],
      'luck2' => [
        'name' => 'Luck II',
        'currentLevel' => $skillPoints->luck2,
        'maxLevel' => 3,
        'baseSkill' => 'luck1'
      ]
    ];
  }

  public static function ChangeVersion($version) {
    $mysqli = Database::GetInstance();

    $player = Functions::GetPlayer();
    $version = $mysqli->real_escape_string($version);

    $json = [
      'message' => ''
    ];

    if ($version === 'false' || $version === 'true') {
      $mysqli->begin_transaction();

      try {
        $mysqli->query('UPDATE player_accounts SET version = '.$version.' WHERE userId = '.$player['userId'].'');

        $json['message'] = 'Your version has been changed.';

        $mysqli->commit();
      } catch (Exception $e) {
        $message = 'An error occurred. Please try again later.';
        $mysqli->rollback();
      }

      $mysqli->close();
    } else {
      $json['message'] = 'Something went wrong!';
    }

    return json_encode($json);
  }

	public static function GetLevel($exp) {
		$lvl = 1;
		$expNext = 10000;

		while ($exp >= $expNext) {
				$expNext *= 2;
				$lvl++;
		}

		return $lvl;
	}

  public static function GetUniquePilotName($pilotName) {
    $mysqli = Database::GetInstance();

		$newPilotName = $pilotName .= Functions::GenerateRandom(4, true, false, false);

    if ($mysqli->query('SELECT userId FROM player_accounts WHERE pilotName = "'.$newPilotName.'"')->num_rows >= 1)
      $newPilotName = GetUniquePilotName($pilotName);

		return $newPilotName;
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
			$str .= substr($chars, rand(0, mb_strlen($chars) -1), 1);
			$c++;
		}

		return $str;
	}

  public static function s($input) {
    return htmlspecialchars(trim($input));
  }

  public static function IsLoggedIn() {
		$mysqli = Database::GetInstance();

    if (!MAINTENANCE && !$mysqli->connect_errno) {
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

  public static function GetRequiredLogdisks($sumPoints) {
      $array = array(
          '1' => '30',
          '2' => '63',
          '3' => '99',
          '4' => '139',
          '5' => '183',
          '6' => '231',
          '7' => '284',
          '8' => '342',
          '9' => '406',
          '10' => '477',
          '11' => '555',
          '12' => '641',
          '13' => '735',
          '14' => '839',
          '15' => '953',
          '16' => '1078',
          '17' => '1216',
          '18' => '1368',
          '19' => '1535',
          '20' => '1718',
          '21' => '1920',
          '22' => '2142',
          '23' => '2386',
          '24' => '2655',
          '25' => '2950',
          '26' => '3275',
          '27' => '3633',
          '28' => '4026',
          '29' => '4459',
          '30' => '4935',
          '31' => '5458',
          '32' => '6034',
          '33' => '6667',
          '34' => '7364',
          '35' => '8130',
          '36' => '8973',
          '37' => '9900',
          '38' => '10920',
          '39' => '12042',
          '40' => '13276',
          '41' => '14634',
          '42' => '16128',
          '43' => '17771',
          '44' => '19578',
          '45' => '21566',
          '46' => '23753',
          '47' => '26158',
          '48' => '28804',
          '49' => '31715',
          '50' => '34917'
      );

      return isset($array[$sumPoints]) ? $array[$sumPoints] : '0';
  }

	public static function GetPlayer() {
		$mysqli = Database::GetInstance();

    if (isset($_SESSION['account']['id'])) {
      $id = $mysqli->real_escape_string(Functions::s($_SESSION['account']['id']));
      return $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$id.'')->fetch_assoc();
    } else {
      return null;
    }
	}
}

?>
