<?php require_once(INCLUDES . 'header.php'); ?>

      <div id="main">
        <div class="container">
          <div class="row">
            <div class="col s12">
              <?php
              $page_ = INCLUDES . 'clan/join.php';

              if (isset($page[1])) {
                $page_ = INCLUDES . 'clan/' . $page[1] . '.php';
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

<?php require_once(INCLUDES . 'footer.php'); ?>
