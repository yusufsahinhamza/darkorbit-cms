<?php
$clans_per_page = 10;
$page_n = isset($page[2]) && preg_match('/^[1-9][0-9]*$/', $page[2]) && $page[2] >= 1 ? intval($page[2] - 1) : 0;
$clans = $mysqli->query('SELECT * FROM server_clans')->fetch_all(MYSQLI_ASSOC);
$number_of_pages = intval(count($clans) / $clans_per_page) + 1;

if ($page_n + 1 > $number_of_pages) {
  $page_n = 0;
}
?>

<div class="card white-text grey darken-4 col s12 padding-15">
  <div class="row">
    <div class="input-field col s12">
      <i class="material-icons prefix">search</i>
      <input class="white-text" type="text" name="search_clan" id="search_clan">
      <label for="search_clan">Search clan...</label>
    </div>
  </div>

  <?php
  $array = $mysqli->query('SELECT clanId FROM server_clan_applications WHERE userId = '.$player['userId'].'')->fetch_all(MYSQLI_ASSOC);

  if (count($array) >= 1) {
  ?>

  <h6>OPEN CLAN APPLICATIONS</h6>
  <table id="open-clan-applications" class="striped highlight">
    <thead>
      <tr>
        <th>Name</th>
        <th>Members</th>
        <th>Rank</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($array as $value) {
        $pendingClan = $mysqli->query('SELECT * FROM server_clans WHERE id = '.$value['clanId'].'')->fetch_assoc();
      ?>
      <tr id="pending-application-<?php echo $pendingClan['id']?>">
        <td><a href="<?php echo DOMAIN; ?>clan/clan-details/<?php echo $pendingClan['id']?>">[<?php echo $pendingClan['tag']; ?>] <?php echo $pendingClan['name']; ?></a></td>
        <td><?php echo count($mysqli->query('SELECT userId FROM player_accounts WHERE clanId = '.$pendingClan['id'].'')->fetch_all(MYSQLI_ASSOC)); ?></td>
        <td><?php echo $pendingClan['rank']; ?></td>
        <td><a class="modal-trigger withdraw-pending" href="#modal" data-clan-id="<?php echo $pendingClan['id']; ?>" data-clan-name="<?php echo $pendingClan['name']; ?>">Pending</a></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <br>
  <br>
  <?php } ?>

  <h6>CLAN LIST</h6>
  <table id="clan-list" class="striped highlight">
    <thead>
      <tr>
        <th>Name</th>
        <th>Members</th>
        <th>Rank</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (array_slice($clans, ($page_n * $clans_per_page), $clans_per_page) as $value) { ?>
      <tr>
        <td><a href="<?php echo DOMAIN; ?>clan/clan-details/<?php echo $value['id']?>">[<?php echo $value['tag']; ?>] <?php echo $value['name']; ?></a></td>
        <td><?php echo count($mysqli->query('SELECT userId FROM player_accounts WHERE clanId = '.$value['id'].'')->fetch_all(MYSQLI_ASSOC)); ?></td>
        <td><?php echo $value['rank']; ?></td>
        <td><?php echo ($value['recruiting'] ? 'Recruiting' : 'Closed'); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <ul class="pagination center">
    <?php if (($page_n + 1) !== 1) { ?>
      <?php if ($number_of_pages > 5 && ($page_n + 1) > 3) { ?>
      <li class="waves-effect waves-light grey"><a href="<?php echo DOMAIN; ?>clan/join/1">1</a></li>
      <?php } ?>

    <li class="waves-effect waves-light"><a href="<?php echo DOMAIN; ?>clan/join/<?php echo ($page_n === 0 ? 1 : $page_n); ?>"><i class="material-icons">chevron_left</i></a></li>
    <?php } ?>

    <?php for ($i = ($number_of_pages > 5 && ($page_n !== 0 && $page_n !== 1) ? ($page_n - 1) : 1); $i <= ($number_of_pages > 5 ? ($page_n + ($page_n === 0 ? 5 : ($page_n === 1 ? 4 : ($page_n + 1 === $number_of_pages ? 1 : ($page_n + 1 === $number_of_pages - 1 ? 1 : 3))))) : $number_of_pages); $i++) { ?>
    <li class="<?php echo ($page_n + 1 === $i ? 'active' : 'waves-effect waves-light grey'); ?>"><a href="<?php echo DOMAIN; ?>clan/join/<?php echo $i; ?>"><?php echo $i; ?></a></li>
    <?php } ?>

    <?php if (($page_n + 1) !== $number_of_pages) { ?>
      <li class="waves-effect waves-light"><a href="<?php echo DOMAIN; ?>clan/join/<?php echo $page_n + 2; ?>"><i class="material-icons">chevron_right</i></a></li>

      <?php if ($number_of_pages > 5) { ?>
        <li class="waves-effect waves-light grey"><a href="<?php echo DOMAIN; ?>clan/join/<?php echo $number_of_pages; ?>"><?php echo $number_of_pages; ?></a></li>
      <?php } ?>
    <?php } ?>
  </ul>
</div>

<?php if (count($array) >= 1) { ?>

<div id="modal" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h4>Withdraw your application</h4>
    <p>Would you really like to withdraw your application from %clan_name%?</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Cancel</a>
    <a id="withdraw" class="modal-close waves-effect waves-light btn grey darken-3">Ok</a>
  </div>
</div>

<?php } ?>
