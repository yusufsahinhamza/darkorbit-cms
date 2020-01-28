<?php
$clanId = isset($page[2]) && preg_match('/^[1-9][0-9]*$/', $page[2]) && $page[2] >= 1 ? Functions::s(intval($page[2])) : 0;
$clanId = $mysqli->real_escape_string($clanId);

$clan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$clanId.'')->fetch_assoc();

$pendingAlready = $mysqli->query('SELECT id FROM server_clan_applications WHERE clanId = '.$clanId.' AND userId = '.$player['userId'].'')->num_rows >= 1;
?>
<div class="card white-text grey darken-4  padding-15">
  <?php if ($clan === NULL) { ?>
    <h5>Clan not found!</h5>
  <?php } else { ?>
  <div class="row">
    <div class="col s6">
      <img src="<?php echo DOMAIN; ?>img/clans/clanlogo_default.jpg">
      <div class="inline-right">
        <p>Name: <?php echo $clan['name']; ?></p>
        <p>Tag: <?php echo $clan['tag']; ?></p>
        <p>Founding Date: <?php echo date('Y.m.d', strtotime($clan['date'])); ?></p>
        <p>Clan Leader: <?php echo $mysqli->query('SELECT pilotName FROM player_accounts where userId = '.$clan['leaderId'].'')->fetch_assoc()['pilotName']; ?></p>
      </div>
    </div>
    <div class="col s6">
      <p>Number of members: <?php echo count($mysqli->query('SELECT userId FROM player_accounts WHERE clanId = '.$clan['id'].'')->fetch_all(MYSQLI_ASSOC)); ?></p>
      <p>Clan Rank: <?php echo $clan['rank']; ?></p>
      <p>Company: <?php echo ($clan['factionId'] == 0 ? 'All' : ($clan['factionId'] == 1 ? 'MMO' : ($clan['factionId'] == 2 ? 'EIC' : 'VRU'))); ?></p>
      <p>Recruiting: <?php echo ($clan['recruiting'] ? 'Recruiting' : 'Closed'); ?></p>
    </div>
    <div class="col s6" style="margin-top: 25px;">
      <p><?php echo $clan['description']; ?></p>
    </div>
    <?php if ($player['clanId'] == 0) { ?>
    <div class="col s6" style="margin-top: 25px;">
      <div class="row">
        <form id="send_clan_application" method="post">
          <input type="hidden" name="clanId" value="<?php echo $clanId; ?>">
          <div class="input-field col s12">
            <textarea name="text" class="materialize-textarea white-text" placeholder="<?php echo ($pendingAlready ? 'Your application to this Clan is pending.' : ($clan['recruiting'] ? 'Enter your application text here.' : "This Clan is not recruiting, so you can't apply to join.")); ?>" <?php echo ($pendingAlready || !$clan['recruiting'] ? 'disabled' : ''); ?>></textarea>
          </div>
          <div class="input-field center col s12">
            <button class="btn grey darken-3 waves-effect waves-light col s12 <?php echo ($pendingAlready || !$clan['recruiting'] ? 'disabled' : ''); ?>">SEND</button>
          </div>
        </form>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
</div>
