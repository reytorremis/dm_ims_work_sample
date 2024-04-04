<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];
// Get db_information
include_once 'dbConnection.php';

//Bg Active Inventories
//tabs
$tab_home = '';
$tab_configure = '';
$tab_inventory = '';
$tab_monitor = '';
$tab_analyze = 'active';
$tab_help = '';
// Contents Displayed
$db_con_id = @$_GET['dbid'];
$platform = @$_GET['p'];

$inv_footer = '</div>';

// Header Information - General
$query_con = ("select connection_name, server, port, driver, platform_desc, admin_username, db_ims.fxn_mask_password(admin_password) as admin_password from db_ims.vw_db_connection_details_complete where db_con_string_id = '$db_con_id' and connection_name is not null");
$result_con = mysqli_query($con,$query_con) or die('Error! Could not connect to database');

while($row = mysqli_fetch_array($result_con)) {
            $table_con_body = '<tbody><tr><td>'.$row['server'].'</td><td>'.$row['port'].'</td><td>'.$row['admin_username'].'</td><td>'.$row['admin_password'].'</td><td>'.$row['driver'].'</td></tr></tbody>';
            $dashboard_header = $row['platform_desc'].' :: '.$row['connection_name'];} 

$table_con_display = '<table class="table table-dark table-m"><thead><tr><th scope="col">SERVER</th><th scope="col">PORT</th><th scope="col">USERNAME</th><th scope="col">PASSWORD</th><th scope="col">DRIVER</th></tr></thead>'.$table_con_body .'</table>';

$inv_header = '<div class="container-fluid col-sm-12 col-xl-6"><div class="well"><h4 class="mb-12">'.$dashboard_header.'</h4><div class="bg-light rounded h-100 p-4">'.$table_con_display.'</div></div>';

