<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $title; ?></title>
        <?php echo $header; ?>
    </head>
    <body>
         <div id="wrapper">
              <?php echo $banner; ?>
            <nav id="navigation">
                <ul id="nav">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="idx_product_specs.php">Project</a></li>
                    <li><a href="idx_registration.php">Registration</a></li>  </ul>
            </nav>
             
            <div id="content_area">
                <?php echo $content; ?>
            </div>
            <footer>
                <p> &copy; All Right Reserved. </p>
            </footer>
       
        </div>
    </body>
</html>
