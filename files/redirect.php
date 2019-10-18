<?php
require_once('config.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if (MAINTENANCE || Database::GetInstance()->connect_errno) {
  $action = 'maintenance';
}

Functions::LoadPage($action);

ob_end_flush();
?>
