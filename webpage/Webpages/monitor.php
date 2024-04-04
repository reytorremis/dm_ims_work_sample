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
$tab_monitor = 'active';
$tab_analyze = '';
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

$inv_header = '<div class="container-fluid col-sm-12 col-xl-6"><div class="well"><h4 class="mb-12">'.$dashboard_header.'</h4><div class="bg-light rounded h-100 p-4">'.$table_con_display.'</div></div></div>';



        // backup log details
$tbl_monitoring_view1 = '';
$tbl_monitoring_view2 = '';
$view_monitoring_details = "select job_id, job_no, board_category as category, concat(category, coalesce (concat('(', sub_category, ')'),'')) as sub_category, TSQL, installation_status, create_date, last_run, monitoring_details from db_ims.vw_monitoring_details where db_con_string_id = '$db_con_id' and platform = '$platform'";
$result_monitoring_details = mysqli_query($con,$view_monitoring_details) or die('Error! Could not connect to database');

if (mysqli_num_rows($result_monitoring_details) > 0) {
            while($row = mysqli_fetch_array($result_monitoring_details)) {
            $id = $row['job_id'];
            $tbl_monitoring_view1 = $tbl_monitoring_view1.'<tr><th scope="row">'.$row['job_no'].'</th><td>'.$row['category'].'</td><td>'.$row['sub_category'].'</td><td>'.$row['TSQL'].'</td><td>'.$row['installation_status'].'</td><td>'.$row['create_date'].'</td></tr>';
            $modal_view = '<div class="modal" id="myModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true"><div class="modal-dialog" role="document">
                        <div class="modal-content"><div class="modal-body" "><form name ="form'.$id.'" ><div class="form-group">
                        <input type="text" name="job_id" id="job_id" value = "'.$id.'" hidden> 
                        <fieldset>
                            <legend>Scheduling</legend>
                            <label> <input type="radio" name="schedule'.$id.'"  id="schedule_freq'.$id.'" value="FQ"> Scheduled </label>
                            <label> <input type="radio" name="schedule'.$id.'"  id="schedule_set'.$id.'"  value="ST"> Set Time </label>
                            <label> <input type="radio" name="schedule'.$id.'"  id="schedule_once'.$id.'"  value="ON"> Run Once </label>
                        </fieldset>
                        <div id="next'.$id.'" style="display:none">
                        <fieldset>
                            <legend>Occurance</legend>
                            <label> <input type="radio" name="occurance'.$id.'"  id="occurance_daily'.$id.'" value="OD"> Daily </label>
                            <label> <input type="radio" name="occurance'.$id.'"  id="occurance_week'.$id.'" value="WK"> Weekly </label>
                         </fieldset>
                         </div>
                        <div id="freq_time'.$id.'" style="display:none">
                        <label for="ftime">Frequency</label>
                        <input type="text" name="ftime" id="ftime'.$id.'">
                        <p> Make sure to separate each with H for hour, M for Minutes and S for Seconds.(Ex. 90M )<p>
                        </div>

                        <div id="set_time'.$id.'" style="display:none">
                        <label for="stime">Set Time</label>
                        <input type="text" name="stime" id="stime'.$id.'">
                        <p> Use Military Time (Ex.22H15M25S) [H (0 to 23), M / S (0 to 59)] <p>
                        </div>
                        <h3 style="color:red; font-size:16px" id="error'.$id.'"></h3> 
                        <div id="weekly'.$id.'" style="display:none">
                        <fieldset>
                            <legend>Weekly Schedule</legend>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_mon" value="1"> Monday </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_tue" value="2"> TUESDAY </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_wed" value="3"> WEDNESDAY </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_thu" value="4"> THURSDAY </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_fri" value="5"> FRIDAY </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_sat" value="6"> SATURDAY </label>
                                <label> <input type="checkbox" name="weekly'.$id.'" id="weekly_sun" value="7"> SUNDAY </label>
                        </fieldset>
                        </div>
                         
                        </div></div><div class="modal-footer">
                        <button type ="submit" class="btn btn-primary">SAVE</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></form>
                        </div></div></div></div>';
            $tbl_monitoring_view2 = $tbl_monitoring_view2.'<tr id = "'.$row['job_id'].'"><td scope="row" style="display:none">'.$row['job_id'].'</td><th scope="row">'.$row['job_no'].'</th><td>'.$row['category'].'</td><td>'.$row['sub_category'].'</td><td>'.$row['last_run'].'</td><td>'.$row['monitoring_details'].'</td><td><button type="button" class="editJob btn btn-warning" data-target="#myModal'.$id.'" id="'.$id.'" data-toggle="modal"><span class="glyphicon glyphicon-edit"></span></button></td><td><button type="button" class="runJob btn-info" id="'.$id.'"><span class="glyphicon glyphicon-play"></span></button></td></tr>'.$modal_view;
            }

          
            
            $tbl_monitoring_details_html1 = '<table class="table table-bordered"><thead><tr><th scope="col">NO</th><th scope="col">CATEGORY</th><th scope="col">SUB CATEGORY</th><th scope="col">TSQL</th><th scope="col">STATUS</th><th scope="col">CREATE DATE</th></tr>
            </thead><tbody>'.$tbl_monitoring_view1.'</tbody></table>';
            
            $tbl_monitoring_details_html2 = '<table class="table table-bordered"><thead><tr><th scope="col">NO</th><th scope="col">CATEGORY</th><th scope="col">SUB CATEGORY</th><th scope="col">LAST RUN</th><th scope="col">MONITORING DETAILS</th><th scope="col">EDIT</th><th scope="col">RUN</th></tr>
            </thead><tbody>'.$tbl_monitoring_view2.'</tbody></table>';



$inv_body1 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">JOB DETAILS</h4>'.$tbl_monitoring_details_html1.'</div></div>';
$inv_body2 = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">MONITORING DETAILS</h4>'.$tbl_monitoring_details_html2.'</div></div>';
$inv_body = $inv_body1.$inv_body2.$modal_view;
} else 
{$inv_body = '<div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">JOB DETAILS</h4></div></div><div class="col-sm-12"><div class="well"><h4 class="mb-4" style="text-align:center">MONITORING DETAILS</h4></div></div>';}



