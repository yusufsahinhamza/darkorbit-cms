<div class="card white-text grey darken-4 padding-15">
  <h6>CLAN INFORMATIONS</h6>

  <div class="row">
    <div class="col s12">
      <p>Tag/Clan name: [<?php echo $clan['tag']; ?>] <?php echo $clan['name']; ?></p>
      <p>Founding date: <?php echo date('Y.m.d', strtotime($clan['date'])); ?></p>
      <p>Clan leader: <?php echo $mysqli->query('SELECT pilotName FROM player_accounts where userId = '.$clan['leaderId'].'')->fetch_assoc()['pilotName']; ?></p>
      <p>Number of members: <?php echo count($mysqli->query('SELECT userId FROM player_accounts WHERE clanId = '.$clan['id'].'')->fetch_all(MYSQLI_ASSOC)); ?></p>
      <p>Clan rank: <?php echo $clan['rank']; ?></p>
      <p>Company affiliation: <?php echo ($clan['factionId'] == 0 ? 'All' : ($clan['factionId'] == 1 ? 'MMO' : ($clan['factionId'] == 2 ? 'EIC' : 'VRU'))); ?></p>
      <p>Recruiting Status: <?php echo ($clan['recruiting'] ? 'Recruiting' : 'Closed'); ?></p>
    </div>
  </div>
</div>
