<?php
/******************************************************************************
jobs.php
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
        <script src="scripts/jobs.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>Jobs</h1>
		</div>
		<p><a href = "index.php">Back to Home</a></p>
        <p>Choose Race: <select id = "chooseEvent"></select></p>
        <p><button id = "create">New Job</button>
        <div id = "results">
            
	</div>
    <div id = "form">
        <div style = 'width:50%; float:left;'>
        <div id = "errorText" class = "red"></div>
        <form id = "jobForm" method = "post" action = "somewhere.php">
            <label>Job Name</label>
            <input type = "text" id = "jobName">
            <label>Ready Time</label>
            <input type = "text" id = "jobReadyTime">
            <label>Time Due</label>
            <input type = "text" id = "jobTimeDue">
            <label>Payout</label>
            <input type = "text" id = "jobPayout">
            <label>Late Payout</label>
            <input type = "text" id = "jobLatePayout">
            <label>No Drop Payout</label>
            <input type = "text" id = "jobNoDropPayout">
            
        </div>
        <div style = 'width:50%; float:left;'>
            <label>Pick Up Checkpoint</label>
            <select class = "checkpointList" id = "jobPickCheckpoint"></select>
            <label>Drop Off</label>
            <table id = "dropOffTable">
                <tr>
                    <th>Checkpoint Name</th><th>Actions</th>
                </tr>
            </table>
            <select class = "checkpointList" id = "addStopSelect"></select>
            <button id = "addStopButton">Add Stop</button>
                
            
            <p><input type="submit" value="Do it!" /></p>
        </form>
        </div>
    </div>
</div>
</body>
</html>