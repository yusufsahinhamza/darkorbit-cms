<?php
$openApplications = $mysqli->query('SELECT * FROM server_clan_diplomacy_applications WHERE senderClanId = '.$player['clanId'].'')->fetch_all(MYSQLI_ASSOC);
?>
<div class="card white-text grey darken-4 padding-15">
  <h6>CLAN DIPLOMACY</h6>
  <table id="clan-diplomacy" class="striped highlight">
    <thead>
      <tr>
        <th>Name</th>
        <th>Form</th>
        <th>Date</th>
        <th>Activity</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($mysqli->query('SELECT * FROM server_clan_diplomacy WHERE toClanId = '.$player['clanId'].' OR senderClanId = '.$player['clanId'].'')->fetch_all(MYSQLI_ASSOC) as $value) {
        $clanId = ($player['clanId'] == $value['senderClanId'] ? $value['toClanId'] : $value['senderClanId'] );
        $clanName = $mysqli->query('SELECT name FROM server_clans WHERE id = '.$clanId.'')->fetch_assoc()['name'];
        ?>
        <tr id="diplomacy-<?php echo $value['id']; ?>">
          <td><?php echo $clanName; ?></td>
          <td><?php echo ($value['diplomacyType'] == 1 ? 'Alliance' : ($value['diplomacyType'] == 2 ? 'NAP' : 'War')); ?></td>
          <td><?php echo date('d.m.Y', strtotime($value['date'])); ?></td>
          <td>
            <?php if ($value['diplomacyType'] == 3) { ?>
            <button data-clan-id="<?php echo $clanId; ?>" data-diplomacy-clan-name="<?php echo $clanName; ?>" data-diplomacy-form="End War" class="end-war btn grey darken-1 waves-effect waves-light col s12 modal-trigger" href="#modal2">CANCEL</button>
            <?php } else { ?>
            <button data-diplomacy-id="<?php echo $value['id']; ?>" class="end-diplomacy btn grey darken-1 waves-effect waves-light col s12">CANCEL</button>
            <?php }?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <br>

  <h6>REQUEST</h6>
  <table class="striped highlight">
    <thead>
      <tr>
        <th>Name</th>
        <th>Form</th>
        <th>Date</th>
        <th>Activity</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($mysqli->query('SELECT * FROM server_clan_diplomacy_applications WHERE toClanId = '.$player['clanId'].'')->fetch_all(MYSQLI_ASSOC) as $value) {
          $requestClanName = $mysqli->query('SELECT name FROM server_clans WHERE id = '.$value['senderClanId'].'')->fetch_assoc()['name'];
          $diplomacyType = ($value['diplomacyType'] == 1 ? 'Alliance' : ($value['diplomacyType'] == 2 ? 'NAP' : ($value['diplomacyType'] == 3 ? 'War' : 'End War')));
        ?>
        <tr id="request-<?php echo $value['id']; ?>">
          <td><?php echo $requestClanName; ?></td>
          <td><?php echo $diplomacyType; ?></td>
          <td><?php echo date('d.m.Y', strtotime($value['date'])); ?></td>
          <td><button data-request-id="<?php echo $value['id']; ?>" data-request-clan-name="<?php echo $requestClanName; ?>" data-request-form="<?php echo $diplomacyType; ?>" class="view-request btn grey darken-1 waves-effect waves-light col s12 modal-trigger" href="#modal1">VIEW</button></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <br>

  <h6>REQUEST DIPLOMACY <a id="open_applications_button" class="waves-effect waves-light btn-small btn-flat grey darken-4 white-text modal-trigger" href="#modal" <?php echo (count($openApplications) <= 0 ? 'style="display:none;"' : ''); ?>>OPEN APPLICATIONS</a></h6>
  <div class="row">
    <form id="request_diplomacy" method="post">
      <input type="hidden" name="clanId" value="0">
      <div class="input-field col s7">
        <input class="white-text" type="text" name="keywords" id="keywords" autocomplete="off">
        <label for="keywords">Clan name</label>
        <ul id="dropdown3" class="dropdown-content">
        </ul>
      </div>
      <div class="input-field col s3">
        <select name="diplomacyType">
          <option value="1">Alliance</option>
          <option value="2">NAP</option>
          <option value="3">War</option>
        </select>
        <label>Diplomacy Type</label>
      </div>
      <div class="input-field center col s2">
        <button class="btn grey darken-3 waves-effect waves-light col s12">SEND</button>
      </div>
    </form>
  </div>
</div>

<div id="modal" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h5>Pending requests</h5>
    <table id="pending-requests" class="striped highlight">
      <thead>
        <tr>
          <th>Date</th>
          <th>Name</th>
          <th>Diplomacy Type</th>
          <th>Status</th>
          <th>Activity</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($openApplications as $value) { ?>
          <tr id="pending-request-<?php echo $value['id']; ?>">
            <td><?php echo date('d.m.Y', strtotime($value['date'])); ?></td>
            <td><?php echo $mysqli->query('SELECT name FROM server_clans WHERE id = '.$value['toClanId'].'')->fetch_assoc()['name']; ?></td>
            <td><?php echo ($value['diplomacyType'] == 1 ? 'Alliance' : ($value['diplomacyType'] == 2 ? 'NAP' : ($value['diplomacyType'] == 3 ? 'War' : 'End War'))); ?></td>
            <td>Waiting...</td>
            <td><button data-request-id="<?php echo $value['id']; ?>" class="cancel-request btn grey darken-1 waves-effect waves-light col s12">CANCEL</button></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div id="modal1" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h5>Diplomacy Request</h5>
    <h6>%clan_name%</h6>
    <p>%form%</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a id="decline" class="modal-close waves-effect waves-light btn grey darken-2">Decline</a>
    <a id="accept" class="modal-close waves-effect waves-light btn grey darken-3">Accept</a>
  </div>
</div>

<div id="modal2" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h5>Diplomacy Request</h5>
    <h6>%clan_name%</h6>
    <p>%form%</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
    <a id="end-war" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
  </div>
</div>
