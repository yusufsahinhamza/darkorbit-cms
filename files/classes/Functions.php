<?php

class Functions {
  public static function ObStart()
  {
    function minify_everything($buffer) {
        $buffer = preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/<!--(.|\s)*?-->/', '/\s+/'), array('>','<','\\1','', ' '), $buffer);
        return $buffer;
    }
    ob_start('ob_gzhandler');
    ob_start('minify_everything');
  }

  public static function LoadPage($variable) {
    if (!empty($variable)) {
      $page = explode('/', htmlspecialchars(trim($variable)));
      if (isset($page[0]) && $page[0] == 'maintenance') {
        $path = ROOT . EXTERNALS . 'maintenance.php';
      } else if ($page[0] == 'index' || !isset($page[0])) {
        $path = ROOT . EXTERNALS . 'index.php';
      } else {
        $path = EXTERNALS . $page[0] . '.php';
      }
      if(!file_exists($path)) {
        $path = EXTERNALS . 'error.php';
      }
      require_once($path);
    } else {
      header('Location: /');
    }
  }

  public static function IsLoggedIn()
  {
    return isset($_SESSION['account']);
  }
}

?>
