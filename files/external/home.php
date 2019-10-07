<?php require_once(INCLUDES . 'header.php'); ?>

      <div id="main">
        <div class="container">
          <div class="row">
            <div id="data" class="card white-text grey darken-4 col s12">
              <div class="row center no-margin">
                <div class="col s3">
                  Uridium: <?php echo number_format($data->uridium, 0, ',', '.'); ?>
                </div>
                <div class="col s3">
                  Credits: <?php echo number_format($data->credits, 0, ',', '.'); ?>
                </div>
                <div class="col s3">
                  Honor: <?php echo number_format($data->honor, 0, ',', '.'); ?>
                </div>
                <div class="col s3">
                  Experience: <?php echo number_format($data->experience, 0, ',', '.'); ?>
                </div>
              </div>
            </div>

            <div id="profile" class="card white-text grey darken-4 col s12">
              <img src="/img/avatar.png">
              <div class="profile-informations">
                <h5><?php echo $player['shipName']; ?></h5>
                <p>Clan: Free Agent</p>
                <p>Rank: <img src="<?php echo DOMAIN; ?>img/ranks/rank_<?php echo $player['rankID']; ?>.png"> <?php echo Functions::GetRankName($player['rankID']); ?></p>
                <p>ID: <?php echo $player['userId']; ?></p>
              </div>
            </div>

            <div id="hall-of-fame" class="card white-text grey darken-4 center col s12">
              <h5 >HALL OF FAME</h5>
              <ul class="tabs grey darken-3 tabs-fixed-width tab-demo z-depth-1">
                <li class="tab"><a href="#pilots">PILOTS</a></li>
                <li class="tab"><a href="#clans">CLANS</a></li>
              </ul>
              <div id="pilots" class="col s12">
                <table class="striped highlight">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Company</th>
                      <th>Rank</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mysqli->query('SELECT * FROM player_accounts ORDER BY rankPoints LIMIT 10') as $value) { ?>
                    <tr>
                      <td><?php echo $value['shipName']; ?></td>
                      <td><img src="/img/companies/logo_<?php echo($value['factionId'] == 1 ? 'mmo' : ($value['factionId'] == 2 ? 'eic' : 'vru')); ?>_mini.png"></td>
                      <td><?php echo $value['rank']; ?></td>
                      <td><?php echo $value['rankPoints']; ?></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <div id="clans" class="col s12">
                <table class="striped highlight">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Rank</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mysqli->query('SELECT * FROM server_clan ORDER BY rank LIMIT 10') as $value) { ?>
                    <tr>
                      <td>[<?php echo $value['tag']; ?>] <?php echo $value['name']; ?></td>
                      <td><?php echo $value['rank']; ?></td>
                      <td><?php echo $value['rankPoints']; ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

<?php require_once(INCLUDES . 'footer.php'); ?>
