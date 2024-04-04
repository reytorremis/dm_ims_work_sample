<?php

$email_add = @$_GET['email'];

$title = "Change Password";
$header = '<link rel="stylesheet" href="css/recoveryform.css">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="js/recover_email_js.js"></script>';
$banner = '';
$sidebar = '';
$content = '<div class="container">
   <div class="row">
   <div class="col-md-6 col-md-offset-3">
   <div class="panel panel-login">
   <div class="panel-heading">
      <h1  id ="login-form-link">Change Password</h1>
      <img src="Images/dbims_logo.png" alt="" width="170" height="110"/>
   </div>
   <div class="panel-body">
   <div class="row">
   <form class="form" id="recovery_form" action="" method="" role="form" name="regform" onsubmit="return ChangePassword()">
   <div class="form__group">
      <input type="text" id="email" name ="email" placeholder="'.$email_add.'" class="form__input" disabled/>
      <input type="text" id="new_password" name ="new_password" placeholder="New Password" class="form__input" />
      <input type="text" id="verify_password" name ="verify_password" placeholder="Verify New Password" class="form__input" />
   </div>
   <input class="btn" type="submit" value="Change Password"/>
    </form>
             
    </div>
</div>
</div>
</div>
</div>
</div>';

include 'bg_background.php';
?>



