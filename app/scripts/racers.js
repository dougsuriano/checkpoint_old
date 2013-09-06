/******************************************************************************
racers.js
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
    var racerBeingEdited;
    loadRacers();
    
    function loadRacers() {
        $.getJSON('../api/racers/', function(data) {
            $('#results').html("<h3>Total Racers: " + data.length + "</h3><table id = 'racerTable'>")
            $('#racerTable').append("<table><tr><th>Racer Number</th><th>Last</th><th>First</th><th>Nick</th><th>City</th><th>State</th><th>Sex</th><th>Category</th><th>Bike Type</th><th>Paid</th><th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                if (data[i].paid == 1) {
                    $('#racerTable').append("<tr><td class = 'lightGreen'> " + data[i].racerNumber + "</td><td class = 'lightGreen'>" + data[i].firstName + "</td><td class = 'lightGreen'>" + data[i].lastName + "</td><td class = 'lightGreen'>" + data[i].nickName + "</td><td class = 'lightGreen'>" + data[i].city + "</td><td class = 'lightGreen'>" + data[i].country + "</td><td class = 'lightGreen'>" + data[i].sex + "</td><td class = 'lightGreen'>" + getCategory(data[i].category) + "</td><td class = 'lightGreen'>" + formatBikeType(data[i].bikeType) + "</td><td class = 'lightGreen'>Paid</td><td class = 'lightGreen'><button class = 'edit' id = 'edit" + data[i].racerNumber + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
                }
                else {
                    $('#racerTable').append("<tr><td class = 'lightRed'> " + data[i].racerNumber + "</td><td class = 'lightRed'>" + data[i].firstName + "</td><td class = 'lightRed'>" + data[i].lastName + "</td><td class = 'lightRed'>" + data[i].nickName + "</td><td class = 'lightRed'>" + data[i].city + "</td><td class = 'lightRed'>" + data[i].country + "</td><td class = 'lightRed'>" + data[i].sex + "</td><td class = 'lightRed'>" + getCategory(data[i].category) + "</td><td class = 'lightRed'>" + formatBikeType(data[i].bikeType) + "</td><td class = 'lightRed'><strong>NOT PAID</strong></td><td class = 'lightRed'><button class = 'edit' id = 'edit" + data[i].racerNumber + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
                }
            }
            $('#racerTable').append("</table>");
            $('.delete').click(function() {
               if (confirm("Are you sure you want to delete this Racer?")) {
                   var id = event.target.id.substr(6);
                   deleteRacer(id);
               } 
            });
            
            $('.edit').click(function() {
                var id = event.target.id.substr(4);
                beginEditRacer(id);
            })
        }); 
    }
    
    function getCategory(id) {
        switch(id) {
        case "1":
            return "Messenger";
            break;
        case "2":
            return "Ex-Messenger";
            break;
        case "3":
            return "Non-Messenger";
        }
    }
    
    function createRacer() {
        $('#errorText').html("");
        var racer = new Object();
        
        racer.racerNumber = $('#racerNumber').val();
        racer.firstName = $('#racerFirst').val();
        racer.lastName = $('#racerLast').val();
        racer.nickName = $('#racerNick').val();
        racer.city = $('#racerCity').val();
        racer.country = $('#racerCountry').val();
        racer.sex = $('#racerSex').val();
        racer.category = $('#racerCategory').val();
        racer.bikeType = $('#racerBikeType').val();
        if ($('#racerPaid').is(':checked')) {
            racer.paid = 1;
        }
        else {
            racer.paid = 0;
        }
        
        
        var jsonData = JSON.stringify(racer);

        request = $.ajax({
            url: "/api/racers/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRacers();
            $('#racerNumber').val("");
            $('#racerFirst').val("");
            $('#racerLast').val("");
            $('#racerNick').val("");
            $('#racerCity').val("");
            $('#racerCountry').val("");
            $('#racerSex').val("");
            $('#racerCategory').val("");
            $( "#form" ).dialog( "close" );
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    function editRacer() {
        $('#errorText').html("");
        var racer = new Object();
        
        racer.racerNumber = $('#racerNumber').val();
        racer.firstName = $('#racerFirst').val();
        racer.lastName = $('#racerLast').val();
        racer.nickName = $('#racerNick').val();
        racer.city = $('#racerCity').val();
        racer.country = $('#racerCountry').val();
        racer.sex = $('#racerSex').val();
        racer.category = $('#racerCategory').val();
        racer.bikeType = $('#racerBikeType').val();
        if ($('#racerPaid').is(':checked')) {
            racer.paid = 1;
        }
        else {
            racer.paid = 0;
        }
        
        
        var jsonData = JSON.stringify(racer);

        request = $.ajax({
            url: "/api/racers/" + racerBeingEdited,
            type: "put",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadRacers();
            $('#racerNumber').val("");
            $('#racerFirst').val("");
            $('#racerLast').val("");
            $('#racerNick').val("");
            $('#racerCity').val("");
            $('#racerCountry').val("");
            $('#racerSex').val("");
            $('#racerCategory').val("");
            $( "#form" ).dialog( "close" );
            editing = false;
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    
    $("#racerForm").submit(function() {
        if (editing) {
            editRacer();
        }
        else {
           createRacer(); 
        }
        
        return false;
    });
    
    $("#create").click(function() {
        $( "#form" ).dialog();
        return false;
    });
    
    function beginEditRacer(id) {
        editing = true;
        $.getJSON('../api/racers/' + id, function(data) {
            
            for (var i = 0; i < data.length; i++) {
                $('#racerNumber').val(data[i].racerNumber);
                $('#racerFirst').val(data[i].firstName);
                $('#racerLast').val(data[i].lastName);
                $('#racerNick').val(data[i].nickName);
                $('#racerCity').val(data[i].city);
                $('#racerCountry').val(data[i].country);
                $('#racerSex').val(data[i].sex);
                $('#racerCategory').val(data[i].category);
                $('#racerBikeType').val(data[i].bikeType);
                if (data[i].paid == 1) {
                    $('#racerPaid').attr("checked",true);
                }
                else {
                    $('#racerPaid').attr("checked",false);
                }
                racerBeingEdited = data[i].racerNumber;
            }
            $( "#form" ).dialog();

        });
        
    }
    
    function deleteRacer(id) {
        request = $.ajax({
            url: "/api/racers/" + id,
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
    
    function formatBikeType(bikeType) {
        switch (bikeType) {
        case "1":
            return "Track Bike";
            break;
        case "2":
            return "Non-Track Bike";
            break;
        }
    }
    
});



