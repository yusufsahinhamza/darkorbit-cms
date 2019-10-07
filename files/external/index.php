<?php require_once(INCLUDES . 'header.php'); ?>

      <div id="main">
        <?php
        if (Functions::IsLoggedIn()) {
          require_once(INCLUDES . 'home.php');
        } else {
          require_once(INCLUDES . 'index.php');
        }
        ?>
      </div>

<?php require_once(INCLUDES . 'footer.php'); ?>
