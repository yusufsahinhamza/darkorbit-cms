      <div id="main">
        <div class="container">
          <div class="row">
            <div class="col s12">
              <?php
              $page_ = INCLUDES . 'clan/join.php';

              if (isset($page[1])) {
                if (($page[1] == 'join' || $page[1] == 'found') && $player['clanId'] > 0) {
                  $page[1] = 'informations';
                } else if (($page[1] == 'informations' || $page[1] == 'members' || $page[1] == 'diplomacy') && $player['clanId'] <= 0) {
                  $page[1] = 'join';
                }

                $page_ = INCLUDES . 'clan/' . $page[1] . '.php';
              } else if ($player['clanId'] > 0) {
                $page_ = INCLUDES . 'clan/informations.php';
              }

              if (!file_exists($page_)) {
                $page_ = EXTERNALS . 'error.php';
              }

              require_once($page_);
              ?>
           </div>
          </div>
        </div>
      </div>
