<?php
ob_start();


include_once 'dbConnection.php';
include_once 'pyfile.php';
$working_dir = getcwd();

if (@$_GET['q'] == "login") {
        $uinfo = addslashes(stripslashes($_POST['username']));
        $password = addslashes(stripslashes($_POST['password']));

        // get salted password
        //# query salt from db
        $get_salt_query = ("SELECT db_ims.get_salted_password('$uinfo','$password') as hashed_password");
        $result = mysqli_query($con,$get_salt_query) or die('Error! Could not connect to database');

        $count = mysqli_num_rows($result);

        //# get hashed password
        if($count==1){
        while($row = mysqli_fetch_array($result)) {
                $hashed_password = $row['hashed_password'];}
        }

        // get user id
        //# query user id from db
        $get_userud_query = ("SELECT db_ims.get_user_id('$uinfo') as userid");
        $result = mysqli_query($con,$get_userud_query) or die('Error! Could not connect to database');

        $count = mysqli_num_rows($result);

        //# assign user id to variable
        if($count==1){
        while($row = mysqli_fetch_array($result)) {
                $userid = $row['userid'];}
        }
        // query 
        $query_chk_for_creds = ("select 1 as user_validation from db_ims.vw_userinfo vu where user_id = '$userid' and userpassword = '$hashed_password' ");
        $result_chk = mysqli_query($con,$query_chk_for_creds) or die('Error! Could not connect to database');

        while($row = mysqli_fetch_array($result_chk)) {
                $uval = $row['user_validation'];}

        $query_usrn_email = ("select username, email_address from db_ims.vw_userinfo where user_id = '$userid' limit 1");
        $get_usrn_email = mysqli_query($con,$query_usrn_email) or die('Error! Could not connect to database');

        while($row = mysqli_fetch_array($get_usrn_email)) {
                $username = $row['username'];
        }      

        if($uval == 1){
            session_start();

            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $userid;

            header("location:home.php");
        }
        else{
            echo json_encode(array(
                'status' => 'Error',
                'msg' => 'Cannot Find Username, Email or Password. Make sure to supply correct detail'
                ));
        }
        }
else if (@$_GET['q'] == "register"){
        $email = addslashes(stripslashes($_POST['email']));
        
        $query = ("SELECT db_ims.validate_record_creation('$email') as result");
        
        $result = mysqli_query($con,$query) or die('Error! Could not connect to database');

        while($row = mysqli_fetch_array($result)) {
                $result_query = $row['result'];
        }

        if ($result_query == 1)
        {
            sleep(1);

            $query = "select username as uname, db_ims.get_new_password(user_id) as upassword from db_ims.vw_userinfo vu where email_address = '$email' limit 1";
            $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

            while($row = mysqli_fetch_array($execute_sql)) {
                $dbims_username = $row['uname'];
                $dbims_password = $row['upassword'];
            }

            $subject = 1;

            sleep(2);

            $args = [
            $py_file_mailer,
            $email,
            $dbims_username,
            $dbims_password,
            $subject
            ]; 

        // just a fancy way of avoiding a foreach loop
            $escaped_args = implode(" ", array_map("escapeshellarg", $args));
            $command = "$python $escaped_args 2>&1";
            $output = shell_exec($command);

        //    echo $output;

            if ($output == 200) {
                $status = 'OK';
                $message = 'Email Sent to '.$email;
            } else {
                $status = 'ERROR';
                $message = 'Failed to Sent Email';
            };        

        echo json_encode(array(
                'status' => $status,
                'msg' => $message
                ));  
       
        }
        else if ($result_query == 2){
            $message = 'Duplicate Email';
        }
        else {
            $message = 'Invalid Email';
        }
        
         header("location:idx_registration.php?qres=$message"); 
         
        }
else if (@$_GET['q'] == "recovery"){
        $email = addslashes(stripslashes($_POST['email']));
        
        $query = ("select db_ims.check_email_address_exists('$email') as result");
        
        $result = mysqli_query($con,$query) or die('Error! Could not connect to database');

   while($row = mysqli_fetch_array($result)) {
           $result_query = $row['result'];
   }

   if ($result_query == 0)
   {
           $subject = 2;

           $query = "select username as uname, db_ims.get_new_password(user_id) as upassword from db_ims.vw_userinfo vu where email_address = '$email' limit 1";
           $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');

           while($row = mysqli_fetch_array($execute_sql)) {
               $dbims_username = strval($row['uname']);
               $dbims_password = strval($row['upassword']);
           }

           $args = [
           $py_file_mailer,
           $email,
           $dbims_username,
           $dbims_password,
           $subject
           ]; 
           

       // just a fancy way of avoiding a foreach loop
           $escaped_args = implode(" ", array_map("escapeshellarg", $args));
           $command = "$python $escaped_args 2>&1";
           $output = shell_exec($command);

       //    echo $output;

           if ($output == 200) {
               $status = 'OK';
               $message = 'Email Sent to '.$email;
           } else {
               $status = 'ERROR';
               $message = 'Failed to Sent Email'.$output;
           };      


       }
       else {
               $status = 'ERROR';
               $message = 'Failed to Connect to Database Email';
       }

       $returnData = array(
                   'status' => $status,
                   'msg' => $message
               );
       echo json_encode($returnData);
}

else if (@$_GET['q'] == "logout") {
        session_start();

        if(isset($_SESSION["username"])){
        session_destroy();}

        $ref= @$_GET['loc'];
        header("location:$ref");
        
        
} else if (@$_GET['q'] == "change_password"){
    $email = addslashes(stripslashes($_POST['email']));
    $new_password = addslashes(stripslashes($_POST['new_password']));
    
    $query = "CALL db_ims.update_user_password('$email', '$new_password')";
    $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');
    
    
       $returnData = array(
                   'status' => 'OK',
                   'msg' => 'Successfully Updated Password'
               );
       echo json_encode($returnData);
    
} else if (@$_GET['q'] == "update_profile"){
    $email = addslashes(stripslashes($_POST['email']));
    $new_username = addslashes(stripslashes($_POST['username']));

    $query = "CALL db_ims.sp_update_user_name('$email', '$new_username')";
    $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');
    
    if(!isset($_SESSION["username"])){
        unset ($_SESSION["username"]);
        session_start();
        
        $_SESSION["username"] = $new_username;
    }
    
    
   $returnData = array( 'status' => 'OK');
   echo json_encode($returnData);
    
} else if (@$_GET['q'] == "update_password"){
    $email = addslashes(stripslashes($_POST['email']));
    $new_password = addslashes(stripslashes($_POST['password']));

    $query = "CALL db_ims.update_user_password('$email', '$new_password')";
    $execute_sql = mysqli_query($con,$query) or die('Error! Could not connect to database');
    
    
   $returnData = array( 'status' => 'OK');
   echo json_encode($returnData);
    
}

ob_end_flush();
?>