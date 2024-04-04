<?php
//ob_start();

session_start();
$session_user = $_SESSION["username"];
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include_once 'dbConnection.php';
include_once 'pyfile.php';

if (@$_GET['q'] == "connect") {
        $platform = addslashes(stripslashes($_POST['platform']));
        if ($platform == "OR"){
            $host_address = addslashes(stripslashes($_POST['host_address']));
            $port = addslashes(stripslashes($_POST['port']));
            $admin_name = addslashes(stripslashes($_POST['username']));
            $admin_pass = addslashes(stripslashes($_POST['password']));
            $oracle_db = addslashes(stripslashes($_POST['orcdb']));
        }else {
            $host_address = addslashes(stripslashes($_POST['host_address']));
            $port = addslashes(stripslashes($_POST['port']));
            $admin_name = addslashes(stripslashes($_POST['username']));
            $admin_pass = addslashes(stripslashes($_POST['password']));
            $oracle_db = '';
        }

        
        
        $data = array(
                'server' => $host_address,
                'port' => $port,
                'platform' => $platform,
                'oracle db' => $oracle_db,
                'dbuser' => $admin_name,
                'dbpass'=>  $admin_pass
                );    


    $execute_query = ("CALL db_ims.create_connection_details('$session_user','$host_address','$admin_name','$admin_pass','$platform','$port', '$oracle_db')");
    $result = mysqli_query($con,$execute_query) or die('Error! Could not connect to database');

    while($row = mysqli_fetch_array($result)) {
            $qry_check = $row['result_query'];} 

    if(!empty($qry_check)){
    //    header("location:connectdb.php?qres=Connection Details Successfully Added"); 
        $returnData = array(
            'status' => 'OK',
            'msg' => implode(",",$data) .' successfully staged.',
            'temp_con_id'  => $qry_check
        );
    }
    
    //else if ($qry_check  == 'ERROR'){
    //    header("location:connectdb.php?qres=Connection Details are Invalid'$get_salt_query.'"); 
    //}
    else {
    //    header("location:connectdb.php?qres=Uknown Error"); 
        $returnData = array(
            'status' => 'ERROR',
            'msg' => $execute_query.' did not execute.',
            'temp_con_id'  => $qry_check
        );
    }

echo json_encode($returnData);

} else if (@$_GET['q'] == "edit") {    
    
    $connect_id = @$_GET['id'];
    $con_name = @$_POST['con_name'];
    $host_ip = $_POST['server'];
    $port = $_POST['port'];
    $admin_user = $_POST['dbuser'];
    $admin_pass = $_POST['dbpass'];
        
    $data = array(
                'connect_id' => $connect_id,
                'connection_name' => $con_name,
                'server' => $host_ip,
                'port' => $port,
                'dbuser' => $admin_user,
                'dbpass'=>  $admin_pass
                );    
try 
{
    $query = "CALL db_ims.edit_connection_details('$connect_id', '$con_name','$host_ip','$port','$admin_user','$admin_pass')";
    $update = mysqli_query($con, $query) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error($con), E_USER_ERROR);
    
    $returnData = array(
            'status' => 'OK',
            'msg' => $query
        );
}
catch(Exception $e) {
    $error = $e->getMessage();
    $returnData = array(
            'status' => 'ERROR',
            'msg' => $error
    );
    
}

echo json_encode($returnData);
        
        
} else if (@$_GET['q'] == "db_connect") {
    
    $temporary_connection_id = $_POST['temp_con_id'];
    
    $args = [
    $py_db_connection,
    $temporary_connection_id
    ]; 

// just a fancy way of avoiding a foreach loop
    $escaped_args = implode(" ", array_map("escapeshellarg", $args));
    $command = "$python $escaped_args 2>&1";
    $output = shell_exec($command);
    
    if (!$output) {

        $returnData = array(
            'status' => 'ERROR',
            'msg' => 'Failed to Connect to Target Database',
            'new_con_id' => ''
        );
    } else {
        $returnData = array(
            'status' => 'OK',
            'msg' => 'Target Database Connection Established',
            'new_con_id' => preg_replace('/\s+/', '', trim(strval($output)))
        );
    };

    echo json_encode($returnData);
} else if (@$_GET['q'] == "rename") {
    
    $permanent_connection_id = $_POST['permanent_con_id'];
    $db_alias = $_POST['db_alias'];
    
    try 
{
    $query = "CALL db_ims.rename_db_connection('$permanent_connection_id','$db_alias')";
    $rename = mysqli_query($con, $query) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error($con), E_USER_ERROR);
    
    $returnData = array(
            'status' => 'OK',
            'msg' => 'Successfully Changed alias'
        );
}
catch(Exception $e) {
    $error = $e->getMessage();
    $returnData = array(
            'status' => 'ERROR',
            'msg' => $error
    );
    
}

echo json_encode($returnData);
} else if (@$_GET['q'] == "delete") {    
    
    $connect_id = @$_GET['id'];
    
try 
{
    $query = "CALL db_ims.archive_connection_details('$connect_id')";
    $update = mysqli_query($con, $query) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error($con), E_USER_ERROR);
    
    $returnData = array(
            'status' => 'OK',
            'msg' => $query
        );
}
catch(Exception $e) {
    $error = $e->getMessage();
    $returnData = array(
            'status' => 'ERROR',
            'msg' => $error
    );
    
}
echo json_encode($returnData);
}
?>