<?php
/******************************************************************************
currentrace.php
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
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        populateRaceSelect();
        getCurrentRace();
        var currentRace;
        function getCurrentRace() {
            $.getJSON('../api/races/current', function(data) {
                $('#current').html("<h2>The current race is " + data[0].raceName + ".</h2>");
                currentRace = data[0].id;
            });
        }
        
        function populateRaceSelect() {
            $.getJSON('../api/races/', function(data) {
                $.each(data, function() {
                    $('#raceSelect').append($("<option />").val(this.id).text(this.raceName));
                })
            });
        }
        
        $('#raceSelect').change(function() {
            request = $.ajax({
                url: "/api/races/current/" + $('#raceSelect').val(),
                type: "post",
            });
            request.done(function (response, textStatus, jqXHR){
                // log a message to the console
                getCurrentRace();
                
            });
        });
        
        $('#changeRace').click(function() {
            request = $.ajax({
                url: "/api/races/current/" + $('#raceSelect').val(),
                type: "post",
            });
            request.done(function (response, textStatus, jqXHR){
                // log a message to the console
                getCurrentRace();
                
            });
            return false;
        });
    });
    
    </script>
	<title>CHECKPOINT</title>
		<link rel="stylesheet" href="../app/css/checkpoint.css" type="text/css" charset="utf-8">
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>CHECKPOINT</h1>
		</div>
		<h3><a href = 'index.php'>Return to Main Menu</a></h3> 
        <div id = "current"> 
        </div>
        <div>
            <p>Select a race from below to change the race. Please note this change will affect all users currently logged into checkpoint.</p>
            <select id = "raceSelect">
            </select>
            <button id = 'changeRace'>Change Race</button>
        </div>
</div>
</body>
</html>
