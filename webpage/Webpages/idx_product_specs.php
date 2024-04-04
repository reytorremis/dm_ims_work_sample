<?php
$title = "What is DB IMS";
$header = '<link rel="stylesheet" type="text/css" href="Styles/Stylesheet.css"/>';
$banner = '<div id="banner"></div>';
$content = '<div class="container">
    <h1>How it works?</h1>
  <div class="row">
    <div class="col-9"><img src="Images/step1_adding_db_instance.png" class="imgLeft" style="width:200px;height:200px;"/></div>
    <div class="col-4"><h4>Step 1: Installing Database</h4></div>
    <div class="col-6"> First, you will specify, the platform you are connecting to. Then, using the same form found in your DB-IMS dashboard, 
    you will need to supply the IP Address, Port, and Admin Credentails of your database instance. DB-IMS will then connect to
    your target database. If target database responds, it will save it as a permanent connection.</div>
  </div>
  <div class="row">
    <div class="col-9"><img src="Images/step2_install_stored_procedure.png" class="imgRight" style="width:200px;height:200px;"/></div>
    <div class="col-4"><h4>Step 2: Install Stored Procedure</h4></div>
    <div class="col-6">Once a permanent connection is established, an SQL Script containing the stored procedure will be installed 
    to your database instance. It will install it based on the nature of your platform using its native SQL Language. Once done, 
    you will provide an alias to your database instance.</div>
  </div>
  <div class="row">
    <div class="col-9"><img src="Images/step3_triger_stored_procedure.png" class="imgLeft" style="width:200px;height:200px;" /></div>
    <div class="col-4"><h4>Step 3: Trigger Stored Procedure</h4></div>
    <div class="col-6">DB-IMS will automatically gather statistics and database information. You can set the schedule and frequency of monitoring.
    The automated script will in its defauly value: 5 Minutes for Uptime Statistics, Hourly for other availability jobs and
    daily for capacity jobs.</div>
  </div>
  <div class="row">
    <div class="col-9"><img src="Images/step4_display_analysis.png" class="imgRight" style="width:200px;height:200px;"/></div>
    <div class="col-4"><h4>Step 4: Display Analysis</h4></div>
    <div class="col-6">You can view the analysis portion at the comfort of your dashboard. Data will be aggregated and displayed into graphs and tables.</div>
  </div>
</div>';

include 'bg_background.php';
?>

