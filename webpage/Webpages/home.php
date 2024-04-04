<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];



include_once 'dbConnection.php';

//tabs
$tab_home = 'active';
$tab_configure = '';
$tab_inventory = '';
$tab_monitor = '';
$tab_analyze = '';
$tab_help = '';

// Title
$title = "DB-IMS Dashboard - Home";

//Scripts
$add_header = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script><script src="js/inventory_js.js"></script>';

$query_sum = "select platform, db_version, cnt from db_ims.vw_db_invetory_summary where user_id = '$session_userid'";
$sum_sql = mysqli_query($con,$query_sum) or die('Error! Could not connect to database');

if (mysqli_num_rows($sum_sql) > 0) {
$env_tbl_hdr = '<h2 style="text-align:center">Version Breakdown</h2><table class="table table-striped center"><thead><tr><th scope="col">Platform</th><th scope="col" >Version</th><th scope="col" >Count</th></tr></thead><tbody>';
$sql_summary_table = '';
    while($row = mysqli_fetch_array($sum_sql)) {
    if ($row['platform'] == 'TOTAL'){
    $sql_summary_table = $sql_summary_table.'<tr><td><span><strong>'.$row['platform'].'</strong></span></td>
                                    <td><span>'.$row['db_version'].'</span></td>
                                    <td><span><strong>'.$row['cnt'].'</strong></span></td></tr>';        
    } else {
    $sql_summary_table = $sql_summary_table.'<tr><td><span>'.$row['platform'].'</span></td>
                                    <td><span>'.$row['db_version'].'</span></td>
                                    <td><span>'.$row['cnt'].'</span></td></tr>';
    }
    }
    $html_envtbl = $env_tbl_hdr.$sql_summary_table.'</tbody></table></div>';
} else {
    $html_envtbl = '';
}

$content_header = '<div class="container-fluid col-sm-12 col-xl-6"><input type="hidden" value="'.$session_userid.'" id = "user-id"><div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">QUICK VIEW OF ENVIRONMENT</h2></div><div class="row"><div class="col-sm-6"><canvas id="environment-summary" width="500" height="450"></canvas></div><div class="col-sm-5">'.$html_envtbl .'</div>';

$notif = '';
$query_notif = "select notif_link, message_text, notif_age from db_ims.notify_user where user_id = '$session_userid' limit 5";
$result_notif = mysqli_query($con,$query_notif) or die('Error! Could not connect to database');
while($row = mysqli_fetch_array($result_notif)){
    $notif = $notif.'<a href="'.$row['notif_link'].'" class="dropdown-item"><h6 class="fw-normal mb-0">'.$row['message_text'].'</h6><small>'.$row['notif_age'].'</small></a><hr class="dropdown-divider">';
}

$notifications = '<div class="col-sm-6 col-xl-6">
                <div class="h-100 bg-light rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Notifications</h6>
                                <a href="profile.php?qpg=notification">Show All</a>
                            </div>'.$notif.'</div></div>';

$query_status = "select coalesce(db_status, 'NO DATA') as db_status, count(*) as instance_cnt from db_ims.vw_uptime_check where user_id = '$session_userid' and connection_name is not null group by coalesce(db_status, 'NO DATA')";
$result_status = mysqli_query($con,$query_status) or die('Error! Could not connect to database');

if (mysqli_num_rows($result_status) > 0) {
$env_tbl_hdr = '<div class="col-sm-6 col-xl-6"><h2 style="text-align:center">Instance Summary</h2><table class="table table-striped center"><thead><tr><th scope="col">STATUS</th><th scope="col" >COUNT</th></tr></thead><tbody>';
$sql_statustbl = '';
    while($row = mysqli_fetch_array($result_status)) {
        $sql_statustbl = $sql_statustbl.'<tr><td><span><strong>'.$row['db_status'].'</strong></span></td>
                                    <td><span>'.$row['instance_cnt'].'</span></td></tr>';      
    
    }
    $html_statustbl = $env_tbl_hdr.$sql_statustbl.'</tbody></table></div></div>';
    
} else {
    $html_statustbl = '</div>';
}


$content = $content_header.$notifications.$html_statustbl;

include 'bg_dashboard.php';
?>