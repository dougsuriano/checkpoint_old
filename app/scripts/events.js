/******************************************************************************
events.js
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
    
    
    loadEvents();
    
    function loadEvents() {
        $.getJSON('../api/events/', function(data) {
            $('#results').html("<table id = 'eventTable'>")
            $('#eventTable').append("<table><tr><th>Id</th><th>Name</th><th>Start Date</th><th>End Date</th><th>City</th><th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                $('#eventTable').append("<tr><td>" + data[i].id + "</td><td>" + data[i].eventName + "</td><td>" + data[i].eventStartDate + "</td><td>" + data[i].eventEndDate + "</td><td>" + data[i].eventCity + "</td><td><button class = 'edit' id = 'edit" + data[i].id + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
            }
            $('#eventTable').append("</table>");
            $('.delete').click(function() {
               if (confirm("Are you sure you want to delete that event?")) {
                   var id = event.target.id.substr(6);
                   deleteEvent(id);
               } 
            });
            
            $('.edit').click(function() {
                $( "#form" ).dialog();
            })
        }); 
    }
    
    function createEvent() {
        var event = new Object();
        
        event.eventName = $('#eventName').val();
        event.eventStartDate = $('#eventStartDate').val();
        event.eventEndDate = $('#eventEndDate').val();
        event.eventCity = $('#eventCity').val();
        
        var jsonData = JSON.stringify(event);
        
        request = $.ajax({
            url: "/api/events/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadEvents();
            $('#eventName').val("");
            $('#eventStartDate').val("");
            $('#eventEndDate').val("");
            $('#eventCity').val("");
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
    
    
    $("#eventForm").submit(function() {
        createEvent();
        $( "#form" ).dialog( "close" );
        return false;
    });
    
    $("#create").click(function() {
        $( "#form" ).dialog();
        return false;
    })
    
    function deleteEvent(id) {
        request = $.ajax({
            url: "/api/events/" + id,
            type: "delete",
        });
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadEvents();
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



