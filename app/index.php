<?php
/******************************************************************************
index.php
Copyright (C) 2013  Doug Suriano & Matt Savoia

This file is part of Checkpoint - Race Management System

Checkpoint is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

Checkpoint is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Checkpoint.  If not, see <http://www.gnu.org/licenses/>.

/******************************************************************************/
include("session.php");
function greeting() {
    $greetings = array('Sup ', 'Heeeeyyy ', 'Yo ', 'Hello ', 'Greetings ', 'Howdy ');
    return $greetings[rand(0, 5)];
}
?>
<html>
<!DOCTYPE html>
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $.getJSON('../api/events/current', function(data) {
            $('#current').html("<h1>" + data[0].eventName + "</h1>");
        });
        
        $.getJSON('../api/races/current', function(data) {
            $('#currentRace').html("<h3>The current race is: " + data[0].raceName + "</h3>");
        });
    });
    
    </script>
	<title>CHECKPOINT</title>
		<link rel="stylesheet" href="../app/css/checkpoint.css" type="text/css" charset="utf-8">
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>CHECKPOINT - Alleycat Management System</h1>
		</div>
		<div id = "main_body">
        <div id = "current">
            Current Race
        </div>
        <div id = "currentRace"></div>
        <div class = "menu">
        <p><a href = 'races.php'>Manage Races</a></p>
        <p><a href = 'jobs.php'>Manage Jobs</a></p>
        <p><a href ='currentrace.php'>Set Current Race</a></p>
        <p><a href = 'enterracers.php'>Enter Racers in Race</a></p>
        
        <p><a href = 'dispatch.php'>Dispatch</a></p>
        <p><a href = 'results.php'>Results</a></p>
	</div>
    </div>
    <div id = "right_sidebar">
        <h2><?php echo greeting() . $_SESSION['first'];?></h2>

    <p><a href = "events.php">Manage Events</a></p>
    <p><a href = 'currentevent.php'>Set Current Event</a></p>
    <p><a href = 'racers.php'>Manage Racers</a></p>
    <p><a href = 'checkpoints.php'>Manage Checkpoints</a></p>
    <p><a href = 'users.php'>Manage Users</a></p>
    <p><a href = 'memcacheCheck.php'>Memcache Check</a></p>
    <p><a href = 'status.php'>PHP info()</a></p>
    <p><a href = "logout.php">Logout</a></p>
    </div>
    
</div>
<div id = "footer">
    <small>Copyright 2013 Doug Suriano. Checkpoint is Free software released under the GPL v2</small>
</div>
</body>
</html>