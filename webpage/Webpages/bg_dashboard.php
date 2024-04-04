<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="utf-8">
    <title><?php echo $title; ?> </title>
    <title>DB-IMS Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <!-- Favicon -->
    <link href= "Images/dbims_logo.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        #live-display li:hover:not(.header){
            background-color: #eee;
            max-height: inherit;
            opacity: 1;
          }
         
        #live-display .list-categories{
        list-style-type: none;
        padding: 0px;
        margin: 0px;
        max-height: 0px;
        opacity: 0;
        overflow: hidden;
        transition: opacity 300ms ease;
      }
        
    </style>
<!--     Libraries Stylesheet -->
    <link href= "lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href= "lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

<!--     Customized Bootstrap Stylesheet -->
     <link href="css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

<!--     Template Stylesheet -->
     <link href= "css/style.css" rel="stylesheet">
     
<!--     Test  -->
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" type="text/javascript"></script>
     <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" type="text/javascript"></script>
     
    
    <?php echo $add_header; ?>
    
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="home.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">DB-IMS Dash</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="Images/user_profiles/default_profile.png" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo $session_username; ?></h6>
                        <span>User</span>
                    </div>
                </div>
                <div class="sidenav navbar-nav w-100">
                    <a href="home.php" class="<?php echo $tab_home ?> nav-item nav-link"><i class="fa fa-home me-2"></i>HOME</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="<?php echo $tab_configure ?> nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-gear-fill me-2"></i>CONFIGURE</a>
                        <div class="tab dropdown-menu bg-transparent border-0">
                            <a href="add_connection_details.php" class="dropdown-item"><i class="fas fa-plus me-2"></i>ADD</a>
                            <a href="db_connection_edit.php?q=edit" class="dropdown-item"><i class="bi bi-pencil-fill me-2" ></i>EDIT</a>
                            <a href="db_connection_edit.php?q=remove" class="dropdown-item"><i class="bi bi-trash-fill me-2"></i>REMOVE</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                    <a href="#" class="<?php echo $tab_monitor ?> nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-speedometer2 me-2"></i>MONITOR</a>
                     <div class="dropdown-menu bg-transparent border-0">
                         <?php
                         include_once 'dbConnection.php';
                         $session_userid = $_SESSION["user_id"];
                         $query_bg_side = "select db_con_string_id, connection_name, paltform_val from db_ims.vw_db_connection_details_complete where connection_name is not null and user_id = '$session_userid'";
                         $result_bg_side = mysqli_query($con,$query_bg_side) or die('Error! Could not connect to database');
                         while($row = mysqli_fetch_array($result_bg_side)) {echo '<a href="monitor.php?dbid='.$row['db_con_string_id'].'&p='.$row['paltform_val'].'" class="dropdown-item"><i class="bi bi-clipboard2-pulse me-2"></i>'.$row['connection_name'].'</a>';} 
                         ?>
                    </div>
                    </div>                   
                   <a href="inventory.php" class="<?php echo $tab_inventory ?> nav-item nav-link"><i class="fa  fa-database me-2"></i>INVENTORY</a>
                    <div class="nav-item dropdown">
                    <a href="#" class="<?php echo $tab_analyze ?> nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-bar-chart-fill me-2" ></i>ANALYZE</a>
                     <div class="dropdown-menu bg-transparent border-0">
                         <a href="analyze_env.php" class="dropdown-item"><i class="fa fa-folder-open me-2"></i>Environment</a>
                         <div class="dropdown-divider"></div>
                         <?php
                         include_once 'dbConnection.php';
                         $session_userid = $_SESSION["user_id"];
                         $query_bg_side = "select db_con_string_id, connection_name, paltform_val from db_ims.vw_db_connection_details_complete where connection_name is not null and user_id = '$session_userid'";
                         $result_bg_side = mysqli_query($con,$query_bg_side) or die('Error! Could not connect to database');
                         while($row = mysqli_fetch_array($result_bg_side)) {echo '<a href="analyze_ind.php?dbid='.$row['db_con_string_id'].'&p='.$row['paltform_val'].'" class="dropdown-item"><i class="fa fa-database me-2"></i>'.$row['connection_name'].'</a>';} 
                         ?>
                    </div></div>
                    
                    <a href="logs.php" class="<?php echo $tab_help ?> nav-item nav-link"><i class="bi bi-question-diamond me-2"></i>LOGS</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="home.php" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <div class="wrap"> <div class="search">
                    <input class="form-control border-0" id="live-search" type="text" size="30" placeholder="Search Database Name">
                    </div>
                     <div id = "live-display"><ul style="list-style-type:none;"></ul></div>  
                    </div>
                    
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                  
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <?php
                         include_once 'dbConnection.php';
                         $session_userid = $_SESSION["user_id"];
                         $query_get_unread = "select notf_cnt from db_ims.vw_check_read_notif where user_id = '$session_userid' and notif_read = 'NO'";
                         $result_read = mysqli_query($con,$query_get_unread) or die('Error! Could not connect to database');
                         
                         if (mysqli_num_rows($result_read) > 0) {
                             while($row = mysqli_fetch_array($result_read)) {
                                 echo '<span class="badge">'.$row['notf_cnt'].'</span>';
                             }
                         }
                         ?>
                            
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notification</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            
                          <?php
                         include_once 'dbConnection.php';
                         $session_userid = $_SESSION["user_id"];
                         $query_notif = "select notif_link, message_text, notif_age from db_ims.notify_user where user_id = '$session_userid' and notif_read = 'NO' limit 5";
                         $result_notif = mysqli_query($con,$query_notif) or die('Error! Could not connect to database');
                         while($row = mysqli_fetch_array($result_notif)) 
                            {echo '<a href="'.$row['notif_link'].'" class="dropdown-item"><h6 class="fw-normal mb-0">'.$row['message_text'].'</h6><small>'.$row['notif_age'].'</small></a><hr class="dropdown-divider">';} 
                         ?>
                            <a href="profile.php?qpg=notification" class="dropdown-item text-center">See all notifications</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="Images/user_profiles/default_profile.png" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex"><?php echo $session_username; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="profile.php?qpg=profile" class="dropdown-item">My Profile</a>
                            <a href="auto_user_action.php?q=logout&loc=index.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

             <?php echo $content; ?>
            
            
            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="home.php">DB IMS</a>, All Right Reserved. 
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
    <script>
function fill(Value) {
        console.log(Value.trim());
	$('#live-search').val(Value.trim());
	$('#live-display').hide();
}

function redirect(db_con_string_id, platform) {
        window.location = 'monitor.php?dbid='+db_con_string_id+'&p='+platform
}

$(document).ready(function() {
   $("#live-search").keyup(function() {
       var name = $('#live-search').val();
       if (name == "") {
           $("#live-display").html("");
       }
       else {
           $.ajax({
               type: "GET",
               url: "extract_data.php?ex=search",
               data: {name: name},
               success: function(data) {
                    var x = "";
                for (var i in data) {
                    x = x + "<li onclick=redirect('" + data[i].id + "','" + data[i].p + "')>" + data[i].cn + "</li></a>";
            }
                   $("#live-display").html(x);
               },
               error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
      }
           });
       }
   });
});

</script>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>