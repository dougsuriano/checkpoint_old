<?php
/******************************************************************************
racers.php
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

?>
<html>
<!DOCTYPE html>
<head>
	<title>CHECKPOINT</title>
		<link rel="stylesheet" href="../app/css/checkpoint.css" type="text/css" charset="utf-8">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script src="scripts/racers.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>Racers</h1>
		</div>
		<p><a href = "index.php">Back to Home</a></p>
        <p><button id = "create">New Racer</button>
        <div id = "results">
            
	</div>
    <div id = "form">
        <div id = "errorText" class = "red"></div>
        <form id = "racerForm" method = "post" action = "somewhere.php">
            <label>Racer Number</label><input type = "text" id = "racerNumber">
            <label>First Name</label><input type = "text" id = "racerFirst">
            <label>Last Name</label><input type = "text" id = "racerLast">
            <label>Nick Name</label><input type = "text" id = "racerNick">
            <label>City</label><input type = "text" id = "racerCity">
            <label>State</label><input type = "text" id = "racerCountry">
            <label>Sex</label><select id = "racerSex"><option value = "M">Male</option><option value = "F">Female</option></select>
            <label>Category</label><select id = "racerCategory"><option value = "1">Messenger</option><option value = "2">Ex-Messenger</option><option value = "3">Non-Messenger</option></select>
            <label>Bike Type</label><select id = "racerBikeType"><option value = "1">Track Bike</option><option value = "2">Non-Track Bike</option></select>
            <label>Racer Paid?</label><input type = "checkbox" id = "racerPaid"><br \>
            <input type="submit" value="Do it!" />
        </form>
    </div>
</div>
</body>
</html>