<?php require_once(INCLUDES . 'header.php'); ?>

      <div id="main">
        <div class="container">
          <div class="row">
            <?php require_once(INCLUDES . 'data.php'); ?>

            <div class="col s12">
              <div class="card white-text grey darken-4 center padding-15">
                <h5>SHOP</h5>
                <ul class="tabs grey darken-3 tabs-fixed-width tab-demo z-depth-1">
                  <li class="tab"><a href="#drones">DRONES</a></li>
                </ul>
                <div id="drones">

                  <div class="row">
                    <div class="col m3 s2">
                      <div id="item-1" class="card grey darken-3">
                        <div class="card-image">
                          <img src="<?php echo DOMAIN; ?>do_img/global/items/drone/apis-5_top.png">
                        </div>
                        <div class="card-content">
                          <span class="card-title">Apis</span>
                          <p>100.000 U.</p>
                        </div>
                        <div class="card-action">
                          <div class="row">
                            <a href="#modal" data-item-id="1" class="buy btn grey darken-1 col s12 modal-trigger">BUY</a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col m3 s2">
                      <div id="item-2" class="card grey darken-3">
                        <div class="card-image">
                          <img src="<?php echo DOMAIN; ?>do_img/global/items/drone/zeus-5_top.png">
                        </div>
                        <div class="card-content">
                          <span class="card-title">Zeus</span>
                          <p>100.000 U.</p>
                        </div>
                        <div class="card-action">
                          <div class="row">
                            <a href="#modal" data-item-id="2" class="buy btn grey darken-1 col s12 modal-trigger">BUY</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div>
          <div id="modal" class="modal grey darken-4 white-text">
            <div class="modal-content">
              <p>Do you really want to buy %item_name% for %item_price%?</p>
            </div>
            <div class="modal-footer grey darken-4">
              <a class="modal-close waves-effect waves-light btn grey darken-2">Cancel</a>
              <a id="confirm-buy" class="modal-close waves-effect waves-light btn grey darken-3">Ok</a>
            </div>
          </div>
        </div>
      </div>

<?php require_once(INCLUDES . 'footer.php'); ?>
