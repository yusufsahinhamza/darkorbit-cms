      <div id="main">
        <div class="container">
          <div class="row">
            <div class="col s12">
              <div class="card white-text grey darken-4 padding-15">
                <h5>SETTINGS</h5>
                  <div class="row">
                    <div class="col s12 l6">
                      <form id="change_pilot_name" method="post">
                        <div class="col s12">
                          <div class="row">
                            <div class="input-field col s9">
                              <i class="material-icons prefix">person</i>
                              <input class="white-text" type="text" name="pilotName" id="pilotName" value="<?php echo Functions::s($player['pilotName']); ?>" maxlength="20" autocomplete="off" required>
                              <label for="pilotName">Pilot Name</label>
                              <span class="helper-text white-text" data-error="Enter a valid pilot name!">Enter your new pilot name.</span>
                            </div>
                            <div class="input-field col s3">
                              <button class="btn grey darken-3 waves-effect waves-light col s12">CHANGE</button>
                            </div>
                          </div>
                        </div>
                      </form>
                      <div class="input-field col s12">
                        <div class="switch">
                          <label style="color: #fff; font-size: 16px;">
                            2D
                            <input type="checkbox" name="version" <?php echo $player['version'] ? 'checked' : ''; ?>>
                            <span class="lever"></span>
                            3D
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
           </div>
          </div>
        </div>
      </div>
