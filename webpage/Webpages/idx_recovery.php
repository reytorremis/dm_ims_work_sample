<?php
$title = "Password Recovery";
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
      <h1  id ="login-form-link">Recover Account Details</h1>
      <img src="Images/dbims_logo.png" alt="" width="170" height="110"/>
   </div>
   <div class="panel-body">
   <div class="row">
   <form class="form" id="recovery_form" action="" method="" role="form" name="regform" onsubmit="return RecoverEmailAddress()">
   <div class="form__group">
      <input type="text" id="email" name ="email" placeholder="Email" class="form__input" />
   </div>
   <input class="btn" type="submit" value="Recover Password"/>
    </form>
             
    </div>
</div>
</div>
</div>
</div>
</div>';

include 'bg_background.php';
?>



