<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];
// Get db_information
include_once 'dbConnection.php';

//tabs
$tab_home = '';
$tab_configure = '';
$tab_inventory = '';
$tab_monitor = '';
$tab_analyze = 'active';
$tab_help = '';

//Bg Active Inventories

// Initialize Count
$c=1;
$content_table = '';

$js_script = '<script src="js/inventory_js.js"></script>';
// Contents Displayed

$others = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';

$title = "DB-IMS Dashboard - Connect";
$add_header = $others .$js_script;

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
    $html_envtbl = $env_tbl_hdr.$sql_summary_table.'</tbody></table></div><input type="hidden" value="'.$session_userid.'" id = "user-id">';
} else {
    $html_envtbl = ''.'<input type="hidden" value="'.$session_userid.'" id = "user-id">';
}



$content_header = '<div class="container-fluid col-sm-12 col-xl-6"><div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">ENVIRONMENT</h2></div><div class="row"><div class="col-sm-6"><canvas id="environment-summary" width="500" height="450"></canvas></div><div class="col-sm-5">'.$html_envtbl .'</div>';

// SQL Server Summary
$sql_query = "select connection_name, uptime, agent, backup,  capacity, connections, growth_rate, maitenance from  db_ims.vw_sql_job_summary where user_id = '$session_userid' order by connection_name desc";
$sql_view_connections = mysqli_query($con,$sql_query) or die('Error: Cannot view connections');


$sql_content_table_header  = '<div class="bg-light rounded h-100 p-4">
                          <div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">SQL Server</h2></div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead> <tr>
                                            <th scope="col" >Conenction Name</th>
                                            <th scope="col">Database Uptime Jobs</th>
                                            <th scope="col">SQL Server Agent Jobs</th>
                                            <th scope="col">SQL Server Backup</th>
                                            <th scope="col">Server & Instance Capacity</th>
                                            <th scope="col">SQL Server Connections</th>
                                            <th scope="col">SQL Server Growth Rate</th>
                                            <th scope="col">Maitenance Jobs</th>
                                        </tr>
                                    </thead><tbody>';

$sql_content_table = '';

while($row = mysqli_fetch_array($sql_view_connections)) {
    $sql_content_table = $sql_content_table.'<tr><td><span>'.$row['connection_name'].'</span></td>
                                    <td><span>'.$row['uptime'].'</span></td>
                                    <td><span>'.$row['agent'].'</span></td>
                                    <td><span>'.$row['backup'].'</span></td>
                                    <td><span>'.$row['capacity'].'</span></td>
                                    <td><span>'.$row['connections'].'</span></td>
                                    <td><span>'.$row['growth_rate'].'</span></td>
                                    <td><span>'.$row['maitenance'].'</span></td></tr>';
     
}
     
$sql_table_contents = $sql_content_table_header.$sql_content_table.'</tbody></table></div></div>';


//PG Summary

$pg_query = "select connection_name, uptime, cache_hit_ratio, memory,  capacity, connections, roles, maitenance from db_ims.vw_pg_job_summary where user_id = '$session_userid' order by connection_name desc";
$pg_view_connections = mysqli_query($con,$pg_query) or die('Error: Cannot view connections');


$pg_content_table_header  = '<div class="bg-light rounded h-100 p-4">
                          <div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">Postgres</h2></div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead> <tr>
                                            <th scope="col" >Conenction Name</th>
                                            <th scope="col">Database Uptime Jobs</th>
                                            <th scope="col">Postgres Cache-Hit-Ratio</th>
                                            <th scope="col">Database Memory</th>
                                            <th scope="col">Postgres Instance Capacity</th>
                                            <th scope="col">Postgres Connections</th>
                                            <th scope="col">Rolese</th>
                                            <th scope="col">Maitenance Jobs</th>
                                        </tr>
                                    </thead><tbody>';

$pg_content_table = '';

while($row = mysqli_fetch_array($pg_view_connections)) {
    $pg_content_table = $pg_content_table.'<tr><td><span>'.$row['connection_name'].'</span></td>
                                    <td><span>'.$row['uptime'].'</span></td>
                                    <td><span>'.$row['cache_hit_ratio'].'</span></td>
                                    <td><span>'.$row['memory'].'</span></td>
                                    <td><span>'.$row['capacity'].'</span></td>
                                    <td><span>'.$row['connections'].'</span></td>
                                    <td><span>'.$row['roles'].'</span></td>
                                    <td><span>'.$row['maitenance'].'</span></td></tr>';
     
}
     
$pg_table_contents = $pg_content_table_header.$pg_content_table.'</tbody></table></div></div>';



//MY Summary

$my_query = "select connection_name, uptime, db_connection, db_capacity from db_ims.vw_mysql_job_summary where user_id = '$session_userid' order by connection_name desc";
$my_view_connections = mysqli_query($con,$my_query) or die('Error: Cannot view connections');


$my_content_table_header  = '<div class="bg-light rounded h-100 p-4">
                          <div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">MySQL</h2></div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead> <tr>
                                            <th scope="col" >Conenction Name</th>
                                            <th scope="col">Database Uptime Jobs</th>
                                            <th scope="col">MYSQL Connection Jobs</th>
                                            <th scope="col">MYSQL Capacity Jobs</th>
                                        </tr>
                                    </thead><tbody>';

$my_content_table = '';

while($row = mysqli_fetch_array($my_view_connections)) {
    $my_content_table = $my_content_table.'<tr><td><span>'.$row['connection_name'].'</span></td>
                                    <td><span>'.$row['uptime'].'</span></td>
                                    <td><span>'.$row['db_connection'].'</span></td>
                                    <td><span>'.$row['db_capacity'].'</span></td></tr>';
     
}
     
$my_table_contents = $my_content_table_header.$my_content_table.'</tbody></table></div></div>';


//OR Summary

$or_query = "select connection_name, uptime, availability from db_ims.vw_oracle_job_summary where user_id = '$session_userid' order by connection_name desc";
$or_view_connections = mysqli_query($con,$or_query) or die('Error: Cannot view connections');


$or_content_table_header  = '<div class="bg-light rounded h-100 p-4">
                          <div class="p-2 mb-2 bg-primary text-white"><h2 style="text-align:center">ORACLE</h2></div>
                            <div class="table-responsive" >
                                <table class="table">
                                    <thead> <tr>
                                            <th scope="col" >Conenction Name</th>
                                            <th scope="col">Database Uptime Jobs</th>
                                            <th scope="col">Oracle Availability Jobs</th>
                                        </tr>
                                    </thead><tbody>';

$or_content_table = '';

while($row = mysqli_fetch_array($or_view_connections)) {
    $or_content_table = $or_content_table.'<tr><td><span>'.$row['connection_name'].'</span></td>
                                    <td><span>'.$row['uptime'].'</span></td>
                                    <td><span>'.$row['availability'].'</span></td>
                                    </tr>';
     
}
     
$or_table_contents = $or_content_table_header.$or_content_table.'</tbody></table></div></div>';


$content = $content_header.$sql_table_contents.$pg_table_contents.$my_table_contents.$or_table_contents.'</div>';


include 'bg_dashboard.php';
?>