<?php
/******************************************************************************
checkpoint.php
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
        <script src="scripts/checkpoints.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>Checkpoints</h1>
		</div>
		<p><a href = "index.php">Back to Home</a></p>
        <p><button id = "create">New Checkpoint</button>
        <div id = "results">
            
	</div>
    <div id = "form">
        <div id = "errorText" class = "red"></div>
        <form id = "checkpointForm" method = "post" action = "somewhere.php">
            <label>Checkpoint Name</label><input type = "text" id = "checkpointName">
            <label>Checkpoint Address</label><input type = "text" id = "checkpointAddress">
            
            <input type="submit" value="Do it!" />
        </form>
    </div>
</div>
</body>
</html>