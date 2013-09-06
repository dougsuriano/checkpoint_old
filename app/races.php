<?php
/******************************************************************************
races.php
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
        <script src="scripts/races.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>Races for</h1>
		</div>
		<p><a href = "index.php">Back to Home</a></p>
        <p><button id = "create">New Race</button>
        <div id = "results">
            
	</div>
    <div id = "form">
        <div id = "errorText" class = "red"></div>
        <form id = "raceForm" method = "post" action = "somewhere.php">
            <label>Race Name</label>
            <input type = "text" id = "raceName">
            <label>Race Datetime (YYYY-MM-DD)</label>
            <input type = "text" id = "raceDateTime">
            <label>Race Type</label>
            <select id = 'raceType'>
                <option value = '1'>Speed</option>
                <option value = '2'>Work Sim</option>
            </select>
            <label>Dispatch Mode
            <select id = "raceDispatchMode">
                <option value = "1">Work Sim Dispatcher</option>
                <option value = "2">Speed Dispatcher</option>
            </select>
            <label>Start Style</label>
            <select id = "raceStartStyle">
                <option value = "1">Le Mann</option>
                <option value = "2">Individual</option>
            </select>
            <label>Start Time</label>
            <input type = "text" id = "raceStartTime">
                
            
            <input type="submit" value="Do it!" />
        </form>
    </div>
</div>
</body>
</html>