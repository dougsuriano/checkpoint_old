$(document).ready(function() {
    var racerData;
    // Stuff to do as soon as the DOM is ready;
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
    
    
    
    function lookupRacerBoard(racerNumber) {
        $.getJSON('../../api/dispatch/' + racerNumber + '/speed', function(data) {
            doneLoading();
            //Check for errors
            if (data.error) {
                $('#results').html("<h1 class = 'red'>Error</h1>");
                $('#results').append("<h1>" + data.error + "</h1>");
                return;
            }
            
            racerData = data;
            //Draw base html
            $('#results').html("<div id = 'racerInfo'></div><h2>Board</h2><table id = 'board'><tr><th>Run #</th><th>Name</th><th>Code</th><th>Pickup</th><th>Stops</th><th>Pick Time</th><th>Complete Time</th><th>Status</th><th>Actions</th></tr></table></div>");
            drawRacerInfo();
            drawRacerData();
            drawBoard(true);
            drawActionButtons();
            $('#count').html("<h3>Racers on course</h3><h1><a href = '' id = 'racerListLink'> " + racerData.count + "</a></h1>");
            $('#racerListLink').click(function() {
                displayRacersOnCourse();
                return false;
            });
            
        });
    }
    
    //Reloads the racer board
    function reloadRacerBoard(racerNumber, actionMessage) {
        $.getJSON('../../api/dispatch/' + racerNumber + "/speed", function(data) {
            //Check for errors
            if (data.error) {
                $('#results').html("<h1 class = 'red'>Error</h1>");
                $('#results').append("<h1>" + data.error + "</h1>");
                return;
            }
            racerData = data;
            $('#results').html("<div id = 'racerInfo'></div><h2>Board</h2><table id = 'board'><tr><th>Run #</th><th>Name</th><th>Code</th><th>Pickup</th><th>Stops</th><th>Pick Time</th><th>Complete Time</th><th>Status</th><th>Actions</th></tr></table></div>");
            drawRacerInfo();
            drawRacerData();
            drawBoard(false);
            $('#results').append("<div id = 'actions'><h1>" + actionMessage + "</h1></div>");
            $('#count').html("<h3>Racers on course</h3><h1><a href = '' id = 'racerListLink'> " + racerData.count + "</a></h1>");
            $('#racerListLink').click(function() {
                displayRacersOnCourse();
                return false;
            });
        }); 
    }
    
    function dispatchFirstRun(runId) {
        $('#dialog').dialog({
            title: 'Dispatch',
            modal: true
        });
        
        $('#dialog').html("<h2 class = 'red'>Start Racer.</h2><p>This will start racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") on race. This will also begin the race clock.<p><button id = 'startButton'>Start Racer</button></p>");
        $('#startButton').button().click(function() {
            var requestData = new Object();
            
            requestData.racer = racerData.racer.id;
            requestData.run = runId;
        
            var jsonData = JSON.stringify(requestData);

            request = $.ajax({
                url: "/api/dispatch/speed/start",
                type: "post",
                data: jsonData
            });

            request.done(function (response, textStatus, jqXHR){
                $('#dialog').dialog('close');
                reloadRacerBoard(racerData.racer.racerNumber, "Racer " + racerData.racer.racerNumber + " has been started.");
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                alert (errorThrown);
            });
        });
        
    }
    
    function dispatchNextRun(runId) {

        var requestData = new Object();
            
        requestData.racer = racerData.racer.id;
        requestData.run = runId;
        
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/speed/next",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            reloadRacerBoard(racerData.racer.racerNumber, "Racer " + racerData.racer.racerNumber + " has been dispatched next run");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
    }
    
    function finish() {

        var requestData = new Object();
            
        requestData.racer = racerData.racer.id;
        
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/speed/finish",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            reloadRacerBoard(racerData.racer.racerNumber, "Racer " + racerData.racer.racerNumber + " has been marked as finished");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
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
        
    function canRacerFinish() {
        if (racerData.racer.status != 5) {
            return false;
        }
        for (var i = 0; i < racerData.board.length; i++) {
            if (racerData.board[i].status == 1) {
                    
                return false;
            }
        }
        return true;
    }
        
    function loading() {
        $('#progress').dialog({modal : true});
    }
        
    function doneLoading() {
        $('#progress').dialog('close');
    }
        
        
    function drawRacerData() {
        if (racerData.racer.status == 1) {
            $('#results').append("<div id = 'racerData'><p><table><tr><th>Start Time</th><th>End Time</th><th>Final Time</th></tr><tr><td><h1>--:--</h1></td> <td><h1>--:--</h1></td> <td><h1>--:--</h1></td>  </tr></table></p></div>");
        }
        else if (racerData.racer.status == 5) {
            $('#results').append("<div id = 'racerData'><p><table><tr><th>Start Time</th><th>End Time</th><th>Elapsed Time</th></tr><tr><td><h1>" + racerData.racer.startTime + "</h1></td> <td><h1>--:--</h1></td> <td><h1 class = 'grey'>" + racerData.racer.elapsed + "</h1></td>  </tr></table></p></div>");
        }
        else if (racerData.racer.status == 6) {
            $('#results').append("<div id = 'racerData'><p><p><table><tr><th>Start Time</th><th>End Time</th><th>Final Time</th></tr><tr><td><h1>" + racerData.racer.startTime + "</h1></td> <td><h1>" + racerData.racer.endTime + "</h1></td> <td><h1>" + racerData.racer.finalTime + "</h1></td>  </tr></table></p></div>");
        }
    }
        
    function drawRacerInfo() {
        $('#racerInfo').html("<h1>" + racerData.racer.racerNumber + " " + formatName(racerData.racer.firstName, racerData.racer.nickName, racerData.racer.lastName) + " (" + formatRaceStatus(racerData.racer.status) + ")</h1>");
        $('#racerInfo').append("<h3>" + formatSex(racerData.racer.sex) + " " + formatCategory(racerData.racer.category) + " from " + racerData.racer.city + ", " + racerData.racer.country + ".</h3>"); 

        
    }
        
    function drawBoard(actionButtons) {
        if (racerData.racer.status == 1) {
            for (var i = 0; i < racerData.board.length; i++) {
                if (actionButtons) {
                    $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td><button class = 'ui-button' id = 'dispatch" + racerData.board[i].id + "'>Dispatch</button></td></tr>");
                    $('#dispatch' + racerData.board[i].id).button().click(function() {
                        var runId = this.id;
                        runId = runId.substr(8);
                        dispatchFirstRun(runId);
                        return false; 
                    });
                }
                else {
                    $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td></td></tr>");
                }
                
            }
        }
        else if (racerData.racer.status == 2) {
            $('#board').append("<tr><td colspan = '9'> <h3>Racer was disqualified from race for reason: " + racerData.racer.notes + "</h3></td></tr>");
        }
        else if (racerData.racer.status == 7) {
            $('#board').append("<tr><td colspan = '9'> <h3>Racer has been marked as DNF.</h3></td></tr>");
        }
        else if (racerData.racer.status == 6) {
            for (var i = 0; i < racerData.board.length; i++) {
                $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td></tr>");
            }
        }
        else if (racerData.racer.status == 5) {
            for (var i = 0; i < racerData.board.length; i++) {
                if (racerData.board[i].status == 2 || racerData.board[i].status == 3) {
                    $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td></tr>");
                }
                else if ((racerData.board[i].status == 1)) {
                    if (actionButtons) {
                        $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td><button class = 'ui-button' id = 'dispatch" + racerData.board[i].id + "'>Dispatch</button></td></tr>");
                        $('#dispatch' + racerData.board[i].id).button().click(function() {
                            var runId = this.id;
                            runId = runId.substr(8);
                            dispatchNextRun(runId);
                            return false; 
                        });
                    }
                    else {
                        $('#board').append("<tr><td>" + racerData.board[i].id + "</td><td>" + racerData.board[i].name + "</td><td>" + racerData.board[i].code + "</td><td>" + racerData.board[i].pickUpCheckpoint + "</td><td>" + racerData.board[i].dropOff + "</td><td>" + racerData.board[i].pickTime + "</td><td>" + racerData.board[i].completeTime + "</td><td>" + racerData.board[i].status + "</td><td></td></tr>"); 
                    }
                    
                } 
            }
        }
    }
    
    function drawActionButtons() {
        if (racerData.racer.status == 6) {
            $('#results').append("<div id = 'actions'><button id = 'dqRacer'>DQ Racer</button>");
            $('#dqRacer').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>DQ Racer</h2><p>This will disqualify racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") from this race. If you have recieved cleareance  from an official, please detail the reason for the disqualification below.</p><p><input type = 'text' id = 'reason'><p><button id = 'dqButton'>DQ Racer</button></p>");
                $('#dqButton').button().click(function() {
                    dqRacer(racerData.racer.id, $('#reason').val());
                });
            });
        }
        if (racerData.racer.status == 5) {
            $('#results').append("<div id = 'actions'><button id = 'dqRacer'>DQ Racer</button><button id = 'dnfRacer'>Mark Racer as DNF</button><button id ='unstart'>Un-Start Rider</button>");
            $('#unstart').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>Un-Start Racer</h2><p>This will un-start racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") from this race. Please note this will erase all racer data about this racer. If you are sure you want to do this, put 666 in the textbox below.</p><p><input type = 'text' id = 'code'><p><button id = 'unstartButton'>DQ Racer</button></p>");
                $('#unstartButton').button().click(function() {
                    if ($('#code').val() == '666') {
                        unstartRacer();
                    }
                });
                
            });
            $('#dqRacer').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>DQ Racer</h2><p>This will disqualify racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") from this race. If you have recieved cleareance  from an official, please detail the reason for the disqualification below.</p><p><input type = 'text' id = 'reason'><p><button id = 'dqButton'>DQ Racer</button></p>");
                $('#dqButton').button().click(function() {
                    dqRacer(racerData.racer.id, $('#reason').val());
                });
            });
            $('#dnfRacer').button().click(function() {
                $('#dialog').dialog({modal: true});
                $('#dialog').html("<h2 class = 'red'>DNF Racer</h2><p>This will mark racer #" + racerData.racer.racerNumber + " (" + racerData.racer.firstName + ") as DNF. race. If the rider is sure, press the DNF racer button below..</p><p><button id = 'dnfButton'>DNF Racer</button></p>");
                $('#dnfButton').button().click(function() {
                    dnfRacer(racerData.racer.id);
                });
                
            });
            if (canRacerFinish()) {
                $('#actions').append("<button id = 'finish'>Finish Racer</button>");
                $('#finish').button().click(function() {
                    finish();
                })
            }
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
    
    function dnfRacer(racerNumber) {
        var requestData = new Object();
        
        requestData.racer = racerData.racer.id;
    
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/dnf",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#dialog').dialog('close');
            reloadRacerBoard(racerData.racer.racerNumber, "Racer #" + racerData.racer.racerNumber + " has been marked DNF from this race.");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
    }
    
    function unstartRacer() {
        var requestData = new Object();
        
        requestData.racer = racerData.racer.id;
    
        var jsonData = JSON.stringify(requestData);

        request = $.ajax({
            url: "/api/dispatch/unstart",
            type: "post",
            data: jsonData
        });

        request.done(function (response, textStatus, jqXHR){
            $('#dialog').dialog('close');
            reloadRacerBoard(racerData.racer.racerNumber, "Racer " + racerData.racer.racerNumber + " has been unstarted from this race.");
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            alert (errorThrown);
        });
    }
    
    function displayRacersOnCourse() {
        $('#racersOnCourse').dialog({height:350, width:500, title:"Racer List", modal:true}).html("<div id = 'racerList'></div>");
        $.getJSON('../../api/dispatch/course/', function(data) {
            if (data.length == 0) {
                $('#racerList').html("There are no racers on the course");
            }
            else {
                $('#racerList').html("<table id = 'racerListTable'><tr><th>Racer Number</th><th>First</th><th>Last</th><th>Elapsed Time</th></tr></table>");
                for (var i = 0; i < data.length; i++) {
                    $('#racerListTable').append("<tr><td>" + data[i].racerNumber + "</td><td>" + data[i].firstName + "</td><td>" + data[i].lastName + "</td><td>" + data[i].elapsed + "</td></tr>");
                
                }
 
            }
            
        });
        
    }
    
        
        
    
    
    
});
