<?php
session_start();
$session_userid = $_SESSION["user_id"];
$session_username = $_SESSION["username"];

// Get db_information
include_once 'dbConnection.php';

//tabs
$tab_home = '';
$tab_configure = 'active';
$tab_inventory = '';
$tab_monitor = '';
$tab_analyze = '';
$tab_help = '';

// Initialize Count
$c=1;
$content_table = '';

// Contents Displayed

$others = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';


if (@$_GET['q'] == "edit") {

$title = "DB-IMS Dashboard - Edit";
$js_script = '<script src="js/edit_connection.js"></script>';

// Initialize Query
    $query = "select db_con_string_id as connection_id, connection_name, server, port, admin_username, db_ims.fxn_mask_password(admin_password) as admin_password, admin_password as cor_pass, platform_desc as platform from db_ims.vw_db_connection_details_complete where username = '$session_username'";
    $view_connections = mysqli_query($con,$query) or die('Error: Cannot view connections');

$connection_details_containerhead  = '<div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Connection Details</h6>
                            <div class="table-responsive" >
                                <table class="table" id="editable_table">
                                    <thead> <tr>
                                            <th scope="col" style="display:none" >Conenction ID</th>
                                            <th scope="col">#</th>
                                            <th scope="col"> Alias </th>
                                            <th scope="col">Server</th>
                                            <th scope="col">Port</th>
                                            <th scope="col">Admin Username</th>
                                            <th scope="col">Admin Password</th>
                                            <th scope="col">Platform</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead><tbody = id="userData">';

while($row = mysqli_fetch_array($view_connections)) {    
    $connection_details_contents = '<tr id = "'.$row['connection_id'].'"><td scope="row" style="display:none">'.$row['connection_id'].'</td>
                                    <td>'.$c++.'</td>
                                    <td><span class="editSpan name">'.$row['connection_name'].'</span>
                                        <input class="editInput con_name form-control input-sm" type="text" id="con_name-'.$row['connection_id'].'" value="'.$row['connection_name'].'" style="display: none;"></td>
                                    <td><span class="editSpan server">'.$row['server'].'</span>
                                        <input class="editInput server form-control input-sm" type="text" id="server-'.$row['connection_id'].'" value="'.$row['server'].'" style="display: none;"></td>
                                    <td><span class="editSpan port">'.$row['port'].'</span>
                                        <input class="editInput port form-control input-sm" type="text" id="port-'.$row['connection_id'].'" value="'.$row['port'].'" style="display: none;"></td>
                                    <td><span class="editSpan dbuser">'.$row['admin_username'].'</span>
                                        <input class="editInput dbuser form-control input-sm" type="text" id="dbuser-'.$row['connection_id'].'" value="'.$row['admin_username'].'" style="display: none;"></td>
                                    <td><span class="editSpan dbpass">'.$row['admin_password'].'</span>
                                        <input class="editInput dbpass form-control input-sm" type="text" id="dbpass-'.$row['connection_id'].'" value="" style="display: none;">  
                                        <input class="dbpass form-control input-sm" type="text" id="cor-dbpass-'.$row['connection_id'].'" value="'.$row['cor_pass'].'" style="display: none;"></td>
                                    <td>'.$row['platform'].'</td>
                                     <td> <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-sm btn-default editBtn" style="float: none;"><span class="glyphicon glyphicon-pencil"></span></button>
                                    <button type="button" class="btn btn-sm btn-success saveBtn" style="float: none; display: none;"><span class="glyphicon glyphicon-floppy-disk"></span></button>
                                    <button type="button" class="btn btn-sm btn-danger cancelBtn" style="float: none; display: none;"><span class="glyphicon glyphicon-remove-sign"></span></button>
                                    </div>
                                    </td></tr>';
        $content_table = $content_table.$connection_details_contents;
}
 
$connection_details_containerfoot = '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';


$content = $connection_details_containerhead.$content_table.$connection_details_containerfoot;

} elseif (@$_GET['q'] == "remove") {

$title = "DB-IMS Dashboard - Edit";
$js_script = '<script src="js/delete_connection_js.js"></script>';

// Initialize Query
    $query = "select db_con_string_id as connection_id, connection_name, server, port, admin_username, db_ims.fxn_mask_password(admin_password) as admin_password, admin_password as cor_pass, platform_desc as platform from db_ims.vw_db_connection_details_complete where username = '$session_username'";
    $view_connections = mysqli_query($con,$query) or die('Error: Cannot view connections');

$connection_details_containerhead  = '<div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Connection Details</h6>
                            <div class="table-responsive" >
                                <table class="table" id="editable_table">
                                    <thead> <tr>
                                            <th scope="col" style="display:none" >Conenction ID</th>
                                            <th scope="col">#</th>
                                            <th scope="col"> Alias </th>
                                            <th scope="col">Server</th>
                                            <th scope="col">Port</th>
                                            <th scope="col">Admin Username</th>
                                            <th scope="col">Admin Password</th>
                                            <th scope="col">Platform</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead><tbody = id="userData">';

while($row = mysqli_fetch_array($view_connections)) {    
    $connection_details_contents = '<tr id = "'.$row['connection_id'].'"><td scope="row" style="display:none">'.$row['connection_id'].'</td>
                                    <td>'.$c++.'</td>
                                    <td id="con-name-'.$row['connection_id'].'">'.$row['connection_name'].'</td>
                                    <td>'.$row['server'].'</td>
                                    <td>'.$row['port'].'</td>
                                    <td>'.$row['admin_username'].'</td>
                                    <td>'.$row['admin_password'].'</td>
                                    <td>'.$row['platform'].'</td>
                                     <td> <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-sm btn-default removeBtn" style="float: none;"><span class="glyphicon glyphicon-trash"></span></button>
                                    </div>
                                    </td></tr>';
        $content_table = $content_table.$connection_details_contents;
}
 
$connection_details_containerfoot = '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';


$content = $connection_details_containerhead.$content_table.$connection_details_containerfoot;

}

$add_header = $others .$js_script;

include 'bg_dashboard.php';
?>