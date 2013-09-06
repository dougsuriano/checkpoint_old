<?php
/******************************************************************************
events.php
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
        <script src="scripts/events.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>Events</h1>
		</div>
		<p><a href = "index.php">Back to Home</a></p>
        <p><button id = "create">Create Event</button>
        <div id = "results">
            
	</div>
    <div id = "form">
        <form id = "eventForm" method = "post" action = "somewhere.php">
            <label>Event Name</label><input type = "text" id = "eventName">
            <label>Event Start Date</label><input type = "text" id = "eventStartDate">
            <label>Event End Date</label><input type = "text" id = "eventEndDate">
            <label>Event City</label><input type = "text" id = "eventCity">
            <input type="submit" value="Go" />
        </form>
    </div>
</div>
</body>
</html>