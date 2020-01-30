<?php
$mysqli = Database::GetInstance();

$mysqli->begin_transaction();

$pushers = [];

try {
  foreach ($mysqli->query('SELECT * FROM log_player_kills WHERE pushing != 1') as $value) {
    $killer = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$value['killer_id'].'')->fetch_assoc();
    $target = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$value['target_id'].'')->fetch_assoc();

    if (json_decode($killer['info'])->registerIP == json_decode($target['info'])->registerIP) {
      if (!in_array($killer['userId'], $pushers)) {
        array_push($pushers, $killer['userId']);
      }

      $data = json_decode($killer['data']);

      $data->experience -= 51200;
      $data->honor -= 512;
      $data->uridium -= 512;

      $mysqli->query("UPDATE player_accounts SET data = '".json_encode($data)."' WHERE userId = ".$killer['userId']."");

      $mysqli->query('UPDATE log_player_kills SET pushing = 1 WHERE id = '.$value['id'].'');
    }
  }

  foreach ($pushers as $id) {
    $killer = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$id.'')->fetch_assoc();

    $current = new DateTime();
    $daysToAdd = 3;

    $statement = $mysqli->query('SELECT id FROM server_bans WHERE userId = '.$id.' AND reason = "Pushing" AND typeId = 1 AND ended = 1');

    if ($statement->num_rows == 1) {
      $daysToAdd = 7;
    } elseif ($statement->num_rows == 2) {
      $daysToAdd = 14;
    } elseif ($statement->num_rows == 3) {
      $daysToAdd = 30;
    } elseif ($statement->num_rows == 4) {
      $daysToAdd = 60;
    } elseif ($statement->num_rows == 5) {
      $daysToAdd = 90;
    } elseif ($statement->num_rows == 6) {
      $daysToAdd = 360;
    } elseif ($statement->num_rows >= 7) {
      $daysToAdd = 9999;
    }

    $current->add(new DateInterval("P{$daysToAdd}D"));
    $newTime = $current->format('Y-m-d H:i:s');

    $mysqli->query('INSERT INTO server_bans (userId, modId, reason, typeId, end_date) VALUES ('.$id.', 1, "Pushing", 1, "'.$newTime.'")');

    Socket::Send('KickPlayer', ['UserId' => $id, 'Reason' => 'You are banned, reason: Pushing']);

    echo $killer['pilotName'] . ' - '.$daysToAdd.' days<br>';
  }

  $mysqli->commit();
} catch (Exception $e) {
  $mysqli->rollback();
}

$mysqli->close();
?>
