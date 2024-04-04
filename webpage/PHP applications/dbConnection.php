<?php
//all the variables defined here are accessible in all the files that include this one
//Connection Details
$host = 'localhost';
$user = 'db_ims_admin';
$password = 'p@ssw0rd';
$db_name = 'db_ims';

$con = new mysqli($host,$user,$password,$db_name)or die("Could not connect to mysql".mysqli_error($con));

?>