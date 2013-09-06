/******************************************************************************
races.js
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
$(document).ready(function() {
    
    
    var currentEvent
     $.getJSON('../api/events/current', function(data) {
         currentEvent = data[0].id;
         $('.banner').html("<h1>Races in " + data[0].eventName);
     });
     
    
    var editing = false;
    var raceBeingEdited;
    loadRaces();
    
    function checkIfRaceStarted() {
        
    }
    
    function loadRaces() {
        $.getJSON('../api/races/', function(data) {
            $('#results').html("<table id = 'racesTable'>")
            $('#racesTable').append("<table><tr><th>Id</th><th>Name</th><th>Race Date</th><th>Race Type</th><th>Dispatch Mode</th><th>Start Style</th><th>Start Time</th><th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                $('#racesTable').append("<tr><td> " + data[i].id + "</td><td>" + data[i].raceName + "</td><td>" + data[i].raceDateTime + "</td><td>" + getRaceType(data[i].raceType) + "</td><td>" + getDispatchMode(data[i].dispatchMode) + "</td><td>" + getStartStyle(data[i].startStyle) + "</td><td>" + data[i].startTime + "</td><td><button class = 'edit' id = 'edit" + data[i].id + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
            }
            $('#racesTable').append("</table>");
            $('.delete').click(function() {
               if (confirm("Are you sure you want to delete this Racer")) {
                   var id = event.target.id.substr(6);
                   deleteRace(id);
               } 
            });
            
            $('.edit').click(function() {
                var id = event.target.id.substr(4);
                beginEditRace(id);
            })
        }); 
    }
    
    function getRaceType(id) {
        switch (id) {
        case "1":
            return "Individual Timed Race";
            break;
        case "2":
            return "Work Sim";
            break;
        }
    }
    
    function getDispatchMode(id) {
        switch (id) {
        case "1":
            return "Work Sim Dispatcher";
            break;
        case "2":
            return "Speed Dispatcher";
            break; 
        }
    }
    
    function getStartStyle(id) {
        switch (id) {
        case "1":
            return "Le Mans";
            break;
        case "2":
            return "Individual";
            break; 
        }
    }
    
    
    function createRace() {
        $('#errorText').html("");
        var race = new Object();
        
        race.raceName = $('#raceName').val();
        race.raceDateTime = $('#raceDateTime').val();
        race.raceType = $('#raceType').val();
        race.dispatchMode = $('#raceDispatchMode').val();
        race.startStyle = $('#raceStartStyle').val();
        race.startTime = $('#raceStartTime').val();
        race.event = currentEvent
        
        var jsonData = JSON.stringify(race);

        request = $.ajax({
            url: "/api/races/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRaces();
            $('#raceName').val();
            $('#raceDateTime').val();
            $('#raceType').val();
            $('#raceDispatchMode').val();
            $('#raceStartStyle').val();
            $('#raceStartTime').val("");
            $( "#form" ).dialog( "close" );
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    function editRace() {
        $('#errorText').html("");
        var race = new Object();
        
        race.raceName = $('#raceName').val();
        race.raceDateTime = $('#raceDateTime').val();
        race.raceType = $('#raceType').val();
        race.dispatchMode = $('#raceDispatchMode').val();
        race.startStyle = $('#raceStartStyle').val();
        race.startTime = $('#raceStartTime').val();
        race.event = currentEvent
        
        
        var jsonData = JSON.stringify(race);

        request = $.ajax({
            url: "/api/races/" + raceBeingEdited,
            type: "put",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRaces();
            $('#raceName').val('');
            $('#raceDateTime').val('');
            $('#raceStartTime').val('');
            
            $( "#form" ).dialog( "close" );
            editing = false;
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    
    $("#raceForm").submit(function() {
        if (editing) {
            editRace();
        }
        else {
           createRace(); 
        }
        
        return false;
    });
    
    $("#create").click(function() {
        $( "#form" ).dialog();
        return false;
    });
    
    function beginEditRace(id) {
        editing = true;
        $.getJSON('../api/races/' + id, function(data) {
            
            for (var i = 0; i < data.length; i++) {
                $('#raceName').val(data[i].raceName);
                $('#raceDateTime').val(data[i].raceDateTime);
                $('#raceType').val(data[i].raceType);
                $('#raceDispatchMode').val(data[i].dispatchMode);
                $('#raceStartStyle').val(data[i].startStyle);
                $('#raceStartTime').val(data[i].startTime);
                
                raceBeingEdited = data[i].id;
            }
            $( "#form" ).dialog();
        });
        
    }
    
    function deleteRace(id) {
        request = $.ajax({
            url: "/api/races/" + id,
            type: "delete",
        });
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRaces();
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
            console.error(
                "The following error occured: "+
                textStatus, errorThrown
            );
        });
    }
    
});



