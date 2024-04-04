<?php
header('Content-Type: application/json');
include_once 'dbConnection.php';
include_once 'pyfile.php';
 
$db_con_id = @$_GET['dbid'];


//get uptime records
if (@$_GET['ex'] == "upt"){

$query = "select uptime_logs as y, concat(UPPER(substring(date_format(hourly_uptime, '%b'),1,2)),date_format(hourly_uptime, '%d:%HH')) as x from db_ims.vw_db_uptime_logs_graph where db_con_string_id = '$db_con_id'";
$execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

$data = array();

while($row = mysqli_fetch_assoc($execute_sql)) {
            $data[] = $row;} 

echo json_encode($data);         

} elseif (@$_GET['plat'] == "MS" && @$_GET['ex'] == "agr") {

$query = "select monthly_growth as x, normal_backup_mb as y1, incremental_backup_mb as y2 from db_ims.vw_sql_backup_growth_rate_six_months where db_con_string_id = '$db_con_id'";
$execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

$data = array();

while($row = mysqli_fetch_assoc($execute_sql)) {
            $data[] = $row;} 

echo json_encode($data);
} elseif (@$_GET['plat'] == "MS" && @$_GET['ex'] == "dlc") {
    
$query = "select database_name as x, data_used_size_mb as y1, log_used_size_mb as y2 from db_ims.vw_sql_db_logs_cap where db_con_string_id = '$db_con_id' order by (data_used_size_mb + log_used_size_mb) desc ";
$execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

$data = array();

while($row = mysqli_fetch_assoc($execute_sql)) {
            $data[] = $row;} 
    
echo json_encode($data); 
} elseif (@$_GET['plat'] == "PG" && @$_GET['ex'] == "its") {
    
$db_con_id = @$_GET['dbid'];
        
$query = "select schema_owner as x, table_size_mb as y1, index_size_mb as y2 from db_ims.vw_pg_tbl_index_sizes where db_con_string_id = '$db_con_id' order by schema_owner";
$execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

$data = array();

while($row = mysqli_fetch_assoc($execute_sql)) {
            $data[] = $row;} 
    
echo json_encode($data); 
} elseif (@$_GET['ex'] == "con_job"){
    $job_id = $_POST['job_id'];
    $datastring = $_POST['datastring'];

    $query = "CALL db_ims.sp_update_job_configurationc('$job_id ','$datastring')";
    $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

    $data = array();

     $data = array(
                'status' => 'OK',
                'msg' => 'SUCCESSFULLY UPDATED');

echo json_encode($data);         

} elseif (@$_GET['ex'] == "run_job"){
    
    $job_id = @$_GET['jid'];

     $args = [
     $py_file_run,
     $job_id];
     
     // just a fancy way of avoiding a foreach loop
    $escaped_args = implode(" ", array_map("escapeshellarg", $args));
    $command = "$python $escaped_args 2>&1";
    $output = shell_exec($command);

    //    echo $output;
    if ($output == 200) {
        $status = 'OK';
        $message = 'Job Successfully Executed';
    } else {
        $status = 'ERROR';
        $message = 'Job Failed';
    };
    
    echo json_encode(array(
        'status' => $status,
        'msg' => $message
    ));
} elseif (@$_GET['ex'] == "inv") {
$uid = @$_GET['uid'];

$query = "select platform as x, count(*) as y, case 
when platform = 'MS SQL Server' then 'rgba(204,6,6,1)'
when platform = 'Postgres' then 'rgba(44,105,184, 1)'
when platform = 'MySQL' then 'rgba(255,191,0,1)'
when platform = 'Oracle' then 'rgba(74,255,128,1)'
else 'rgba(0,0,0,1)' end as color
from db_ims.vw_db_invetory where user_id = '$uid' and connection_name is not null group by platform order by platform desc";


$inve_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

$data = array();

while($row = mysqli_fetch_assoc($inve_sql)) {
            $data[] = $row;} 

echo json_encode($data);

} elseif (@$_GET['ex'] == "search") {
    
    session_start();
    $session_userid = $_SESSION["user_id"];

    $keyword = @$_GET['name'];
    
    $query = "select connection_name as cn, db_con_string_id as id, paltform_val as p from db_ims.vw_uptime_check where connection_name like '%$keyword%' and user_id = '$session_userid' limit 5";
    $result = mysqli_query($con,$query) or die('Error! Could not connect to database');

    $data = array();

    while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;} 

    echo json_encode($data);

    
    if (isset($_POST['name'])) {
    
    $keyword = @$_GET['name'];
    
    $query = "select connection_name, db_con_string_id, paltform_val from db_ims.vw_uptime_check where connection_name like '%$keyword%' limit 5";
    $result = mysqli_query($con,$query) or die('Error! Could not connect to database');

    $data = array();

    while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;} 

    echo json_encode($data);
}
}

 
?>
