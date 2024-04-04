<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];
// Get db_information
include_once 'dbConnection.php';

//tabs
$tab_home = '';
$tab_configure = '';
$tab_inventory = 'active';
$tab_monitor = '';
$tab_analyze = '';
$tab_help = '';


//Bg Active Inventories

// Initialize Count
$c=1;
$content_table = '';

$js_script = '';
// Contents Displayed

$others = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';

$title = "DB-IMS Dashboard - Connect";
$add_header = $others .$js_script;
    $query = "select db_con_string_id, connection_name, platform_desc as platform, paltform_val, coalesce(db_uptime, 'No Data') as db_uptime, coalesce(db_status, 'No Connection') as db_status, coalesce(concat(cast(day_uptime as varchar(10)), ' days and ',  cast(hours_uptime as varchar(10)), ' hours'), 'No Data') as age from db_ims.vw_uptime_check where connection_name is not null and user_id = '$session_userid' order by db_uptime asc";
    $view_connections = mysqli_query($con,$query) or die('Error: Cannot view connections');

$connection_details_containerhead  = '<div class="container-fluid col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                          <div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">INVENTORY</h2></div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead> <tr>
                                            <th scope="col" >Conenction Name</th>
                                            <th scope="col">Platform</th>
                                            <th scope="col">Database Uptime</th>
                                            <th scope="col">Database Status</th>
                                            <th scope="col">Run Time</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead><tbody>';

while($row = mysqli_fetch_array($view_connections)) {
    $db_status = $row['db_status'];
    if ($db_status == 'ONLINE'){$color = 'green';}
    elseif ($db_status == 'OFFLINE') {$color = 'red';}
    else {$color = 'blue';}


    $icon = '';
    $connection_details_contents = '<tr><td><span>'.$row['connection_name'].'</span></td>
                                    <td><span>'.$row['platform'].'</span></td>
                                    <td><span>'.$row['db_uptime'].'</span></td>
                                    <td><span><i class="fa fa-circle" aria-hidden="true"  style="font-size:20px;color:'.$color.'"></i></span>&nbsp&nbsp<span class="editSpan server">'.$db_status.'</span></td>
                                    <td><span>'.$row['age'].'</span></td>
                                    <td><span><a href="analyze_ind.php?dbid='.$row['db_con_string_id'].'&p='.$row['paltform_val'].'"><i class="fa fa-window-maximize me-2"></i></a></span></td></tr>';
     
    $content_table = $content_table.$connection_details_contents;
}
     
$connection_details_containerfoot = '</tbody></table></div></div></div>';


$content = $connection_details_containerhead.$content_table.$connection_details_containerfoot;


include 'bg_dashboard.php';
?>