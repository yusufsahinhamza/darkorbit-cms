<div id="company-select" class="card center white-text grey darken-4">
  <div class="row">
    <?php if ($player['factionId'] != 1) { ?>
    <div class="col s6">
      <h3>MMO</h3>
      <img data-faction-name="Mars Mining Operations" class="company mmo modal-trigger" href="#modal" src="<?php echo DOMAIN; ?>img/companies/selection/mmo.jpg">
    </div>
    <?php } ?>
    <?php if ($player['factionId'] != 2) { ?>
    <div class="col s6">
      <h3>EIC</h3>
      <img data-faction-name="Earth Industries Corporation" class="company eic modal-trigger" href="#modal" src="<?php echo DOMAIN; ?>img/companies/selection/eic.jpg">
    </div>
    <?php } ?>
    <?php if ($player['factionId'] != 3) { ?>
    <div class="col s6">
      <h3>VRU</h3>
      <img data-faction-name="Venus Resources Unlimited" class="company vru modal-trigger" href="#modal" src="<?php echo DOMAIN; ?>img/companies/selection/vru.jpg">
    </div>
    <?php } ?>
  </div>
</div>

<div id="modal" class="modal grey darken-4 white-text">
  <div class="modal-content">
    <h4>Do you really want to switch companies?</h4>
    <h6>%faction_name%</h6>
    <p>In order for your company to let you go peacefully, you have to pay 5,000 Uridium. Since you're switching to a rival company, the new company will only recognize half of your current honor points - negative honor points will remain the same.</p>
  </div>
  <div class="modal-footer grey darken-4">
    <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
    <a id="confirm-company-change" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
  </div>
</div>