$content = $inv_header.$inv_body;

/// start of content
$js_script = '<script src="js/monitor_js.js"></script>';
$jquery = '<script>
$(document).ready(function(){
 $(".editJob").click(function(){
    a =$(this).attr("id");
    console.log(a);
    $("#schedule_freq" + a).click(function() {
        $("#freq_time" + a).show();
        $("#set_time" + a).hide();
        $("#next" + a).show();
    });
    
    $("#schedule_set" + a).click(function() {
        $("#freq_time" + a).hide();
        $("#set_time" + a).show();
        $("#next" + a).show();
    });
    $("#schedule_once" + a).click(function() {
        $("#freq_time" + a).hide();
        $("#set_time" + a).hide();
        $("#next" + a).hide();
        $("#weekly" + a).hide();
    });
    $("#occurance_daily" + a).click(function() {
        $("#weekly" + a).hide();
    });
    $("#occurance_week" + a).click(function() {
        $("#weekly" + a).show();
    });
    $("form[name=form"+ a +"]").submit(function (evt) {
    evt.preventDefault();
    var x = ValidationCheck(a);
    if (x !== null){
         console.log(x);
         submit_form(a,x);
    }
    });
});
 
  var a = "";
    $(".runJob").click(function(){
    var x =$(this).attr("id");
    console.log(x);
    run_job_manually(x);
    });
    
});
</script>';
$others = '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">'.$jquery;
$add_header = $others .$js_script;
$title = "DB-IMS Dashboard - Connect";
        
        

      


include 'bg_dashboard.php';
?>




