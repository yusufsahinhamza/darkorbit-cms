<?php
require_once('config.php');
Functions::LoadPage(isset($_GET['p']) ? $_GET['p'] : (MAINTENANCE || Database::GetInstance()->connect_errno ? 'maintenance' : 'index'));
?>
