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

$get_platform_options = ("select genval, description from db_ims.generic_value where category = 'Platform'");
$result = mysqli_query($con,$get_platform_options) or die('Error! Could not connect to database');


// Creating Selection Display
$platform_display = '<select name="platform" id="platform" class="form-control form_data" aria-label="platform"><option value="">---SELECT ONE---</option>';

while($row = mysqli_fetch_array($result)) {        
        $platform_display = $platform_display.'<option value="'.$row['genval'].'">'.$row['description'].'</option>';
}

$platform_display = $platform_display.'</select>';
       
$title = "DB-IMS Dashboard - Connect";

$add_header = '<link href= "css/connectionform.css" rel="stylesheet"><script src="js/connectdb_js.js" charset="utf-8" Content-Type="text/html"></script>';


$scipt = '<script>
$(document).ready(function(){
$("#confirm").attr("disabled","disabled");

var current_fs, next_fs, previous_fs; //fieldsets
var opacity;
var new_con_id;

$("#platform").change(function() {
    var x = $(this).val();
    console.log(x);
    if(x == "OR"){
        $("#orc-db-div").show();
        console.log("show this");
    } else {
        $("#orc-db-div").hide();
        console.log("hide that");
    }
});


$(".next").click(function(){

current_fs = $(this).parent();
next_fs = $(this).parent().next();

//Add Class Active
$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

//show the next fieldset
next_fs.show();
//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
"display": "none",
"position": "relative"
});
next_fs.css({"opacity": opacity});
},
duration: 600
});

$("#area").val(
"Platform : "+$("#platform").val()+"\n"+
"Host IP : "+$("#host_address").val()+"\n"+
"Oracle Database : "+$("#orcdb").val()+"\n"+
"Port : "+$("#port").val()+"\n"+
"Username : "+$("#username").val()+"\n"+
"Password : "+$("#password").val()+"\n"
); 

});


$(".test").click(function(){

current_fs = $(this).parent();
next_fs = $(this).parent().next();

//Add Class Active
$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

//show the next fieldset
next_fs.show();
//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
"display": "none",
"position": "relative"
});

next_fs.css({"opacity": opacity});
},
duration: 600
});

// saving data

$.when(connect_to_db_fxn()).done(function(res) {
    console.log("step 3");
    new_con_id = res;
    console.log(new_con_id);
    $("#confirmbtn").css({"color":"white", "background":"skyblue"});
    $("#confirmbtn").removeAttr("disabled");
    $("#previous").css({"color":"white", "background":"#616161"})
    $("#previous").removeAttr("disabled");
});

});


$(".confirmbtn").click(function(){

current_fs = $(this).parent();
next_fs = $(this).parent().next();

//Add Class Active
$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

//show the next fieldset
next_fs.show();
//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
"display": "none",
"position": "relative"
});

next_fs.css({"opacity": opacity});
},
duration: 600
});
});


$(".previous").click(function(){

current_fs = $(this).parent();
previous_fs = $(this).parent().prev();

//Remove class active
$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

//show the previous fieldset
previous_fs.show();

//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
"display": "none",
"position": "relative"
});
previous_fs.css({"opacity": opacity});
},
duration: 600
});
});

$(".radio-group .radio").click(function(){
$(this).parent().find(".radio").removeClass("selected");
$(this).addClass("selected");
});

$(".submit").click(function(){

$.when(rename_db_con_fxn(new_con_id)).done(() => location.reload());

})

});</script>';


$content = '<!-- MultiStep Form -->
    <div class="row justify-content-center mt-0">
        <div class="col-11 col-sm-9 col-md-7 col-lg-6 text-center p-0 mt-3 mb-2">
            <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                <h2><strong>Connecting to Database Environment</strong></h2>
                <p>Fill up all Details to proceed</p>
                <div class="row">
                    <div class="col-md-12 mx-0">
                        <form id="msform">
                        
                            <!-- progressbar -->
                         <ul id="progressbar">
                                <li class="active" id="add"><strong>ADD</strong></li>
                                <li id="confirm"><strong>CONFIRM</strong></li>
                                <li id="connect"><strong>CONNECT</strong></li>
                                <li id="save"><strong>ALIAS</strong></li>
                            </ul> 
                            
                            <!-- fieldsets -->
                            <fieldset>
                                <!--Connection Form-->
                                <div class="form-card" id="startfield">
                                    <h2 class="fs-title">Connection Details</h2>
                                    <p>Platform<span class="required">*</span></p>
                                    <div class="name-item">'.$platform_display.'</div>
                                    
                                    
                               <div class="conform-item">
                               <p>Connection Details<span class="required">*</span></p>
                               <div class="name-item">
                                 <input type="text" name="host_address" class="form-control form_data" placeholder="Host Address" id="host_address" required/>
                                 <input type="number" name="port" class="form-control form_data" id="port" placeholder="Port" required/>
                               </div>
                               </div>

                               <div class="conform-item">
                               <p>Database Credentials<span class="required">*</span></p>
                               <div class="name-item">
                                 <input type="text" name="username" class="form-control form_data" id="username" placeholder="Username"/>
                                 <input type="text" name="password" class="form-control form_data" id="password" placeholder="Password"/>
                               </div>
                               </div>

                               <div class="name-item" id ="orc-db-div" style="display: none;">
                                <p>Oracle Database: <span class="required">*</span></p>
                                 <input type="text" name="orcdb" class="form-control form_data" id="orcdb" placeholder="Oracle Database"/>
                               </div>
                          

                                </div> <input type="button" name="next" class="next action-button" value="Next" />
                            </fieldset>
                            <fieldset>
                            
                                <!--Summary-->
                                
                                <div class="form-card">
                                    <h2 class="fs-title">Connection Summary</h2> 
                                    <div class="conform-item">
                                    <textarea class="form-control" id="area" rows="" readonly></textarea>
                                    </div>
                                </div> 
                                <input type="button" name="previous" class="previous action-button-previous" value="Previous" /><input type="button" name="test" class="test action-button" value="Test" />
                            </fieldset>
                            
                            <fieldset>
                            
                            <!--Connect to DB-->
                            
                                 <div class="form-card">
                                    <h2 class="fs-title">Summary</h2> 
                                    <div class="conform-item">
                                    <textarea class="form-control" id="connect_area" rows="" readonly></textarea>
                                    </div>
                                </div> 
                                <input type="button" name="previous" id ="previous" class="previous action-button-disabled" value="Previous" disabled="true"/> <input type="button" name="confirmbtn" id="confirmbtn" class="confirmbtn action-button-disabled" value="Confirm" />
                            </fieldset>
                            <fieldset>
                                <div class="form-card">
                                    <h2 class="fs-title text-center">You are Almost There!</h2> <br><br>
                                    <div class="row justify-content-center">
                                        <div class="col-3"> <img src="https://img.icons8.com/color/96/000000/ok--v2.png" class="fit-image"> </div>
                                    </div> <br><br>
                                    <div class="row justify-content-center">
                                        <div class="col-7 text-center">
                                            <h5>Give it a Name </h5>
                                             <input type="text" name="db_alias" class="form-control form_data" placeholder="Alias" id="db_alias"/>
                                            <input type="button" name="submit" id="submit" class="submit action-button" value="Submit" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>'.$scipt;


include 'bg_dashboard.php';
?>


