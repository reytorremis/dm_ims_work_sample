$(document).ready(function(){
    $('.editBtn').on('click',function(){
        //hide edit span
        $(this).closest("tr").find(".editSpan").hide();
        
        //show edit input
        $(this).closest("tr").find(".editInput").show();
        
        //hide edit button
        $(this).closest("tr").find(".editBtn").hide();
        
        //show edit button
        $(this).closest("tr").find(".saveBtn").show();
        
    });
    
     $('.saveBtn').on('click',function(){
        var trObj = $(this).closest("tr");
        var data = new FormData();
        var con_id = $(this).closest("tr").attr('id');
        data.append("server", document.getElementById("server").value)
        data.append("port", document.getElementById("port").value)
        data.append("dbuser", document.getElementById("dbuser").value)
        data.append("dbpass", document.getElementById("dbpass").value)
        
//        console.log(con_id)
         
//         for (var pair of data.entries()) {
//            console.log(pair[0]+ ', ' + pair[1]); }
        
       var xhr = new XMLHttpRequest();
        xhr.open("POST","config_connect.php?q=edit&id="+con_id);
        
        xhr.onload = function(){
            var php_response = JSON.parse(this.response);
            if(php_response["status"] == "OK"){
                console.log(php_response["msg"]);
                    trObj.find(".editInput").hide();
                    trObj.find(".saveBtn").hide();
                    trObj.find(".editSpan").show();
                    trObj.find(".editBtn").show();
            } else{
                alert(php_response["msg"]);
                    trObj.find(".editInput").hide();
                    trObj.find(".saveBtn").hide();
                    trObj.find(".editSpan").show();
                    trObj.find(".editBtn").show();
            }
           
            }
            
        
        
        xhr.send(data);
        
        //PREVENT HTML FORM SUBMIT
        
        return false;
        
     });
     
    });