<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];

include_once 'dbConnection.php';

//tabs
$tab_home = '';
$tab_configure = '';
$tab_inventory = '';
$tab_monitor = '';
$tab_analyze = '';
$tab_help = 'active';

// Title
$title = "DB-IMS Dashboard - Logs";

//Scripts
$add_header = '';

$add_hrs = '+6';

$date_now = date("Y-m-d H:i:s", strtotime($add_hrs.' hours'));
$date_format = date("M-d-Y", strtotime($date_now));  

$content = '<p>&nbsp;</p>
<p>Logs as of '.$date_now.'</p>
<p>&nbsp;</p>
<div id="list">
  <p><iframe src="db_ims-connection-logs-'.$date_format.'.log" frameborder="0" height="400"
      width="95%"></iframe></p>
</div>';

include 'bg_dashboard.php';
?>