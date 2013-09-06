<?php
include("session.php");
    
?>
<html>
<!DOCTYPE html>
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="dispatchers/dispatch_speed_ind.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    
    </script>
	<title>CHECKPOINT DISPATCHER</title>
		<link rel="stylesheet" href="/app/css/checkpoint.css" type="text/css" charset="utf-8">
        <link rel="stylesheet" href="dispatchers/dispatch_speed_ind.css" type="text/css" charset="utf-8">
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/black-tie/jquery-ui.css" type="text/css">
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>CHECKPOINT DISPATCH</h1>
		</div>
        <div id = "leftColumn">
            <p><a href = 'index.php'>Back to Menu</a></p>
        Racer Number
        <form id = "racerNumber">
            <input type = "text" id = "racerNumberField">
        </form>
        <div id = "count">
            
        </div>
        </div>
        <div id = "rightColumn">
            <div id = 'results'>
                <h3>Enter a racer number to begin.</h3>
        </div>
          		
</div>
<div id = "dialog">
    
</div>
<div id = "progress">
    <img src = 'dispatchers/loading.gif'>
</div>
<div id = 'racersOnCourse'>
</div>
</body>
</html>