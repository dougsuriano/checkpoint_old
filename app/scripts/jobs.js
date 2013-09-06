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
$(document).ready(function() {
    var currentEvent
    var checkpoints;
    var stops;
    $.getJSON('../api/events/current', function(data) {
        currentEvent = data[0].id;
        $('.banner').html("<h1>Races in " + data[0].eventName);
    });
    
    $.getJSON('../api/checkpoints/', function(data) {
        checkpoints = data;
        for (var i = 0; i < data.length; i++) {
            $('.checkpointList').append("<option value = '" + data[i].id + "'>" +  data[i].name + "</option>"); 
        }
    });
    
    getRacesForDropDown();
    
    function getRacesForDropDown() {
        $.getJSON('../api/races/', function(data) {
            for (var i = 0; i < data.length; i++) {
                $('#chooseEvent').append("<option value = '" + data[i].id + "'>" +  data[i].raceName + "</option>"); 
            }
            loadJobs($('#chooseEvent').val());
            $('#chooseEvent').change(function() {
                $('#results').html("");
                loadJobs($('#chooseEvent').val());
            });
            
        });
    }
     

    var editing = false;
    var jobBeingEdited;
    
    
    function loadJobs(race) {
        $.getJSON('../api/races/' + race + '/jobs', function(data) {
            $('#results').html("<table id = 'jobsTable'>")
            $('#jobsTable').append("<table><tr><th>Id</th><th>Name</th><th>Time Ready</th><th>Time Due</th><th>Payout</th><th>Late Payout</th><th>No Drop Payout</th><th>Pick Up</th><th>Stops</th><th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                $('#jobsTable').append("<tr><td> " + data[i].id + "</td><td>" + data[i].name + "</td><td>" + data[i].readyTime + "</td><td>" + data[i].timeDue + "</td><td>" + data[i].payout + "</td><td>" + data[i].latePayout + "</td><td>" + data[i].noDropPayout + "</td><td>" + getCheckpointName(data[i].pickUpCheckpoint) + "</td><td>" + data[i].stops + "</td><td><button class = 'edit' id = 'edit" + data[i].id + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
            }
            $('#racesTable').append("</table>");
            $('.delete').click(function() {
                if (confirm("Are you sure you want to delete this Job")) {
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
    
    
    
    function createJob() {
        $('#errorText').html("");
        var job = new Object();
        
        job.name = $('#jobName').val();
        job.race = $('#chooseEvent').val();
        job.readyTime = $('#jobReadyTime').val();
        job.timeDue = $('#jobTimeDue').val();
        job.payout = $('#jobPayout').val();
        job.latePayout = $('#jobLatePayout').val();
        job.noDropPayout = $('#jobNoDropPayout').val();
        job.pickUpCheckpoint = $('#jobPickCheckpoint').val();
        job.dropOffCheckpoints = stops;
        
        
        var jsonData = JSON.stringify(job);

        request = $.ajax({
            url: "/api/jobs/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadJobs($('#chooseEvent').val());
            $('#jobName').val('');
            $('#jobReadyTime').val('');
            $('#jobTimeDue').val('');
            $('#jobPayout').val('');
            $('#jobLatePayout').val('');
            $('#jobNoDropPayout').val('');
            $('#jobPickCheckpoint').val('');
            
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
        var job = new Object();
        
        job.name = $('#jobName').val();
        job.race = $('#chooseEvent').val();
        job.readyTime = $('#jobReadyTime').val();
        job.timeDue = $('#jobTimeDue').val();
        job.payout = $('#jobPayout').val();
        job.latePayout = $('#jobLatePayout').val();
        job.noDropPayout = $('#jobNoDropPayout').val();
        job.pickUpCheckpoint = $('#jobPickCheckpoint').val();
        job.dropOffCheckpoints = stops;
        
        
        
        var jsonData = JSON.stringify(job);

        request = $.ajax({
            url: "/api/jobs/" + raceBeingEdited,
            type: "put",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadJobs($('#chooseEvent').val());
            $('#jobName').val('');
            $('#chooseEvent').val('');
            $('#jobReadyTime').val('');
            $('#jobTimeDue').val('');
            $('#jobPayout').val('');
            $('#jobLatePayout').val('');
            $('#jobNoDropPayout').val('');
            $('#jobPickCheckpoint').val('');
            
            $( "#form" ).dialog( "close" );
            
            editing = false;
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
            $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    
    $("#jobForm").submit(function() {
        if (editing) {
            editJob();
        }
        else {
            createJob(); 
        }
        
        return false;
    });
    
    $("#create").click(function() {
        stops = new Array();
        $('#dropOffTable').html("");
        $( "#form" ).dialog({ minWidth: 500 });
        return false;
    });
    
    function beginEditsRace(id) {
        editing = true;
        $.getJSON('../api/races/' + id, function(data) {
            
            for (var i = 0; i < data.length; i++) {
                $('#raceName').val(data[i].raceName);
                $('#raceDateTime').val(data[i].raceDateTime);
                $('#raceType').val(data[i].raceType);
                $('#raceDispatchMode').val(data[i].dispatchMode);
                $('#raceStartStyle').val(data[i].startStyle);
                
                checkpointBeingEdited = data[i].id;
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
    
    $('#addStopButton').click(function() {
        stop = new Object();
        stop.checkpoint = $('#addStopSelect').val();
        stops.push(stop);
        checkpointName = getCheckpointName($('#addStopSelect').val());
        $('#dropOffTable').append("<tr><td>" + checkpointName + "</td><td></td></tr>");
        return false;
       
    });
    
    function getCheckpointName(id) {
        for (var i = 0; i < checkpoints.length; i++) {
            if (checkpoints[i].id == id) {
                return checkpoints[i].name;
            }
        }
        return 'crap!';
    }
    
    
});



