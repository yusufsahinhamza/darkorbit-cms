<?php
define('ROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

ini_set('log_errors', 1);
ini_set('error_log', ROOT . 'error_logs' . DIRECTORY_SEPARATOR . 'php_error.log');
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$sessions_path = ROOT . 'sessions';
ini_set('session.save_path', $sessions_path);

if (!file_exists($sessions_path)) {
  mkdir($sessions_path);
}

if (session_start()) {
    setcookie(session_name(), session_id(), null, '/', null, null, true);
}

define('MAINTENANCE', FALSE);

define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DATABASE', 'server');
define('MYSQL_PORT', '3306');

define('DOMAIN', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') && (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . '/');

define('CLASSES', ROOT . 'classes' . DIRECTORY_SEPARATOR);
define('EXTERNALS', ROOT . 'external' . DIRECTORY_SEPARATOR);
define('INCLUDES', EXTERNALS . 'includes' . DIRECTORY_SEPARATOR);
define('CRONJOBS', EXTERNALS . 'cronjobs' . DIRECTORY_SEPARATOR);

require_once(CLASSES . 'SMTP.php');
require_once(CLASSES . 'Functions.php');
require_once(CLASSES . 'Database.php');
require_once(CLASSES . 'Socket.php');

Functions::ObStart();
?>
