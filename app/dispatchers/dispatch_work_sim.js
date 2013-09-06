$(document).ready(function() {
    var racerData;
    var checkpoints;
    
    var runsCheckpoints;
    // Stuff to do as soon as the DOM is ready;
    
    hasRaceStarted();
    getCheckpoints();
    
    updateTitle();

    $('#racerNumberField').keyup(function() {
        if ($('#racerNumberField').val().length == 3) {
            $('#racerNumberField').attr("disabled", true);
            //Now Get the racer Info
            lookupRacerBoard($('#racerNumberField').val());
            $('#racerNumberField').val("");
            $('#racerNumberField').attr("disabled", false);
            loading();
        }
    });
    
    function updateTitle() {
        $.getJSON('../api/events/current', function(eventdata) {
            $.getJSON('../api/races/current', function(data) {
                $('.banner').html("<h1>" + eventdata[0].eventName + " - DISPATCH - " + data[0].raceName + "</h1>");
            });
        }); 
    }
    
    
    
    function hasRaceStarted() {
        $.getJSON('../api/dispatch/time', function(data) {
            if (data.raceStarted == 0) {
                $('#dialog').dialog(
                    { modal:true,
                        closeOnEscape:false
                    }).parent().find('.ui-dialog-titlebar-close').hide();;
                    $(".ui-dialog-titlebar-close", $(this).parent()).hide();
                $('#dialog').html("<h1>Race has not started</h1><h3>Race will be starting in " + data.raceTime + "</h3><button id = 'check'>Check Again</button>");
                $('#check').button().click(function() {
                    hasRaceStarted();
                })
            }
            else {
                $('#dialog').dialog('close');
            }
        });
    }
    
    function getCheckpoints() {
        $.getJSON('../api/checkpoints/', function(data) {
            checkpoints = data;
        });
    }
    
    
    function lookupRacerBoard(racerNumber) {
        $.getJSON('../../api/dispatch/' + racerNumber + '/worksim', function(data) {
            doneLoading();
            //Check for errors
            if (data.error) {
                $('#results').html("<h1 class = 'red'>Error</h1>");
                $('#results').append("<h1>" + data.error + "</h1>");
                return;
            }
            
            racerData = data;
            //Draw base html
            $('#results').html("<div id = 'racerInfo'></div><div id ='racerActions'></div><table id = 'board'><tr><th>id</th><th>Name</th><th>Code</th><th>Pickup</th><th>Pick Time</th><th>Drops</th><th>Drop Time</th><th>Complete Time</th><th>Status</th><th>Due</th><th>Determination</th><th>Payout</th><th>Actions</th></tr></table><div id = 'racerPoints'></div></div>");
            drawRacerInfo();
            drawBoard(true);
            drawActionButtons();
            drawPoints();
            
        });
    }
    
    //Reloads the racer board
    function reloadRacerBoard(racerNumber, actionMessage) {
        $.getJSON('../../api/dispatch/' + racerNumber + '/worksim', function(data) {
            //Check for errors
            if (data.error) {
                $('#results').html("<h1 class = 'red'>Error</h1>");
                $('#results').append("<h1>" + data.error + "</h1>");
                return;
            }
            racerData = data;
            $('#results').html("<div id = 'racerInfo'></div><div id ='racerActions'></div><table id = 'board'><tr><th>id</th><th>Name</th><th>Code</th><th>Pickup</th><th>Pick Time</th><th>Drops</th><th>Drop Time</th><th>Complete Time</th><th>Status</th><th>Due</th><th>Determination</th><th>Payout</th><th>Actions</th></tr></table><div id = 'racerPoints'></div></div>");
            drawRacerInfo();
            drawBoard(false);
            drawPoints();
            $('#racerActions').html("<h2 class = 'green'>" + actionMessage + "</h2>");
            
        }); 
    }
    

    
    
    
    function dispatchJob(job) {
        var requestData = new Object();
            
        requestData.racer = racerData.racer.id;
        requestData.job = job;
        
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/worksim/dispatch",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#dialog').dialog('close');
            actionMessage = "Racer #" + requestData.racer.racerNumber + " has been dispatched job #" + job;
            reloadRacerBoard(racerData.racer.racerNumber, actionMessage)
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            theError = jQuery.parseJSON(jqXHR.responseText);
            $('#dialog').html("<h2 class = 'red'>" + theError.errorMessage + "</h2>");
        });
    }
    
    function dropJob(code, checkpoint) {
        var requestData = new Object();
            
        requestData.racer = racerData.racer.id;
        requestData.code = code;
        requestData.checkpoint = checkpoint;
        
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/worksim/drop",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#dialog').dialog('close');
            actionMessage = "Racer #" + racerData.racer.racerNumber + " has dropped off job with code " + code;
            reloadRacerBoard(racerData.racer.racerNumber, actionMessage)
            
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            theError = jQuery.parseJSON(jqXHR.responseText);
            $('#dialog').html("<h2 class = 'red'>" + theError.errorMessage + "</h2>");
        });
    }
    

    
    function formatName(first, nick, last) {
        if (nick.length > 0) {
            return first + " '" + nick + "' " + last; 
        }
        else {
            return first + " " + last;
        }
    }
    
    function formatSex(sex) {
        if (sex == 'M') {
            return 'Male';
        }
        else {
            return 'Female';
        }
    }
    
    function formatCategory(status) {
        switch (status) {
        case '1':
            return "Messenger";
            break;
        case 2:
            return "Ex-Messenger";
            break;
        case 3:
            return "Non-Messenger";
            break;
        }
    }
    
    function formatRaceStatus(status) {
        switch (status) {
        case '1':
            return '<span class = "grey">Not Yet Raced</span>';
            break;
        case '2':
            return '<span class = "red">Disqualified</span>';
            break;
        case '3':
            return '<span class = "red">Racer Scratched</span>';
            break;
        case '4':
            return '<span class = "red">Racer Dropped</span>';
            break;
        case '5':
            return '<span class = "orange">Racing</span>';
            break;
        case '6':
            return '<span class = "green">Finished</span>';
            break;
        case '7':
            return '<span class = "red">DNF</span>';
            
        }
    }
    
    function formatDetermination(determination) {

        switch (determination) {
            case null:
            return "n/a";
            break;
        case "1":
            return "On Time";
            break;
        case "2":
            return "Late";
            break;
            }
    }
    
    function formatStatus(status) {

        switch (status) {
            case "2":
                return "Picked";
                break;
            case "3":
                return "Dropped"
                break;
            }
    }
        
        
    function loading() {
        $('#progress').dialog({modal : true});
    }
        
    function doneLoading() {
        $('#progress').dialog('close');
    }
        

        
    function drawRacerInfo() {
        $('#racerInfo').html("<h1>" + racerData.racer.racerNumber + " " + formatName(racerData.racer.firstName, racerData.racer.nickName, racerData.racer.lastName) + " (" + formatRaceStatus(racerData.racer.status) + ")</h1>");
        $('#racerInfo').append("<h3>" + formatSex(racerData.racer.sex) + " " + formatCategory(racerData.racer.category) + " from " + racerData.racer.city + ", " + racerData.racer.country + ".</h3>");
    }
        
    function drawBoard(actionButtons) {
        if (racerData.racer.status == 2) {
           $("#board").append("<tr><td colspan = '13'><h1 class = 'red'>Racer has been disqualifed with reason: " + racerData.racer.notes + "</h1></td></tr>"); 
        }
        else if (racerData.racer.status == 7) {
            $("#board").append("<tr><td colspan = '13'><h1>>Racer has been marked DNF.</h1></td></tr>"); 
        }
        else if (racerData.racer.status == 5) {
            if (racerData.board.length > 0) {
                last = -1;
                for (var i = 0; i < racerData.board.length; i++) {
                    var cellClass = getBackgroundColor(racerData.board[i].status);
                    if (last != racerData.board[i].id) {
                        if (actionButtons) {
                            $("#board").append("<tr><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].id + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].name + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].code + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].pickUpCheckpoint + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].pickTime + "</td><td class = '" + cellClass + "' >" + racerData.board[i].dropOff + "</td><td class = '" + cellClass + "' >" + racerData.board[i].dropOffTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].completeTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + formatStatus(racerData.board[i].status) + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].dueTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + formatDetermination(racerData.board[i].determination) + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].payout + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'><button id = 'edit" + racerData.board[i].code + "' class = 'edit'>Edit</button></td></tr>");
                        }
                        else {
                            $("#board").append("<tr><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].id + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].name + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].code + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].pickUpCheckpoint + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].pickTime + "</td><td class = '" + cellClass + "' >" + racerData.board[i].dropOff + "</td><td class = '" + cellClass + "' >" + racerData.board[i].dropOffTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].completeTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + formatStatus(racerData.board[i].status) + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].dueTime + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + formatDetermination(racerData.board[i].determination) + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'>" + racerData.board[i].payout + "</td><td class = '" + cellClass + "'  rowspan = '" + racerData.board[i].stops + "'></td></tr>");
                        }
                        
                    }
                    else {
                        $("#board").append("<tr><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].dropOffTime + "</td></tr>");
                    }
                    last = racerData.board[i].id;
                    $('#edit' + racerData.board[i].code).button().click(function() {
                        code = $(this).attr('id').substr(4);
                       beginEditRun(code);
                   
                    });
                
                }
                
            }
            
        }
    }
    

    
    function drawActionButtons() {
        if (racerData.racer.status == 5) {
            $('#racerActions').html("<p><button id = 'dispatch'>Dispatch Run</button><button id = 'drop'>Drop Run</button><button id = 'dq'>DQ Racer</button><button id = 'dnf'>DNF Racer</button></p>");
            $('#dispatch').button().click(function() {
                $('#dialog').dialog({modal:true, width:600, height:400});
                $('#dialog').html("<h1>Dispatch Order</h1>");
                $('#dialog').append("<h3>Enter the package id</h3><input type = 'text' id = 'job'><p><button id = 'dispatchRun'>Dispatch</button>");
                $('#dispatchRun').button().click(function() {
                    dispatchJob($('#job').val());
                })
            });
            $('#drop').button().click(function() {
                $('#dialog').dialog({modal:true, width:600, height:400});
                $('#dialog').html("<h1>Drop Job</h1>");
                $('#dialog').append("<h3>Enter the code on the package and select the drop checkpoint</h3><p><input type = 'text' id = 'code'><p><select id = 'checkpoints'></select></p><button id = 'dropButton'>Drop!</button>");
                for (var i = 0; i < checkpoints.length; i++) {
                    $('#checkpoints').append("<option value = '" + checkpoints[i].id + "'>" + checkpoints[i].name + "</option>");
                }
                $('#dropButton').button().click(function() {
                    dropJob($('#code').val(), $('#checkpoints').val());
                });
                
                
            });
            $('#dq').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>DQ Racer</h2><p>This will disqualify racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") from this race. If you have recieved cleareance  from an official, please detail the reason for the disqualification below.</p><p><input type = 'text' id = 'reason'><p><button id = 'dqButton'>DQ Racer</button></p>");
                $('#dqButton').button().click(function() {
                    dqRacer(racerData.racer.id, $('#reason').val());
                });
            });
            $('#dnf').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>DNF Racer</h2><p>This will mark racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") as DNF. race. If the rider is sure, press the DNF racer button below..</p><p><button id = 'dnfButton'>DNF Racer</button></p>");
                $('#dnfButton').button().click(function() {
                    dnfRacer(racerData.racer.id);
                }); 
            });
            
        }
    }
    
    function dqRacer(racerNumber, reason) {
        if (reason.length < 1) {
            alert("Please provide a reason why racer is being DQ'd.");
            return;
        }
        var requestData = new Object();
        
        requestData.racer = racerData.racer.id;
        requestData.reason = reason;
    
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/dq",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#dialog').dialog('close');
            reloadRacerBoard(racerData.racer.racerNumber, "Racer " + racerData.racer.racerNumber + " has been DQ'd from this race.");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
    }
    
    function beginEditRun(code) {
        $.getJSON('../api/dispatch/worksim/run/' + code, function(data) {
            $('#editRun').dialog({modal:true, title: "Edit Run " + code, width:800, height:500});
            $('#editRun').html("<div id = 'editContainer>'><div id = 'editLeft'></div><div id = 'editRight'>Hi</div><div id = 'editBottomLeft'><button id = 'removeRun'>Remove Run</button></div><div id = 'editBottomRight'><button id = 'saveRun'>Save Changes</button></div></div>");
            
            $('#removeRun').button().click(function() {
                if (confirm("Are you sure you want to remove this run from the racer's board? This will delete all information about the run and is not reversible.")) {
                    removeRun(code);
                }
            })
            $('#saveRun').button().click(function() {
                editRun(code);
            });
            
            
            $('#editLeft').html("<p><label>Status</label><select id = 'runStatus'></select>");
            if (data.board[0].status == '2') {
                $('#runStatus').append("<option value = '2' selected>Picked</option><option value = '3'>Dropped</option>");
            }
            else {
                $('#runStatus').append("<option value = '2'>Picked</option><option value = '3' selected>Dropped</option>");
            }
            $('#editLeft').append("<p><label>Pick Time</label><input id = 'runPick' type = 'text' value ='" + data.board[0].pickTime + "'>");
            $('#editLeft').append("<p><label>Complete Time</label><input id = 'runComplete' type = 'text' value ='" + data.board[0].completeTime + "'>");
            $('#editLeft').append("<p><label>Determination</label><select id = 'runDetermination'><option value = '1'>On Time</option><option value = '2'>Late</option><option value = '3'>Not Dropped</option>");
            $('#editLeft').append("<p><label>Payout</label><input id = 'runPayout' type = 'text' value ='" + data.board[0].payout + "'>");
            $('#editRight').html("<table id = 'drops'></table>");
            if (data.board.length > 0) {
               $('#drops').append("<tr><th>#</th><th>Checkpoint</th><th>Time Dropped</th></tr>");
               runsCheckpoints = new Array();
               for (var i = 0; i < data.board.length; i++) {
                   $('#drops').append("<tr><td>" + (i + 1) + "</td><td>" + data.board[i].dropOff + "</td><td><input type = 'text' value ='" + data.board[i].dropOffTime + "' id = 'rc" + data.board[i].runsCheckpointsId + "'></td></tr>");
                   runsCheckpoints.push(data.board[i].runsCheckpointsId);
               }
            }
            
        });
    }
    
    function editRun(code) {
        var requestData = new Object();
        
        requestData.racer = racerData.racer.id;
        requestData.code = code;
        requestData.status = $('#runStatus').val();
        requestData.pickTime = $('#runPick').val();
        requestData.completeTime = $('#runComplete').val();
        requestData.determination = $('#runDetermination').val();
        requestData.payout = $('#runPayout').val();
        
        requestData.runsCheckpoints = new Array();
        for (var i = 0; i < runsCheckpoints.length; i++) {
            var rcTemp = new Object;
            rcTemp.timeDropped = $('#rc' + runsCheckpoints[i]).val();
            rcTemp.id = runsCheckpoints[i];
            requestData.runsCheckpoints.push(rcTemp);
        }
        
        
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/worksim/run/" + code,
            type: "put",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#editRun').dialog('close');
            reloadRacerBoard(racerData.racer.racerNumber, "Run " + code + " has been edited.");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
        
    }
    
    function drawPoints() {
        $('#racerPoints').html("<h2>Current Earnings $" + racerData.racer.points + "</h2>");
    }
    
    function getBackgroundColor(status) {
        switch (status) {
        case "2":
            return "backgroundOrange";
            break;
        case "3":
            return "backgroundGreen";
            break;
        }
    }
    
    function removeRun(code) {


        request = $.ajax({
            url: "/api/dispatch/worksim/run/" + code,
            type: "delete"
        });

        request.done(function (response, textStatus, jqXHR){
            $('#editRun').dialog('close');
            reloadRacerBoard(racerData.racer.racerNumber, "Run " + code + " has been deleted.");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
        
    }
    
   
    
        
        
    
    
    
});
