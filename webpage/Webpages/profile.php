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
$tab_analyze = '';
$tab_help = '';

if (@$_GET['qpg'] == 'profile') {
    $query = "select username, email_address, masked_password as password  from db_ims.vw_userinfo where user_id = '$session_userid' limit 1";
    $get_profile = mysqli_query($con,$query) or die('Error: Cannot view connections');
    while($row = mysqli_fetch_array($get_profile)) {
            $display_profile = '<span class="font-weight-bold">'.$row['username'].'</span><span class="text-black-50">'.$row['email_address'].'</span>';
            $username = $row['username'];
            $email = $row['email_address'];
            $password = $row['password'];
             ;} 


    // Title
 $title = "DB-IMS Dashboard - Profile";

    //Scripts
 $add_header = '<script src="js/change_user_js.js" charset="utf-8" Content-Type="text/html"></script>';

    $content = '<div class="container-xxl position-relative bg-white d-flex p-0">
        <div class="row">
            <div class="col-md-4 border-right">
                <div class="d-flex flex-column align-items-center text-center p-3 py-5"><img class="rounded-circle mt-5" width="150px" src="Images/user_profiles/default_profile.png">'.$display_profile.'<span> </span></div>
            </div>
            <div class="col-md-8 border-right">
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">Profile Settings</h4>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><label class="labels">User Name</label><input id="username" type="text" class="form-control" placeholder="USERNAME" value="'.$username.'" disabled></div>
                        <div class="col-md-12"><label class="labels">Email ADDRESS</label><input type="text"  id="email_ad" class="form-control" placeholder="EMAIL ADDRESS" value="'.$email.'" disabled></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><label class="labels">Password</label><input type="password" id="password" class="form-control" placeholder="PASSWORD" value="'.$password.'" disabled></div>
                        <div class="col-md-6"><label class="labels">Confirm Password</label><input type="password" id="cpassword" class="form-control" value="'.$password.'" placeholder="CONFIRM PASSWORD" disabled></div>
                    </div>
                    <div class="mt-5 text-center">
                    <button class="editProfile btn btn-secondary" type="button">Edit Profile</button>
                    <button class="editPassword btn btn-secondary" type="button">Edit Password</button>
                    <button class="saveProfile btn btn-primary" id="'.$session_userid.'" type="button" disabled>Save Profile</button>
                    </div>
                </div>
            </div>';
    
    
} else if (@$_GET['qpg'] == 'notification') {
    
       // Title
$title = 'DB-IMS Dashboard - Notifications';

//read all notifications
    $rd_query = "CALL db_ims.sp_read_all('$session_userid')";
    $rd_all = mysqli_query($con,$rd_query) or die('Error: Cannot view connections');

    
    //Scripts
$add_header = '';

 $jquery = '<script>
$(document).ready(function(){
        
$(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });

});
</script>';
    
    $c = 0;
    
    $notif_tbl = '';
    
    $query = "select notif_link, message_text, notif_age from db_ims.notify_user where user_id = '$session_userid' limit 50";
    $get_notifcatione = mysqli_query($con,$query) or die('Error: Cannot view connections');
    
    while($row = mysqli_fetch_array($get_notifcatione)) {
             $notif_tbl = $notif_tbl.'<tr class="clickable-row" data-href="'.$row['notif_link'].'"><td><span>'.$row['message_text'].'</span><td><span>'.$row['notif_age'].'</span></td></tr>';
             }; 
    
     $content = '<div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <table class="table table-hover">
                                <thead><tr></tr></thead>
                                <tbody>'.$notif_tbl.'</tbody>
                            </table>
                        </div>
                    </div>'. $jquery;
    
}
include 'bg_dashboard.php';
?>