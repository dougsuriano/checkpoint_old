/******************************************************************************
users.js
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
    var userBeingEdited;
    loadUsers();
    
    function loadUsers() {
        $.getJSON('../api/users/', function(data) {
            $('#results').html("<table id = 'userTable'>")
            $('#userTable').append("<table><tr><th>Id</th><th>Username</th><th>First</th><th>Last</th><th>Level</th><th>Actions</th></tr>");
            for (var i = 0; i < data.length; i++) {
                $('#userTable').append("<tr><td> " + data[i].id + "</td><td>" + data[i].username + "</td><td>" + data[i].first + "</td><td>" + data[i].last + "</td><td>" + data[i].level + "</td><td><button class = 'edit' id = 'edit" + data[i].id + "'>Edit</button><button class = 'delete' id = 'delete" + data[i].id + "'>Delete</button></td></tr>");
            }
            $('#userTable').append("</table>");
            $('.delete').click(function() {
               if (confirm("Are you sure you want to delete this User?")) {
                   var id = event.target.id.substr(6);
                   deleteUser(id);
               } 
            });
            
            $('.edit').click(function() {
                var id = event.target.id.substr(4);
                beginEditUser(id);
            })
        }); 
    }
    
    
    function createUser() {
        $('#errorText').html("");
        var user = new Object();
        user.username = $('#userUsername').val();
        user.password = $('#userPassword').val();
        user.first = $('#userFirst').val();
        user.last = $('#userLast').val();
        user.level = $('#userLevel').val();
        var jsonData = JSON.stringify(user);

        request = $.ajax({
            url: "/api/users/",
            type: "post",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadUsers();
            $('#userUsername').val("");
            $('#userPassword').val("");
            $('#userFirst').val("");
            $('#userLast').val("");
            $( "#form" ).dialog( "close" );
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    function editUser() {
        $('#errorText').html("");
        var user = new Object();
        
        user.username = $('#userUsername').val();
        user.password = $('#userPassword').val();
        user.first = $('#userFirst').val();
        user.last = $('#userLast').val();
        user.level = $('#userLevel').val();
        
        
        var jsonData = JSON.stringify(user);

        request = $.ajax({
            url: "/api/users/" + userBeingEdited,
            type: "put",
            data: jsonData
        });

        // callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadUsers();
            $('#userUsername').val("");
            $('#userPassword').val("");
            $('#userFirst').val("");
            $('#userLast').val("");
            $( "#form" ).dialog( "close" );
            editing = false;
        });

        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // log the error to the console
                $('#errorText').html("An error occured. Please check your input and try again");
        });
    }
    
    
    $("#userForm").submit(function() {
        if (editing) {
            editUser();
        }
        else {
           createUser(); 
        }
        
        return false;
    });
    
    $("#create").click(function() {
        $( "#form" ).dialog();
        return false;
    });
    
    function beginEditUser(id) {
        editing = true;
        $.getJSON('../api/user/' + id, function(data) {
            
            for (var i = 0; i < data.length; i++) {
                $('#userUsername').val(data[i].username);
                $('#userFirst').val(data[i].first);
                $('#userLast').val(data[i].last);
                $('#userLevel').val(data[i].level);
                userBeingEdited = data[i].id;
            }
            $( "#form" ).dialog();
        });
        
    }
    
    function deleteUser(id) {
        request = $.ajax({
            url: "/api/users/" + id,
            type: "delete",
        });
        request.done(function (response, textStatus, jqXHR){
            // log a message to the console
            loadUsers();
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



