/******************************************************************************
enter.js
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
    
    var racersIn;
    var racersOut;
    getRacesForDropDown();
    
    function getRacesForDropDown() {
        $.getJSON('../api/races/current', function(current) {
            
            $.getJSON('../api/races/', function(data) {
            
                for (var i = 0; i < data.length; i++) {
                    if (data[i].id == current.id) {
                        $('#chooseEvent').append("<option value = '" + data[i].id + "' selected>" +  data[i].raceName + "</option>"); 
                    }
                    else {
                        $('#chooseEvent').append("<option value = '" + data[i].id + "'>" +  data[i].raceName + "</option>"); 
                    }
                    
                }
                loadRaceEntryInfo($('#chooseEvent').val());
                $('#chooseEvent').change(function() {
                    $('#results').html("");
                    loadRaceEntryInfo($('#chooseEvent').val());
                });
            
            });
        });
        
    }
    
    function loadRaceEntryInfo(race) {
        $('#racersNot').html("<tr><th><input type = 'checkbox' id = 'notSelectAll'></th><th>Racer Name</th><th>Time</th></tr>");
        $('#notSelectAll').change(function() {
            if ($('#notSelectAll').prop('checked')) {
                for (var i = 0; i < racersOut.length; i++) {
                    $('#not' + i).prop('checked', true);
                } 
            }
            else {
                for (var i = 0; i < racersOut.length; i++) {
                    $('#not' + i).prop('checked', false);
                } 
            }
        });
    
        $('#racersIn').html("<tr><th><input type = 'checkbox' id = 'inSelectAll'></th><th>Racer Name</th><th>Time</th></tr>");
        $('#inSelectAll').change(function() {
            if ($('#inSelectAll').prop('checked')) {
                for (var i = 0; i < racersIn.length; i++) {
                    $('#in' + i).prop('checked', true);
                } 
            }
            else {
                for (var i = 0; i < racersIn.length; i++) {
                    $('#in' + i).prop('checked', false);
                } 
            }
        });
        $.getJSON('../api/races/' + race + '/racers/in', function(data) {
            racersIn = data;
            if (racersIn.length == 0) {
                $('#racersIn').append("<td colspan = '3'>No Racers entered in race</td>");
            }
            else {
                for (var i = 0; i < data.length; i++) {
                    $('#racersIn').append("<tr><td><input type = 'checkbox' id = 'in" + i + "'></td><td>" + data[i].racerNumber + " " + data[i].firstName + " '" + data[i].nickName + "' " + data[i].lastName + "</td><td></td></tr>");
                }
            }
        });
        
        $.getJSON('../api/races/' + race + '/racers/not', function(data) {
            racersOut = data;
            if (racersOut.length == 0) {
                $('#racersNot').append("<td colspan = '3'>No more racers.</td>");
            }
            else {
                for (var i = 0; i < data.length; i++) {
                    $('#racersNot').append("<tr><td><input type = 'checkbox' id = 'not" + i + "'></td><td>" + data[i].racerNumber + " " + data[i].firstName + " '" + data[i].nickName + "' " + data[i].lastName + "</td><td></td></tr>");
                }
            }
            
        });
    }
    
    
    
    function addRacersToRace() {
        var request = new Object();
        request.racers = new Array();
        for (var i = 0; i < racersOut.length; i++) {
            if ($('#not' + i).prop('checked')) {
                var racer = new Object();
                racer.id = racersOut[i].id;
                request.racers.push(racer);
            }
        }
        var jsonData = JSON.stringify(request);
        
        request = $.ajax({
            url: "/api/races/" + $('#chooseEvent').val() + "/racers/in",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            loadRaceEntryInfo($('#chooseEvent').val());
        });
    }
    
    function removeRacerFromRace() {
        
        var request = new Object();
        request.racers = new Array();
        console.log(racersIn);
        for (var i = 0; i < racersIn.length; i++) {
            if ($('#in' + i).prop('checked')) {
                var racer = new Object();
                racer.id = racersIn[i].id;
                request.racers.push(racer);
            }
        }
        var jsonData = JSON.stringify(request);
        
        request = $.ajax({
            url: "/api/races/" + $('#chooseEvent').val() + "/racers/not",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            loadRaceEntryInfo($('#chooseEvent').val());
        });
        
        
    }
    
    $('.addRacersButton').click(function() {
        addRacersToRace();
        return false; 
    });
    
    $('.removeRacersButton').click(function() {
        if (confirm("Are you sure you want to remove these races from the course? Please understand that ALL racer data will be deleted including runs a racer may have done or completed already.")) {
            removeRacerFromRace();
        }
       
        return false; 
    });
});
