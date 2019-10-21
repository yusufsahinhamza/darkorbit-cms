<?php
$mysqli = Database::GetInstance();

$mysqli->begin_transaction();

try {
  foreach ($mysqli->query('SELECT * FROM player_accounts WHERE rankId != 21') as $value) {
    $data = json_decode($value['data']);
    $destructions = json_decode($value['destructions']);

    $rankPoints = 0;

    $rankPoints += ($data->experience / 100000);
    $rankPoints += ($data->honor / 100);
    $rankPoints += (Functions::GetLevel($data->experience) * 100);

    $registerDate = new DateTime(json_decode($value['info'])->registerDate);
    $daysSinceRegistration = (new DateTime(date('d.m.Y H:i:s')))->diff($registerDate)->days;

    $rankPoints += ($daysSinceRegistration * 6);
    $rankPoints += ($mysqli->query('SELECT baseShipId FROM server_ships WHERE shipID = '.$value['shipId'].'')->fetch_assoc()['baseShipId'] * 100);

    $rankPoints -= ($destructions->fpd * 100);
    $rankPoints -= ($destructions->dbe * 4);
    $rankPoints += ($destructions->de * 4);
    $rankPoints -= ($destructions->dbrz * 8);

    if ($rankPoints < 0) {
      $rankPoints = 0;
    }

    $rankPoints = round($rankPoints);

    $mysqli->query('UPDATE player_accounts SET rankPoints = '.$rankPoints.' WHERE userId = '.$value['userId'].'');
  }

  foreach ($mysqli->query('SELECT * FROM server_bans WHERE typeId = 1') as $value) {
    $mysqli->query('UPDATE player_accounts SET rankPoints = 10 WHERE userId = '.$value['userId'].'');
  }

  foreach ($mysqli->query('SELECT * FROM player_accounts WHERE rankId != 21 ORDER BY rankPoints DESC') as $key => $value) {
    $mysqli->query('UPDATE player_accounts SET rank = '.($key + 1).' WHERE userId = '.$value['userId'].'');
  }

  foreach ($mysqli->query('SELECT id FROM server_clans') as $value) {
    $rankPoints = 0;

    $sumRankpoints = $mysqli->query('SELECT SUM(rankPoints) as rankPoints, COUNT(userId) as userCount FROM player_accounts WHERE clanId = '.$value['id'].'')->fetch_assoc();

    $rankPoints = $sumRankpoints['rankPoints'];
    $rankPoints /= $sumRankpoints['userCount'];

    $rankPoints = round($rankPoints);

    $mysqli->query('UPDATE server_clans SET rankPoints = '.$rankPoints.' WHERE id = '.$value['id'].'');
  }

  foreach ($mysqli->query('SELECT * FROM server_clans ORDER BY rankPoints DESC') as $key => $value) {
    $mysqli->query('UPDATE server_clans SET rank = '.($key + 1).' WHERE id = '.$value['id'].'');
  }

  for ($i = 1; $i <= 3; $i++) {
    $rank = array(1 => 20,
    				2 => 12.29,
    				3 => 10,
    				4 => 9,
    				5 => 8,
    				6 => 7,
    				7 => 6,
    				8 => 5,
    				9 => 4.5,
    				10 => 4,
    				11 => 3.5,
    				12 => 3,
    				13 => 2.5,
    				14 => 2,
    				15 => 1.5,
    				16 => 1,
    				17 => 0.5,
    				18 => 0.1,
    				19 => 0.01
    			);

    $addition = 0;
    $userscount = ($mysqli->query('SELECT userId FROM player_accounts WHERE rankPoints >= 10 AND factionId = '.$i.' AND rankId != 21')->num_rows) - 1;
    $predictcount = 0;
    $userslist = ($mysqli->query('SELECT userId FROM player_accounts WHERE rankPoints >= 10 AND factionId = '.$i.' AND rankId != 21 ORDER BY rankPoints DESC')->fetch_all(MYSQLI_ASSOC));

    $rank = array_reverse($rank, true);

    $mysqli->query('UPDATE player_accounts SET rankId = 20 WHERE userId = '.$userslist[0]['userId'].'');
    unset($userslist[0]);

    foreach ($rank as $key => $value) {
    	$predict = $userscount / 100 * $value;

    	if ($predictcount < $userscount) {
    		$predictcount += ceil($predict);
    		$tmp = 0;

    		foreach ($userslist as $key2 => $value2) {
    				if ($tmp < $predict) {
              $mysqli->query('UPDATE player_accounts SET rankId = '.$key.' WHERE userId = '.$userslist[$key2]['userId'].'');

    					unset($userslist[$key2]);
    					$tmp += 1;
    				}
    		}
    	}

    	$addition += $value;
    }
  }

  $mysqli->commit();
} catch (Exception $e) {
  $mysqli->rollback();
}

$mysqli->close();
?>
