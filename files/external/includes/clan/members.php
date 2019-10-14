<div class="card white-text grey darken-4 padding-15">
  <h6>CLAN MEMBERS</h6>

  <div id="members" class="row">
    <?php foreach ($mysqli->query('SELECT * FROM player_accounts WHERE clanId = '.$clan['id'].'')->fetch_all(MYSQLI_ASSOC) as $value) { ?>
    <div class="col s12">
      <div id="user-<?php echo $value['userId']?>" class="card white-text grey darken-3 padding-5">
        <div class="row">
          <div class="col s4">
            <h6><?php echo $value['pilotName']; ?></h6>
            <p>EP: <?php echo number_format(json_decode($value['data'])->experience); ?></p>
            <p>Rank: <img src="<?php echo DOMAIN; ?>img/ranks/rank_<?php echo $value['rankId']; ?>.png"> <?php echo Functions::GetRankName($value['rankId']); ?></p>
          </div>
          <div class="col s4">
            <p>Joined: <?php echo date('Y.m.d', strtotime(json_decode($clan['join_dates'])->{$value['userId']})); ?></p>
            <p>Function: <?php echo ($value['userId'] == $clan['leaderId'] ? 'Clan leader' : '-'); ?></p>
            <p>Position: <?php echo (Socket::Get('IsOnline', array('UserId' => $value['userId'], 'Return' => false)) ? Socket::Get('GetPosition', array('UserId' => $value['userId'])) : ($value['factionId'] == 1 ? '4-1' : ($value['factionId'] == 2 ? '4-2' : '4-3'))); ?></p>
          </div>
          <div class="col s4">
            <p>Company: <?php echo ($value['factionId'] == 1 ? 'MMO' : ($value['factionId'] == 2 ? 'EIC' : 'VRU')); ?></p>
            <?php if ($clan['leaderId'] == $player['userId'] && $value['userId'] == $clan['leaderId']) { ?>
            <a class="delete-clan btn grey darken-2 waves-effect waves-light s6 modal-trigger" href="#modal2">DELETE</a>
            <?php } else if ($clan['leaderId'] == $player['userId'] && $value['userId'] != $clan['leaderId']) { ?>
              <a data-user-id="<?php echo $value['userId']?>" class="dismiss-member btn grey darken-2 waves-effect waves-light s6 modal-trigger" href="#modal1">DISMISS CLAN MEMBER</a>
            <?php } else if ($clan['leaderId'] != $player['userId'] && $value['userId'] == $player['userId']) { ?>
              <a class="leave-clan btn grey darken-2 waves-effect waves-light s6 modal-trigger" href="#modal3">LEAVE CLAN</a>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>

  <?php
  $array = $mysqli->query('SELECT * FROM server_clan_applications WHERE clanId = '.$clan['id'].'')->fetch_all(MYSQLI_ASSOC);

  if (count($array) >= 1) {
  ?>

  <h6>APPLICATIONS</h6>

  <div id="applications" class="row">
    <?php
    foreach ($array as $value) {
      $user = $mysqli->query('SELECT * FROM player_accounts WHERE userId = '.$value['userId'].'')->fetch_assoc();
      $userData = json_decode($user['data']);
    ?>
    <div class="col s12">
      <div id="application-user-<?php echo $user['userId']?>" class="card white-text grey darken-3 padding-5">
        <div class="row">
          <div class="col s4">
            <h6><?php echo $user['pilotName']; ?></h6>
            <p>EP: <?php echo number_format($userData->experience); ?></p>
          </div>
          <div class="col s4">
            <p>Level: <?php echo Functions::GetLevel($userData->experience); ?></p>
            <p>Company: <?php echo ($user['factionId'] == 1 ? 'MMO' : ($user['factionId'] == 2 ? 'EIC' : 'VRU')); ?></p>
          </div>
          <div class="col s4">
            <p>Date: <?php echo date('Y.m.d', strtotime($value['date'])); ?></p>
            <?php if ($clan['leaderId'] == $player['userId'] && $value['userId'] != $clan['leaderId']) { ?>
            <a data-user-id="<?php echo $user['userId']; ?>" data-user-name="<?php echo $user['pilotName']; ?>" data-user-text="<?php echo $value['text']; ?>" class="view-application btn grey darken-2 waves-effect waves-light s6 modal-trigger" href="#modal">VIEW</a>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
</div>

<?php if ($clan['leaderId'] == $player['userId']) { ?>
<div id="modal" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h4>Application</h4>
    <h6>%user_name%</h6>
    <p>%user_text%</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a id="decline" class="modal-close waves-effect waves-light btn grey darken-2">Decline</a>
    <a id="accept" class="modal-close waves-effect waves-light btn grey darken-3">Accept</a>
  </div>
</div>

<div id="modal1" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <p>Dismiss clan member</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
    <a id="confirm-dismiss-member" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
  </div>
</div>

<div id="modal2" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <p>Do you really want to delete this clan?</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
    <a id="confirm-delete-clan" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
  </div>
</div>

<?php } else { ?>

<div id="modal3" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <p>Do you really want to leave this clan?</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
    <a id="confirm-leave-clan" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
  </div>
</div>

<?php } ?>
