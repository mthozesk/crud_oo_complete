<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/hu.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/hu_vars.js"></script>
    <script>
	    $.get(URL + 'session.php', function(data) { 
            if( data == "expired" ) { 
                location.href = "hu_login.html"; 
            }
        });
		
        
        var id = getID();
		console.log(id);
        
        function retrievePage() {
            xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (xhttp.readyState == 4 && xhttp.status == 200) {
                    document.getElementById("body").innerHTML = xhttp.responseText;
                }
            };
            xhttp.open("GET", URL + "http://csis.svsu.edu/~mthozesk/cis355wi19/temp/customers.class.php?id=" + id, true);
            xhttp.send();
        }
        
        function selectUser() { 
            id = document.getElementById("user_id").value;
            xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (xhttp.readyState == 4 && xhttp.status == 200) {
                    document.getElementById("body").innerHTML = xhttp.responseText;
                }
            };
            xhttp.open("GET", URL + "http://csis.svsu.edu/~mthozesk/cis355wi19/temp/customers.class.php?id=" + id, true);
            xhttp.send();
        }
    </script>
</head>

<body onload="retrievePage();">
    <div class="padding">
        <div id="body">
        </div>
    </div>
</body>
</html>