// Connection Details
if ($platform == 'MS'){
// Connection Details - MS
        $query_condet = ("select x.db_con_string_id, max_connection_cnt, ncon, active, db_status  from db_ims.vw_sql_connection_details x join db_ims.vw_uptime_check y on x.db_con_string_id = y.db_con_string_id where x.db_con_string_id = '$db_con_id'");
        $result_condet = mysqli_query($con,$query_condet) or die('Error! Could not connect to database');

        if (mysqli_num_rows($result_condet) > 0) {

            while($row = mysqli_fetch_array($result_condet)) {
                    $max_con = '<span><p class="text-dark" style="text-align:center">'.number_format($row['max_connection_cnt'],0).'</p></span>';
                    $calc_p = round(($row['ncon'] / $row['max_connection_cnt'])*100,2);
                    $conncted = '<span><p class="text-dark" style="text-align:center">'.number_format($row['ncon'],0).'</p></span>';
                    if ($calc_p <= 33){
                        $xclass = 'progress-bar progress-bar-striped bg-success';
                    }
                    elseif ($calc_p <= 66) {
                        $xclass = 'progress-bar progress-bar-striped bg-warning';
                    }
                    else {
                        $xclass = 'progress-bar progress-bar-striped bg-danger';
                    }

                    $num_con = '<div class="'.$xclass.'" role="progressbar" aria-valuenow="'.$calc_p.'" aria-valuemin="0" aria-valuemax="100"></div>';

                    $active = '<span><p class="text-dark" style="text-align:center">'.number_format($row['active'],0).'</p></span>';;     

                    if ($row['db_status'] == 'ONLINE') {
                        $db_status = '<span><p class="text-success" style="text-align:center">'.$row['db_status'].'</p></span>';
                    } else {
                        $db_status = '<span><p class="text-danger" style="text-align:center">'.$row['db_status'].'</p></span>';
                    }

        }

        $inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div>'.$db_status.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div>'.$max_con.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0">'.$conncted.'<div class="progress">'.$num_con.'</div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Active Sessions</h4></div>'.$active.'</div></div></div>';


            } else {
                $inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0"><div class="progress"></div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Active Sessions</h4></div></div></div></div>';}}
elseif($platform == 'PG') {
    // Connection Details - PG
        $query_condet = ("select x.db_con_string_id, max_connection, used_connection, remaining_connection, db_status  from db_ims.vw_pg_connection_details x join db_ims.vw_uptime_check y on x.db_con_string_id = y.db_con_string_id where x.db_con_string_id = '$db_con_id'");
        $result_condet = mysqli_query($con,$query_condet) or die('Error! Could not connect to database');

        if (mysqli_num_rows($result_condet) > 0) {

            while($row = mysqli_fetch_array($result_condet)) {


                    $max_con = '<span><p class="text-dark" style="text-align:center">'.number_format($row['max_connection'],0).'</p></span>';
                    $calc_p = round((($row['max_connection'] - $row['remaining_connection']) / $row['max_connection'])*100,2);
                    $conncted = '<span><p class="text-dark" style="text-align:center">'.number_format($row['used_connection'],0).'</p></span>';
                    if ($calc_p <= 33){
                        $xclass = 'progress-bar progress-bar-striped bg-success';
                    }
                    elseif ($calc_p <= 66) {
                        $xclass = 'progress-bar progress-bar-striped bg-warning';
                    }
                    else {
                        $xclass = 'progress-bar progress-bar-striped bg-danger';
                    }

                    $num_con = '<div class="'.$xclass.'" role="progressbar" aria-valuenow="'.$calc_p.'" aria-valuemin="0" aria-valuemax="100"></div>';

                    $remaining = '<span><p class="text-dark" style="text-align:center">'.number_format($row['remaining_connection'],0).'</p></span>';;     

                    if ($row['db_status'] == 'ONLINE') {
                        $db_status = '<span><p class="text-success" style="text-align:center">'.$row['db_status'].'</p></span>';
                    } else {
                        $db_status = '<span><p class="text-danger" style="text-align:center">'.$row['db_status'].'</p></span>';
                    }

        }

        $inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div>'.$db_status.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div>'.$max_con.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0">'.$conncted.'<div class="progress">'.$num_con.'</div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div>'.$remaining.'</div></div></div>';

        
                } else {$inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0"><div class="progress"></div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div></div></div></div>';}}

elseif($platform == 'MY') {
    $query_condet = ("select x.db_con_string_id, max_connection, used_connection, remaining_connection, db_status from db_ims.vw_my_connection_details x join db_ims.vw_uptime_check y on x.db_con_string_id = y.db_con_string_id where x.db_con_string_id = '$db_con_id'");
    $result_condet = mysqli_query($con,$query_condet) or die('Error! Could not connect to database');
    
    if (mysqli_num_rows($result_condet) > 0) {

            while($row = mysqli_fetch_array($result_condet)) {


                    $max_con = '<span><p class="text-dark" style="text-align:center">'.number_format($row['max_connection'],0).'</p></span>';
                    $calc_p = round((($row['max_connection'] - $row['remaining_connection']) / $row['max_connection'])*100,2);
                    $conncted = '<span><p class="text-dark" style="text-align:center">'.number_format($row['used_connection'],0).'</p></span>';
                    if ($calc_p <= 33){
                        $xclass = 'progress-bar progress-bar-striped bg-success';
                    }
                    elseif ($calc_p <= 66) {
                        $xclass = 'progress-bar progress-bar-striped bg-warning';
                    }
                    else {
                        $xclass = 'progress-bar progress-bar-striped bg-danger';
                    }

                    $num_con = '<div class="'.$xclass.'" role="progressbar" aria-valuenow="'.$calc_p.'" aria-valuemin="0" aria-valuemax="100"></div>';

                    $remaining = '<span><p class="text-dark" style="text-align:center">'.number_format($row['remaining_connection'],0).'</p></span>';;     

                    if ($row['db_status'] == 'ONLINE') {
                        $db_status = '<span><p class="text-success" style="text-align:center">'.$row['db_status'].'</p></span>';
                    } else {
                        $db_status = '<span><p class="text-danger" style="text-align:center">'.$row['db_status'].'</p></span>';
                    }

        }
    
    $inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div>'.$db_status.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div>'.$max_con.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0">'.$conncted.'<div class="progress">'.$num_con.'</div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div>'.$remaining.'</div></div></div>';} 
    else {$inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0"><div class="progress"></div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div></div></div></div>';}

    
}elseif($platform == 'OR') {
    
    $query_condet = ("select x.db_con_string_id, max_connection, used_connection, remaining_connection, db_status from db_ims.vw_or_connection_details x join db_ims.vw_uptime_check y on x.db_con_string_id = y.db_con_string_id where x.db_con_string_id = '$db_con_id'");
    $result_condet = mysqli_query($con,$query_condet) or die('Error! Could not connect to database');
    
    if (mysqli_num_rows($result_condet) > 0) {

            while($row = mysqli_fetch_array($result_condet)) {


                    $max_con = '<span><p class="text-dark" style="text-align:center">'.number_format($row['max_connection'],0).'</p></span>';
                    $calc_p = round((($row['max_connection'] - $row['remaining_connection']) / $row['max_connection'])*100,2);
                    $conncted = '<span><p class="text-dark" style="text-align:center">'.number_format($row['used_connection'],0).'</p></span>';
                    if ($calc_p <= 33){
                        $xclass = 'progress-bar progress-bar-striped bg-success';
                    }
                    elseif ($calc_p <= 66) {
                        $xclass = 'progress-bar progress-bar-striped bg-warning';
                    }
                    else {
                        $xclass = 'progress-bar progress-bar-striped bg-danger';
                    }

                    $num_con = '<div class="'.$xclass.'" role="progressbar" aria-valuenow="'.$calc_p.'" aria-valuemin="0" aria-valuemax="100"></div>';

                    $remaining = '<span><p class="text-dark" style="text-align:center">'.number_format($row['remaining_connection'],0).'</p></span>';;     

                    if ($row['db_status'] == 'ONLINE') {
                        $db_status = '<span><p class="text-success" style="text-align:center">'.$row['db_status'].'</p></span>';
                    } else {
                        $db_status = '<span><p class="text-danger" style="text-align:center">'.$row['db_status'].'</p></span>';
                    }

        }
    
    $inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div>'.$db_status.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div>'.$max_con.'</div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0">'.$conncted.'<div class="progress">'.$num_con.'</div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div>'.$remaining.'</div></div></div>';} 
    else {$inv_connect_details = '<div class="row"><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Instance Status</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Max Connection</h4></div></div>
                </div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Connected</h4></div><div class="pg-bar mb-0"><div class="progress"></div></div>
                </div></div><div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Remaining Connections</h4></div></div></div></div>';}

}

// Uptime and Other Misc
if ($platform == 'MS'){
        // Instance and Agent Status
        $query_iastatus = ("select db_status, offline_db, agent_status from db_ims.vw_db_status_check where db_con_string_id = '$db_con_id'");
        $result_iastatus = mysqli_query($con,$query_iastatus) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_iastatus) > 0) {
            while($row = mysqli_fetch_array($result_iastatus)) {
            $db_status_html = '<span><p class="text-dark" style="text-align:center">'. str_replace("|","\n",$row['db_status']).'</p></span>';
            $offline_db_html = '<span><p class="text-dark" style="text-align:center">'.$row['offline_db'].'</p></span>';
            $agent_status_html = '<span><p class="text-dark" style="text-align:center">'. str_replace("|","\n",$row['agent_status']).'</p></span>';
            }

            $inv_body2 = '<div class="row"><div class="col-sm-9"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
            <div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Agent Status</h4></div>'.$db_status_html.'</div>
            <div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">OFFLINE DATABASESS</h4></div>'.$offline_db_html.'</div>
            <div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">DATABASE STATISTICS</h4></div>'.$agent_status_html.'</div></div>';
    
            } else {$inv_body2 = '<div class="row"><div class="col-sm-9"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                        <div class="col-sm-3"><div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">Agent Status</h4></div></div>
                        <div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">OFFLINE DATABASESS</h4></div></div>
                        <div class="well"><div class="p-2 mb-2 bg-primary text-white"><h4 style="text-align:center">DATABASE STATISTICS</h4></div></div></div>';}}

elseif ($platform == 'PG'){
            $tbl_memory_cache = '';
            $query_memory_cache = ("select database_name, cache_hit_ratio_index_percent, cache_hit_ratio_table_percent, memory, creation_date from db_ims.vw_pg_cache_hit_ratio_and_memory where db_con_string_id = '$db_con_id'");
            $result_memory_cache = mysqli_query($con,$query_memory_cache) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_memory_cache) > 0) {

            while($row = mysqli_fetch_array($result_memory_cache)) {
                $tbl_memory_cache = $tbl_memory_cache.'<tr><th scope="row">'.$row['database_name'].'</th><td>'.$row['cache_hit_ratio_index_percent'].'</td><td>'.$row['cache_hit_ratio_table_percent'].'</td><td>'.$row['memory'].'</td><td>'.$row['creation_date'].'</td></tr>';
            }
                $tbl_memory_cache_html = '<table class="table table-responsive"><thead><tr><th scope="col">DATABASE</th><th scope="col">CHR Index</th><th scope="col">CHR Table</th><th scope="col">MEMORY</th><th scope="col">DATE CREATED</th></tr>
                </thead><tbody>'.$tbl_memory_cache.'</tbody></table>';
            
        
            
            $inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DATABASE DETAILS</h4>'.$tbl_memory_cache_html.'</div></div>';
            
            
            }else {$inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DATABASE DETAILS</h4></div></div>';;}}
elseif ($platform == 'MY'){
         $tbl_table_index_info = '';
            $query_table_index_info = ("select schema_name, table_name, size_mb, indexable_columns from db_ims.table_size_and_index_info where db_con_string_id = '$db_con_id'");
            $result_table_index_info = mysqli_query($con, $query_table_index_info) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_table_index_info) > 0) {

            while($row = mysqli_fetch_array($result_table_index_info)) {
                $tbl_table_index_info = $tbl_table_index_info .'<tr><th scope="row">'.$row['schema_name'].'</th><td>'.$row['table_name'].'</td><td>'.$row['size_mb'].'</td><td>'.$row['indexable_columns'].'</td></tr>';
            }
                $tbl_table_index_info_html = '<table class="table table-responsive"><thead><tr><th scope="col">SCHEMA NAME</th><th scope="col">TABLE NAME</th><th scope="col">SIZE (MB)</th><th scope="col">INDEXABLE COLUMNS</th></tr>
                </thead><tbody>'.$tbl_table_index_info.'</tbody></table>';
            
        
            
            $inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">TABLE & INDEX INFORMATION</h4>'.$tbl_table_index_info_html.'</div></div>';
            
            
            }else {$inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">TABLE & INDEX INFORMATION</h4></div></div>';}
} elseif($platform == 'OR') {
    $tbl_instance_info = '';
            $query_instance_info = ("select instance_name, host_name, instance_status, database_status, state, logins from db_ims.or_instance_details where db_con_string_id = '$db_con_id'");
            $result_instance_info = mysqli_query($con, $query_instance_info) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_instance_info) > 0) {

            while($row = mysqli_fetch_array($result_instance_info)) {
                $tbl_instance_info = $tbl_instance_info .'<tr><th scope="row">'.$row['instance_name'].'</th><td>'.$row['host_name'].'</td><td>'.$row['instance_status'].'</td><td>'.$row['database_status'].'</td><td>'.$row['state'].'</td><td>'.$row['logins'].'</td></tr>';
            }
                $tbl_instance_info_html = '<table class="table table-responsive"><thead><tr><th scope="col">INSTANCE NAME</th><th scope="col">HOST NAME</th><th scope="col">INSTANCE STATUS</th><th scope="col">DATABASE STATUS</th><th scope="col">STATE</th><th scope="col">LOGINS</th></tr>
                </thead><tbody>'.$tbl_instance_info.'</tbody></table>';
            
        
            
            $inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center"INSTANCE INFORMATION</h4>'.$tbl_instance_info_html.'</div></div>';
            
            
            }else {$inv_body2 = '<div class="row"><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Uptime Statistics (Past 30 days)</h4><canvas id="up-time-statistics"></canvas></div></div>
                          <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">INSTANCE INFORMATION</h4></div></div>';}
}

if ($platform == 'MS'){
        // Job History
            $tbl_job_history = '';
            $query_job_history = ("select job_name, job_run_status, job_status, job_history_top_five from db_ims.vw_job_status_history where db_con_string_id = '$db_con_id'");
            $result_job_history = mysqli_query($con,$query_job_history) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_job_history) > 0) {

            while($row = mysqli_fetch_array($result_job_history)) {
                $tbl_job_history = $tbl_job_history.'<tr><th scope="row">'.$row['job_name'].'</th><td>'.$row['job_status'].'</td><td>'.$row['job_run_status'].'</td><td>'.$row['job_history_top_five'].'</td></tr>';
            }

                $tbl_jobhistory_html = '<table class="table table-responsive"><thead><tr><th scope="col">Job Name</th><th scope="col">STATUS</th><th scope="col">RUN STATUS</th><th scope="col">Most Recent History</th></tr>
                </thead><tbody>'.$tbl_job_history.'</tbody></table>';

                $inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">SQL Server Jobs</h4>'.$tbl_jobhistory_html.'</div></div>';
            }
            else {$inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">SQL Server Jobs</h4></div></div>';}}
elseif ($platform == 'PG'){
         // Active Connections
            $tbl_active = '';
            $query_active = ("select user_name, client_address, total_connections, last_query from db_ims.vw_pg_active_sessions where db_con_string_id = '$db_con_id'");
            $result_active = mysqli_query($con,$query_active) or die('Error! Could not connect to database');

                if (mysqli_num_rows($result_active) > 0) {
                while($row = mysqli_fetch_array($result_active)) {
                $tbl_active = $tbl_active.'<tr><th scope="row">'.$row['user_name'].'</th><td>'.$row['client_address'].'</td><td>'.$row['total_connections'].'</td><td>'.$row['last_query'].'</td></tr>';
                }

                 $tbl_active_html = '<table class="table table-responsive"><thead><tr><th scope="col">USER</th><th scope="col">CLIENT ADDRESS</th><th scope="col">TOTAL CONNECTIONS</th><th scope="col">Last Query</th></tr>
                </thead><tbody>'.$tbl_active.'</tbody></table>';

                $inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Active Sessions</h4>'.$tbl_active_html.'</div></div>';

                } else {$inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Active Sessions</h4></div></div>';}}
elseif ($platform == 'MY'){
         // Active Connections
            $tbl_sessions = '';
            $query_sessions = ("select process_id, user_name, server, last_query from db_ims.vw_my_active_sessions where db_con_string_id = '$db_con_id'");
            $result_sessions = mysqli_query($con,$query_sessions) or die('Error! Could not connect to database');

                if (mysqli_num_rows($result_sessions) > 0) {
                while($row = mysqli_fetch_array($result_sessions)) {
                $tbl_sessions = $tbl_sessions.'<tr><th scope="row">'.$row['process_id'].'</th><td>'.$row['user_name'].'</td><td>'.$row['server'].'</td><td>'.$row['last_query'].'</td></tr>';
                }

                 $tbl_sessions_html = '<table class="table table-responsive"><thead><tr><th scope="col">PID</th><th scope="col">USERNAME</th><th scope="col">SERVER</th><th scope="col">LAST QUERY</th></tr>
                </thead><tbody>'.$tbl_sessions.'</tbody></table>';

                $inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Active Sessions</h4>'.$tbl_sessions_html.'</div></div>';

                } else {$inv_body3 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Active Sessions</h4></div></div>';}}
elseif($platform == 'OR') {$inv_body3 = '';}

if ($platform == 'MS'){
        // Memory Capacity
        $tbl_mem_cap = '';
        $query_mem_cap = ("select volume_mount_point, file_system_type, total_size_mb, free_size_mb,  used_percentage, free_percentage, disk_property, last_date_check from db_ims.vw_sql_server_memory_capacity where db_con_string_id = '$db_con_id'");
        $result_mem_cap = mysqli_query($con,$query_mem_cap) or die('Error! Could not connect to database');

        if (mysqli_num_rows($result_mem_cap) > 0) {
            while($row = mysqli_fetch_array($result_mem_cap)) {
                if ($row['used_percentage'] <= 40){
                            $xclass = 'progress-bar-success';
                        }
                        elseif ($row['used_percentage'] <= 80) {
                            $xclass = 'progress-bar-warning';
                        }
                        else {
                            $xclass = 'progress-bar-danger';
                        }

                $tbl_mem_cap = $tbl_mem_cap.'<tr><th scope="row">'.$row['volume_mount_point'].'</th><td>'.$row['file_system_type'].'</td><td>'.$row['total_size_mb'].'</td><td>'.$row['free_size_mb'].'</td><td><progress value = "'.$row['used_percentage'].'" max = "100"/></progress></td><td>'.$row['disk_property'].'</td></tr>';
            }

            $tbl_mem_cap_html = '<table class="table table-striped"><thead><tr><th scope="col">Volume Mount</th><th scope="col">FILE SYSTEM TYPE</th><th scope="col">TOTAL (GB)</th><th scope="col">FREE (GB)</th><th scope="col">USAGE</th><th scope="col">DISK PROPERTIES</th></tr>
            </thead><tbody>'.$tbl_mem_cap.'</tbody></table>';
            $inv_body4 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DISK MEMORY CAPACITY</h4>'.$tbl_mem_cap_html.'</div></div>';
                } else {$inv_body4 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DISK MEMORY CAPACITY</h4></div></div>';}}
                
elseif ($platform == 'PG'){
        $tbl_top_query = '';
        $query_top_query = ("select process_id, user_name, query from db_ims.vw_pg_longest_running_queries where db_con_string_id = '$db_con_id'");
        $result_top_query = mysqli_query($con,$query_top_query) or die('Error! Could not connect to database');    
        
            if (mysqli_num_rows($result_top_query) > 0) {
            while($row = mysqli_fetch_array($result_top_query)) {
            $tbl_top_query = $tbl_top_query.'<tr><th scope="row">'.$row['process_id'].'</th><td>'.$row['user_name'].'</td><td>'.$row['query'].'</td></tr>';
            }
            
             $tbl_top_query_html = '<table class="table table-responsive"><thead><tr><th scope="col">PID</th><th scope="col">USER</th><th scope="col">QUERY</th></tr>
            </thead><tbody>'.$tbl_top_query.'</tbody></table>';
            
            $inv_body4 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">TOP 3 Longest Queries</h4>'.$tbl_top_query_html.'</div></div>';
            
            } else {$inv_body4 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">TOP 3 Longest Queries</h4></div></div>';}
            
} elseif ($platform == 'MY'){
    
               $tbl_datasize = '';
               $query_datasize = ("select x,y from db_ims.vw_innodb_and_db_information where db_con_string_id = '$db_con_id' and data_type = 'DB_SIZE'");
               $result_datasize = mysqli_query($con,$query_datasize) or die('Error! Could not connect to database');

               if (mysqli_num_rows($result_datasize) > 0) {
               while($row = mysqli_fetch_array($result_datasize)) {
                   $tbl_datasize = $tbl_datasize.'<tr><td>'.$row['x'].'</td><td>'.$row['y'].'</td></tr>';
               }
               $tbl_datasize_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">DATABASE SIZES</h4><table class="table table-bordered"><thead><tr><th scope="col">DATABASE</th><th scope="col">SIZE (MB)</th></tr>
                        </thead><tbody>'.$tbl_datasize.'</tbody></table></div></div>';
               } else {$tbl_datasize_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">DATABASE SIZES</h4><table class="table table-bordered"><thead><tr><th scope="col">DATABASE</th><th scope="col">SIZE (MB)</th></tr>
                       </thead><tbody></tbody></table></div></div>';}


                // Table Cache Hit Ration Breakdown
               $tbl_innodb = '';
               $query_innodb = ("select x,y from db_ims.vw_innodb_and_db_information where db_con_string_id = '$db_con_id' and data_type = 'INNODB_SIZE'");
               $result_innodb = mysqli_query($con,$query_innodb) or die('Error! Could not connect to database');

               if (mysqli_num_rows($result_innodb) > 0) {
               while($row = mysqli_fetch_array($result_innodb )) {
                    $tbl_innodb =  $tbl_innodb.'<tr><td>'.$row['x'].'</td><td>'.$row['y'].'</td></tr>';
               }
               $tbl_innodb_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">INNODB RECOMMENDATION</h4><table class="table table-bordered"><thead><tr><th scope="col">RIBPS (GB)</th><th scope="col">INNODB USED (GB)</th></tr>
               </thead><tbody>'.$tbl_innodb.'</tbody></table></div></div>';
               } else {$tbl_innodb_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">INNODB RECOMMENDATION</h4><table class="table table-bordered"><thead><tr><th scope="col">RIBPS (GB)</th><th scope="col">INNODB USED (GB)</th></tr>
               </thead><tbody></tbody></table></div></div>';}

           $inv_body4 = $tbl_datasize_html.$tbl_innodb_html;
    
} elseif($platform == 'OR') {$inv_body4 = '';}

//Section 5
if ($platform == 'MS'){
        // backup log details
            $tbl_back_up_details = '';
            $query_back_up_details = ("select database_name, full_back_up_date, full_back_up_size_MB, age, back_up_age_hrs from db_ims.vw_sql_backup_details where db_con_string_id = '$db_con_id'");
            $result_back_up_details = mysqli_query($con,$query_back_up_details) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_back_up_details) > 0) {
            while($row = mysqli_fetch_array($result_back_up_details)) {

                if ($row['back_up_age_hrs'] <= 120){$xclass = 'None';}
                elseif ($row['back_up_age_hrs'] >= 120 && $row['back_up_age_hrs'] <= 336){$xclass = 'LightYellow';}
                else{$xclass = 'LightCoral';}

                $tbl_back_up_details = $tbl_back_up_details.'<tr style="background-color:'.$xclass.'"><th scope="row">'.$row['database_name'].'</th><td>'.$row['full_back_up_date'].'</td><td>'.$row['full_back_up_size_MB'].'</td><td>'.$row['age'].'</td></tr>';
            }

            $tbl_back_up_details_html = '<table class="table table-bordered"><thead><tr><th scope="col">Database Name</th><th scope="col">FULL BACKUP DATE</th><th scope="col">FULL BACKUP SIZE (MB)</th><th scope="col">AGE</th></tr>
            </thead><tbody>'.$tbl_back_up_details.'</tbody></table>';

            $inv_body5 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">BACKUP DETAILS</h4>'.$tbl_back_up_details_html.'</div></div>';

            } else {$inv_body5 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">BACKUP DETAILS</h4></div></div>';}}
elseif ($platform == 'PG'){
            // Inheretance Map
            $tbl_inheretance_map = '';
            $query_inheretance_map = ("select object_id, schema_name, parent_object_id, inheretance_map from db_ims.vw_pg_inherentance_map where db_con_string_id = '$db_con_id'");
            $result_inheretance_map = mysqli_query($con,$query_inheretance_map) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_inheretance_map) > 0) {
            while($row = mysqli_fetch_array($result_inheretance_map)) {
                $tbl_inheretance_map = $tbl_inheretance_map.'<tr"><th scope="row">'.$row['object_id'].'</th><td>'.$row['schema_name'].'</td><td>'.$row['parent_object_id'].'</td><td>'.$row['inheretance_map'].'</td></tr>';
            }
            $tbl_inheretance_map_html = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">ROLE MAP</h4><table class="table table-bordered"><thead><tr><th scope="col">ID</th><th scope="col">SCHEMA</th><th scope="col">PARENT ID</th><th scope="col">MAP</th></tr>
            </thead><tbody>'.$tbl_inheretance_map.'</tbody></table></div></div>';

            } else {$tbl_inheretance_map_html = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">ROLE MAP</h4><table class="table table-bordered"><thead><tr><th scope="col">ID</th><th scope="col">SCHEMA</th><th scope="col">PARENT ID</th><th scope="col">MAP</th></tr>
            </thead><tbody></tbody></table></div></div>';}


             // Vacuum Summary
            $tbl_vacuum = '';
            $query_vacuum = ("select table_name, schema_owner, last_vacuum, num_tups, dead_tups from db_ims.vw_pg_vacuum_summary where db_con_string_id = '$db_con_id'");
            $result_vacuum = mysqli_query($con,$query_vacuum) or die('Error! Could not connect to database');

            if (mysqli_num_rows($result_vacuum) > 0) {
            while($row = mysqli_fetch_array($result_vacuum)) {
                $tbl_vacuum = $tbl_vacuum.'<tr"><th scope="row">'.$row['table_name'].'</th><td>'.$row['schema_owner'].'</td><td>'.$row['last_vacuum'].'</td><td>'.$row['num_tups'].'</td><td>'.$row['dead_tups'].'</td></tr>';
            }
                $tbl_vacuum_html = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">VACUUM SUMMARY</h4><table class="table table-bordered"><thead><tr><th scope="col">TABLE</th><th scope="col">SCHEMA</th><th scope="col">LAST VACUUM</th><th scope="col">TUPLES</th><th scope="col">DEAD TUPLES</th></tr>
            </thead><tbody>'.$tbl_vacuum.'</tbody></table></div></div>';
            } else {$tbl_vacuum_html = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">VACUUM SUMMARY</h4><table class="table table-bordered"><thead><tr><th scope="col">TABLE</th><th scope="col">SCHEMA</th><th scope="col">LAST VACUUM</th><th scope="col">TUPLES</th><th scope="col">DEAD TUPLES</th></tr>
            </thead><tbody></tbody></table></div></div>';}


            $inv_body5 = $tbl_inheretance_map_html.$tbl_vacuum_html;
        
} elseif ($platform == 'MY'){
        $tbl_largest = '';
        $query_largest = ("select schema_name, total_table_cnt, total_row_cnt, total_table_size, total_index_size, total_size from db_ims.mysql_table_and_index_summary where db_con_string_id = '$db_con_id'");
        $result_largest = mysqli_query($con,$query_largest) or die('Error! Could not connect to database');    
        
            if (mysqli_num_rows($result_largest) > 0) {
            while($row = mysqli_fetch_array($result_largest)) {
            $tbl_largest = $tbl_largest.'<tr><th scope="row">'.$row['schema_name'].'</th><td>'.$row['total_table_cnt'].'</td><td>'.$row['total_row_cnt'].'</td><td>'.$row['total_table_size'].'</td><td>'.$row['total_index_size'].'</td><td>'.$row['total_size'].'</td></tr>';
            }
            
             $tbl_largest_html = '<table class="table table-responsive"><thead><tr><th scope="col">SCHEMA</th><th scope="col">TABLE COUNT</th><th scope="col">ROW SIZE (MB)</th><th scope="col">TABLE SIZE (MB)</th><th scope="col">INDEX SIZE (MB)</th><th scope="col">TOTAL SIZE (MB)</th></tr>
            </thead><tbody>'.$tbl_largest.'</tbody></table>';
            
            $inv_body5 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">SUMMARY OF LARGEST TABLES AND SCHEMAS</h4>'.$tbl_largest_html.'</div></div>';
            
            } else {$inv_body5 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">SUMMARY OF LARGEST TABLES AND SCHEMAS</h4></div></div>';}
            
} elseif($platform == 'OR') {$inv_body5 = '';}
 
// Section 6
if ($platform == 'MS'){
// data & logs comparison
                    $tbl_data_logs_com = '';
                    $query_data_logs_com = ("select database_name, total_size_mb, total_used_percentage, data_size_used_percentage, log_size_used_percentage from db_ims.vw_sql_db_logs_cap_size_comp where db_con_string_id = '$db_con_id' order by total_used_percentage desc");
                    $result_data_logs_com = mysqli_query($con,$query_data_logs_com) or die('Error! Could not connect to database');

                    if (mysqli_num_rows($result_data_logs_com) > 0) {
                    while($row = mysqli_fetch_array($result_data_logs_com)) {
                    if ($row['total_used_percentage'] >= 50 && ($row['data_size_used_percentage'] >= 75 || $row['log_size_used_percentage'] >= 75)){$xclass = 'LightCoral';}
                    elseif ($row['total_used_percentage'] >= 50 && ($row['data_size_used_percentage'] >= 40 || $row['log_size_used_percentage'] >= 40)){$xclass = 'LightYellow';}
                    else{$xclass = 'None';}

                    $tbl_data_logs_com = $tbl_data_logs_com.'<tr style="background-color:'.$xclass.'"><th scope="row">'.$row['database_name'].'</th><td>'.$row['total_size_mb'].'</td><td>'.$row['total_used_percentage'].'</td><td>'.$row['data_size_used_percentage'].'</td><td>'.$row['log_size_used_percentage'].'</td></tr>';
                    }
                    $tbl_data_logs_com_html = '<table class="table table-bordered"><thead><tr><th scope="col">Database Name</th><th scope="col">TOTAL SIZE (MB)</th><th scope="col">TOTAL SIZE (%)</th><th scope="col">DATA SIZE (%)</th><th scope="col">LOG SIZE (%)</th></tr>
                    </thead><tbody>'.$tbl_data_logs_com.'</tbody></table>';


                    $inv_body6 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DATA & LOGS CAPACITY</h4>'.$tbl_data_logs_com_html.'</div></div>
                            <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Data and Logs Capacity</h4>
                            <canvas id="data-logs-cap"></canvas></div></div>';
                } else {$inv_body6 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">DATA & LOGS CAPACITY</h4></div></div>
                            <div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">Data and Logs Capacity</h4>
                            <canvas id="data-logs-cap"></canvas></div></div>';}}
elseif ($platform == 'PG'){
            //Index and Table Sizes
            $inv_body6 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">INDEX and TABLE SIZES</h4><canvas id="index-tbl-sizes"></canvas></div></div>';
}elseif ($platform == 'MY'){
    $inv_body6 = '';
} elseif($platform == 'OR') {$inv_body6 = '';}

// Section 7
if ($platform == 'MS'){
                // Backup Growth Rate
                $tbl_dbgrowth = '';
                $query_dbgrowth = ("select growth_name, time_taken_sec, change_size_mb from db_ims.vw_sql_database_growth_rate_90_days where db_con_string_id = '$db_con_id'");
                $result_dbgrowth = mysqli_query($con,$query_dbgrowth) or die('Error! Could not connect to database');

                if (mysqli_num_rows($result_dbgrowth) > 0) {
                while($row = mysqli_fetch_array($result_dbgrowth)) {
                    $tbl_dbgrowth = $tbl_dbgrowth.'<tr><th scope="row">'.$row['growth_name'].'</th><td>'.$row['change_size_mb'].'</td><td>'.$row['time_taken_sec'].'</td></tr>';
                }

                $tbl_dbgrowth_html = '<table class="table table-striped"><thead><tr><th scope="col">Growth Type</th><th scope="col">Change in Size (MB)</th><th scope="col">Time Duration (S)</th></tr>
                </thead><tbody>'.$tbl_dbgrowth.'</tbody></table>';

                $inv_body7 = '<div class="row"><div class="col-sm-7"><div class="well"><h4 class="mb-4" style="text-align:center">Average Backup Growth Rate (Past 6 Months)</h4>
                     <canvas id="avg-backup-growth"></canvas></div></div><div class="col-sm-5"><div class="well">
                     <h4 class="mb-4" style="text-align:center">Data and Log Growth Rate (Past 90 days)</h4>'.$tbl_dbgrowth_html.'</div></div></div>';
                } else { $inv_body7 = '<div class="row"><div class="col-sm-7"><div class="well"><h4 class="mb-4" style="text-align:center">Average Backup Growth Rate (Past 6 Months)</h4>
                     <canvas id="avg-backup-growth"></canvas></div></div><div class="col-sm-5"><div class="well">
                     <h4 class="mb-4" style="text-align:center">Data and Log Growth Rate (Past 90 days)</h4></div></div></div';}}
elseif ($platform == 'PG'){
    
                // Index Cache Hit Ration Breakdown
               $tbl_chr_idx = '';
               $query_chr_idx = ("select schema_owner, obj_name, index_cache_hit_ratio from db_ims.vw_pg_idx_cache_hit_ratio_below_80 where db_con_string_id = '$db_con_id'");
               $result_chr_idx = mysqli_query($con,$query_chr_idx) or die('Error! Could not connect to database');

               if (mysqli_num_rows($result_chr_idx) > 0) {
               while($row = mysqli_fetch_array($result_chr_idx)) {
                   $tbl_chr_idx = $tbl_chr_idx.'<tr"><th scope="row">'.$row['schema_owner'].'</th><td>'.$row['obj_name'].'</td><td>'.$row['index_cache_hit_ratio'].'</td></tr>';
               }
               $tbl_chr_idx_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">INDEX LIST BELOW 80% CACHE HIT RATIO</h4><table class="table table-bordered"><thead><tr><th scope="col">SCHEMA</th><th scope="col">INDEX NAME</th><th scope="col">CACHE HIT RATIO</th></tr>
                        </thead><tbody>'.$tbl_chr_idx.'</tbody></table></div></div>';
               } else {$tbl_chr_idx_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">INDEX LIST BELOW 80% CACHE HIT RATIO</h4><table class="table table-bordered"><thead><tr><th scope="col">SCHEMA</th><th scope="col">INDEX NAME</th><th scope="col">CACHE HIT RATIO</th></tr>
                       </thead><tbody></tbody></table></div></div>';}


                // Table Cache Hit Ration Breakdown
               $tbl_chr_tbl = '';
               $query_chr_tbl = ("select schema_owner, obj_name, table_cache_hit_ratio from db_ims.vw_pg_tbl_cache_hit_ratio_below_80 where db_con_string_id = '$db_con_id'");
               $result_chr_tbl = mysqli_query($con,$query_chr_tbl) or die('Error! Could not connect to database');

               if (mysqli_num_rows($result_chr_tbl) > 0) {
               while($row = mysqli_fetch_array($result_chr_tbl)) {
                   $tbl_chr_tbl = $tbl_chr_tbl.'<tr"><th scope="row">'.$row['schema_owner'].'</th><td>'.$row['obj_name'].'</td><td>'.$row['table_cache_hit_ratio'].'</td></tr>';
               }
               $tbl_chr_tbl_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">TABLE LIST BELOW 80% CACHE HIT RATIO</h4><table class="table table-bordered"><thead><tr><th scope="col">SCHEMA</th><th scope="col">TABLE NAME</th><th scope="col">CACHE HIT RATIO</th></tr>
               </thead><tbody>'.$tbl_chr_tbl.'</tbody></table></div></div>';
               } else {$tbl_chr_tbl_html = '<div class="col-sm-6"><div class="well"><h4 class="mb-4" style="text-align:center">TABLE LIST BELOW 80% CACHE HIT RATIO</h4><table class="table table-bordered"><thead><tr><th scope="col">SCHEMA</th><th scope="col">TABLE NAME</th><th scope="col">CACHE HIT RATIO</th></tr>
               </thead><tbody></tbody></table></div></div>';}

           $inv_body7 = $tbl_chr_tbl_html.$tbl_chr_idx_html;
    
}elseif ($platform == 'MY'){
    $inv_body7 = '';
} elseif($platform == 'OR') {$inv_body7 = '';}


$content = $inv_header.$inv_connect_details.$inv_body2.$inv_body3.$inv_body4.$inv_body5.$inv_body6.$inv_body7.$inv_footer;




/// start of content
$js_script = '';
$others = '<script src="js/chart_js.js"></script>';
$add_header = $others .$js_script;
$title = "DB-IMS Dashboard - Connect";
        
        

      


include 'bg_dashboard.php';
?>




