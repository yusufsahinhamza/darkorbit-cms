<?php
ob_end_flush();

if (!Functions::IsLoggedIn()) {
  header('Location: /');
  die();
}

$mysqli->begin_transaction();

try {
  foreach ($mysqli->query('SELECT * FROM server_bans WHERE userId = '.$player['userId'].' AND ended = 0')->fetch_all(MYSQLI_ASSOC) as $value) {
    if (new DateTime(date('d.m.Y H:i:s')) >= new DateTime($value['end_date'])) {
      $mysqli->query('UPDATE server_bans SET ended = 1 WHERE id = '.$value['id'].'');
    }
  }

  $mysqli->commit();
} catch (Exception $e) {
  $mysqli->rollback();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo SERVER_NAME; ?></title>
    <meta name="description" content="<?php echo SERVER_NAME; ?> is the ultimate space shooter. Explore the endless expanses of universe in one of the best and most exciting online browser games ever produced. Brave all dangers and go where nobody's ever gone before - either alone or with others. Set a course for undiscovered galaxies and seek out new lifeforms!">
    <meta property="og:title" content="<?php echo SERVER_NAME; ?>"/>
    <meta property="og:url" content="<?php echo DOMAIN; ?>"/>
    <meta property="og:site_name" content="<?php echo SERVER_NAME; ?>"/>
    <meta property="og:description" content="<?php echo SERVER_NAME; ?> is the ultimate space shooter. Explore the endless expanses of universe in one of the best and most exciting online browser games ever produced. Brave all dangers and go where nobody's ever gone before - either alone or with others. Set a course for undiscovered galaxies and seek out new lifeforms!"/>
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="1 days">
    <link rel="shortcut icon" href="<?php echo DOMAIN; ?>favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?php echo DOMAIN; ?>css/map-revolution.css" />
    <script type="text/javascript" src="<?php echo DOMAIN; ?>js/darkorbit/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo DOMAIN; ?>js/darkorbit/jquery.js"></script>
    <script type="text/javascript" src="<?php echo DOMAIN; ?>js/darkorbit/jquery.flashembed.js"></script>
    <script type="text/javascript" src="<?php echo DOMAIN; ?>js/darkorbit/main.js"></script>
</head>
<body>

<?php
$statement = $mysqli->query('SELECT * FROM server_bans WHERE typeId = 1 AND ended = 0 AND userId = '.$player['userId'].'');
$fetch = $statement->fetch_assoc();

if ($statement->num_rows <= 0) {
$gamePlayerSettings = json_decode($mysqli->query('SELECT * FROM player_settings WHERE userId = '.$player['userId'].'')->fetch_assoc()['gameplay']);
?>
  <div id="container"></div>

  <script type="text/javascript">
      function onFailFlashembed() {
          var html = '';
          html += '<div id="flashFail">';
          html += '<div class="flashFailHead">Get the Adobe Flash Player</div>';
          html += '<div class="flashFailHeadText">';
          html += 'In order to play <?php echo SERVER_NAME; ?>, you need the latest version of Flash Player. Just install it to start playing!';
          html += '<div class="flashFailHeadLink">';
          html += 'Download the Flash Player here for free: <a href=\"http://www.adobe.com/go/getflashplayer\" style=\"text-decoration: underline; color:#b1b1e8;\">Download Flash Player<\/a>';
          html += '</div>';
          html += '</div>';
          html += '</div>';
          jQuery('#container').html(html);
      }

      function expressInstallCallback(info) {
          onFailFlashembed();
      }

      jQuery(document).ready(function(){
          var aFlashVersion = flashembed.getVersion();
          if(!Object.keys) Object.keys = function(o){
              if (o !== Object(o))
                  throw new TypeError('Object.keys called on non-object');
              var ret=[],p;
              for(p in o) if(Object.prototype.hasOwnProperty.call(o,p)) ret.push(p);
                  return ret;
          }
          var sParam = '[' + '"jQuery.flashEmbed"' + ',"' + aFlashVersion[0] + "." +aFlashVersion[1] + '"' + ',"' + Object.keys(jQuery.browser)[0] + '"]';
          var data = {"action": "setClientBrowserConfig", "params": sParam, "isEncodeMessage":0}

          jQuery.post('<?php echo DOMAIN; ?>flashAPI/loadingScreen.php', data, function(data) {});

          flashembed("container", {
            "onFail": onFailFlashembed,
            "src": "<?php echo DOMAIN; ?>spacemap/preloader.swf",
            "version": [11,0],
            "expressInstall": "<?php echo DOMAIN; ?>swf_global/expressInstall.swf",
            "width": "100%",
            "height": "100%",
            "wmode": "direct",
            "bgcolor": "#000000",
            "id": "preloader",
            "allowfullscreen": "true",
            "allowFullScreenInteractive": "true"
          }, {
            "lang": "en",
            "userID": "<?php echo $player['userId']; ?>",
            "sessionID": "<?php echo $player['sessionId']; ?>",
            "basePath": "spacemap",
            "pid": "563",
            "boardLink": "",
            "helpLink": "",
            "loadingClaim": "LOADING",
            "chatHost": "<?php echo $_SERVER['SERVER_NAME']; ?>",
            "cdn": "",
            "useHash": "0",
            "host": "<?php echo $_SERVER['SERVER_NAME']; ?>",
            "browser": "Chrome",
            "Chrome": "1",
            "gameXmlHash": "",
            "resourcesXmlHash": "",
            "profileXmlHash": "",
            "languageXmlHash": "",
            "loadingscreenHash": "",
            "gameclientHash": "",
            "gameclientPath": "spacemap",
            "loadingscreenAssetsXmlHash": "",
            "showAdvertisingHint": "",
            "gameclientAllowedInitDelay": "10",
            "eventStreamContext": "",
            "useDeviceFonts": "0",
            "display2d": "<?php echo ($player['version'] ? '1' : '2'); ?>",
            "autoStartEnabled": "<?php echo (int)($gamePlayerSettings != null ? $gamePlayerSettings->autoStartEnabled : true); ?>",
            "mapID": "1",
            "allowChat": "<?php echo (int)($mysqli->query('SELECT * FROM server_bans WHERE typeId = 0 AND userId = '.$player['userId'].'')->num_rows <= 0);?>"
          });
      });
  </script>
<?php } else { ?>
<div id="container">
  <div id="banned">
    <div>You are banned by administrator.</div>
    <div>Reason: <?php echo $fetch['reason']; ?></div>
    <div>End date: <?php echo (new DateTime(date('d.m.Y H:i:s')))->diff(new DateTime($fetch['end_date']))->days >= 9998 ? 'Permanent' : date('d.m.Y H:i', strtotime($fetch['end_date'])); ?></div>
  </div>
</div>
<?php } ?>
</body>
</html>
