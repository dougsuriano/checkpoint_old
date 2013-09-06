/******************************************************************************
checkpoints.js
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
    
    var editing = false;
    var checkpointBeingEdited;
    loadCheckpoints();
    
    function loadCheckpoints() {
        $.getJSON('../api/checkpoints/', function(data) {
            $('#results').html("<table id = 'racerTable'>")
            $('#racerTable').append("<table><tr><th>Id</th><th>Name</th><th>Address</th><<th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                $('#racerTable').append("<tr><td> " + data[i].id + "</td><td>" + data[i].name + "</td><td>" + data[i].address + "</td><td><button class = 'edit' id = 'edit" + data[i].id + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
            }
            $('#racerTable').append("</table>");
            $('.delete').click(function() {
               if (confirm("Are you sure you want to delete this Racer?")) {
                   var id = event.target.id.substr(6);
                   deleteCheckpoint(id);
               } 
            });
            
            $('.edit').click(function() {
                var id = event.target.id.substr(4);
                beginEditCheckpoint(id);
            })
        }); 
    }
    
    
    function createCheckpoint() {
        $('#errorText').html("");
        var checkpoint = new Object();
        
        checkpoint.name = $('#checkpointName').val();
        checkpoint.address = $('#checkpointAddress').val();
        
        var jsonData = JSON.stringify(checkpoint);

        request = $.ajax({
            url: "/api/checkpoints/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadCheckpoints();
            $('#checkpointName').val("");
            $('#checkpointAddress').val("");
            $( "#form" ).dialog( "close" );
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    function editCheckpoint() {
        $('#errorText').html("");
        var checkpoint = new Object();
        
        checkpoint.name = $('#checkpointName').val();
        checkpoint.address = $('#checkpointAddress').val();
        
        var jsonData = JSON.stringify(checkpoint);

        request = $.ajax({
            url: "/api/checkpoints/" + checkpointBeingEdited,
            type: "put",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadCheckpoints();
            $('#checkpointName').val("");
            $('#checkpointAddress').val("");
            $( "#form" ).dialog( "close" );
            editing = false;
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    
    $("#checkpointForm").submit(function() {
        if (editing) {
            editCheckpoint();
        }
        else {
           createCheckpoint(); 
        }
        
        return false;
    });
    
    $("#create").click(function() {
        $( "#form" ).dialog();
        return false;
    });
    
    function beginEditCheckpoint(id) {
        editing = true;
        $.getJSON('../api/checkpoint/' + id, function(data) {
            
            for (var i = 0; i < data.length; i++) {
                $('#checkpointName').val(data[i].racerNumber);
                $('#checkpointAddress').val(data[i].firstName);
                checkpointBeingEdited = data[i].id;
            }
            $( "#form" ).dialog();
        });
        
    }
    
    function deleteCheckpoint(id) {
        request = $.ajax({
            url: "/api/checkpoints/" + id,
            type: "delete",
        });
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRacers();
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



