<?php
require_once('config.php');
$action = (isset($_GET['action']) && Database::GetInstance()) != NULL ? $_GET['action'] : (Database::GetInstance() != NULL ? 'index' : 'maintenance');
Functions::LoadPage($action);
ob_end_flush();
?>
