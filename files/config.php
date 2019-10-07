<?php
define('ROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

ini_set('log_errors', 1);
ini_set('error_log', ROOT . 'error_logs' . DIRECTORY_SEPARATOR . 'php_error_log');
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('session.save_path', ROOT . 'sessions');

define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DATABASE', 'server');

define('DOMAIN', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') && (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . '/haber-sitesi/');

define('CLASSES', ROOT . 'classes' . DIRECTORY_SEPARATOR);
define('EXTERNALS', 'external' . DIRECTORY_SEPARATOR);
define('INCLUDES', 'includes' . DIRECTORY_SEPARATOR);

require_once(CLASSES . 'Functions.php');
require_once(CLASSES . 'Database.php');

date_default_timezone_set('UTC');

Functions::ObStart();
?>
