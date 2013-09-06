<?php
/******************************************************************************
RaceController.php
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

RaceController
Race Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/Race.php');
require_once('../api/views/RaceView.php');
require_once('../api/models/Job.php');

class RaceController extends BaseController {
    
    public function getAllRaces() {
        $event = $this->getCurrentEvent();
        try {
            $sql = $this->db->prepare("SELECT * FROM races WHERE event = :event");
            $sql->bindParam(':event', $event->id);
            if ($sql->execute()) {
                $races = $sql->fetchAll(PDO::FETCH_CLASS, 'Race');
                $view = new RaceView();
                $view->races = $races;
                $view->generate();
            } 
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllRaces($event))", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
        
    }
    
    public function getOneRace($race) {
        try {
            $sql = $this->db->prepare("SELECT * FROM races WHERE id = :race");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $races = $sql->fetchAll(PDO::FETCH_CLASS, 'Race');
                $view = new RaceView();
                $view->races = $races;
                $view->generate();
            } 
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneRace($race)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function createRace() {
        try {
            $sql = $this->db->prepare("INSERT INTO races (event, raceName, raceDateTime, raceType, dispatchMode, startStyle, startTime) VALUES (:event, :raceName, :raceDateTime, :raceType, :startStyle, :dispatchMode, :startTime)");
            $sql->bindParam(':event', $this->requestParams['event']);
            $sql->bindParam(':raceName', $this->requestParams['raceName']);
            $sql->bindParam(':raceDateTime', $this->requestParams['raceDateTime']);
            $sql->bindParam(':raceType', $this->requestParams['raceType']);
            $sql->bindParam(':dispatchMode', $this->requestParams['dispatchMode']);
            $sql->bindParam(':startStyle', $this->requestParams['startStyle']);
            $sql->bindParam(':dispatchMode', $this->requestParams['dispatchMode']);
            $sql->bindParam(':startTime', $this->requestParams['startTime']);
            $sql->execute();
            $this->getOneRace($this->db->lastInsertId());
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("createRace", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function editRace($id) {
        try {
            $sql = $this->db->prepare("UPDATE races SET event = :event, raceName = :raceName, raceDateTime = :raceDateTime, raceType = :raceType, startStyle = :startStyle, dispatchMode = :dispatchMode, startTime = :startTime WHERE id = :id");
            $sql->bindParam(':event', $this->requestParams['event']);
            $sql->bindParam(':raceName', $this->requestParams['raceName']);
            $sql->bindParam(':raceDateTime', $this->requestParams['raceDateTime']);
            $sql->bindParam(':raceType', $this->requestParams['raceType']);
            $sql->bindParam(':startStyle', $this->requestParams['startStyle']);
            $sql->bindParam(':dispatchMode', $this->requestParams['dispatchMode']);
            $sql->bindParam(':startTime', $this->requestParams['startTime']);
            $sql->bindParam(':id', $id);
            $sql->execute();
            
            //Cool, now if race being updated is the current race, lets update the memcaache.
            
            if ($this->getCurrentRace()->id == $id) {
                $this->setCurrentRace($id);
            }
            $this->getOneRace($id);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editRace($id)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function deleteRace($id) {
        try {
            $sql = $this->db->prepare("DELETE FROM races WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteRace($id)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function addRacersToRace($race) {
        try {
            $this->db->beginTransaction();
            //First get information about the race
            $sql = $this->db->prepare("SELECT * FROM races WHERE id = :race");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $races = $sql->fetchAll(PDO::FETCH_CLASS, 'Race');
                $raceInfo = $races[0];
            }
            //Now loop through all the racers
            $racers = $this->requestParams['racers'];
            foreach($racers as $racer) {
                //First check if racer is in race already
                $sql = $this->db->prepare("SELECT id FROM racesEntry WHERE racer = :racer AND race = :race");
                $sql->bindParam(':racer', $racer['id']);
                $sql->bindParam(':race', $race);
                if ($sql->execute()) {
                    $results = $sql->fetchAll(PDO::FETCH_ASSOC);
                    if (sizeof($results) != 0) {
                        $view = new RaceView();
                        $view->badRequest();
                        return;
                    }
                }
                //Alright cool, she/he are not in the race, let's add em!
                $sql = $this->db->prepare("INSERT INTO racesEntry (racer, race, status) VALUES (:racer, :race, :status)");
                $sql->bindParam(':racer', $racer['id']);
                $sql->bindParam(':race', $race);
                $sql->bindValue(':status', RACE_ENTRY_ENTERED_STATUS);
                $sql->execute();
                $racesEntryId = $this->db->lastInsertId();
                $logDescription = "Added racer " . $racer['id'] . " to race # $race.";
                $this->logIntoRaceEventTable($race, $racer['id'], 'addRacerToRace', $logDescription);
                
                if ($raceInfo->raceType == RACE_TYPE_WORK_SIM) {
                    
                    echo $racesEntryId;
                    $sql = $this->db->prepare("UPDATE racesEntry SET status = :racingStatus WHERE id = :id");
                    $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
                    $sql->bindParam(':id', $racesEntryId);
                    $sql->execute();
                }
                //If the race type is a INDIVIDUAL_TIME race, we need to create the runs for the racer
                //For worksim, racer starts with an empty board, but not individual_race
                else if ($raceInfo->raceType == RACE_TYPE_INDIVIDUAL_TIME) {
                    //Get all the jobs for the race
                    $sql = $this->db->prepare("SELECT * FROM jobs WHERE race = :race");
                    $sql->bindParam(':race', $race);
                    $sql->execute();
                    $jobs = $sql->fetchAll(PDO::FETCH_CLASS, 'Job');
                    
                    //For each job create a run
                    foreach ($jobs as $job) {
                        do {
                            $sql = $this->db->prepare("INSERT INTO runs (race, code, job, racer, status) VALUES (:race, :code, :job, :racer, :status)");
                            $sql->bindParam(':race', $race);
                            $sql->bindValue(':code', $this->generateCode());
                            $sql->bindParam(':job', $job->id);
                            $sql->bindParam(':racer', $racer['id']);
                            $sql->bindValue(':status', RUN_STATUS_ASSIGNED_NOT_DISPATCHED);
                        } while (!$sql->execute());
                        $runId = $this->db->lastInsertId();
                        //Now create the run's checkpoints
                        $sql = $this->db->prepare("SELECT * FROM jobsCheckpoints WHERE job = :job");
                        $sql->bindParam(':job', $job->id);
                        $sql->execute();
                        while($checkpoint = $sql->fetch(PDO::FETCH_ASSOC)) {
                            $innerSql = $this->db->prepare("INSERT INTO runsCheckpoints (run, checkpoint) VALUES (:run, :checkpoint)");
                            $innerSql->bindParam(':run', $runId);
                            $innerSql->bindParam(':checkpoint', $checkpoint['checkpoint']);
                            $innerSql->execute();
                        }
                            
                        
                        
                    }
                    
                    
                    
                }
            }
            $this->db->commit();
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("addRacerToRace($race)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //This will completley remove the racer from a race. 
    public function removeRacersFromRace($race) {
        try {
            $this->db->beginTransaction();
            //First get information about the race
            $sql = $this->db->prepare("SELECT * FROM races WHERE id = :race");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $races = $sql->fetchAll(PDO::FETCH_CLASS, 'Race');
                $raceInfo = $races[0];
            }
            $racers = $this->requestParams['racers'];
            foreach($racers as $racer) {
                $sql = $this->db->prepare("DELETE FROM racesEntry WHERE racer = :racer AND race = :race");
                $sql->bindParam(':racer', $racer['id']);
                $sql->bindParam(':race', $race);
                $sql->execute();
                $logDescription = "Removed racer from race with id #$race";
                $this->logIntoRaceEventTable($race, $racer['id'], 'removeRacersFromRace', $logDescription);
                //Remove all the runs the racer has assigned to them
                $sql = $this->db->prepare("DELETE FROM runs WHERE racer = :racer AND race = :race");
                $sql->bindParam(':racer', $racer['id']);
                $sql->bindParam(':race', $race);
                $sql->execute();
                
                
            }
            $this->db->commit();
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("removeRacersFromRace($race)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function dqRacerFromRace() {
        try {
            $sql = $this->db->prepare("UPDATE racesEntry SET status = :dqStatus, notes = :reason WHERE racer = :racer AND race = :race");
            $reason = $this->requestParams['reason'];
            $racer = $this->requestParams['racer'];
            $race = $this->getCurrentRace()->id;
            $sql->bindValue(':dqStatus', RACE_ENTRY_DQ_STATUS);
            $sql->bindParam(':reason', $reason);
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $logDescription = "Racer was DQ'd from the event for $reason";
            $this->logIntoRaceEventTable($race, $racer, 'dqRacerFromRace', $logDescription);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("dqRacerFromRace()", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    public function dnfRacerFromRace() {
        try {
            $sql = $this->db->prepare("UPDATE racesEntry SET status = :dnfStatus WHERE racer = :racer AND race = :race");
            $racer = $this->requestParams['racer'];
            $race = $this->getCurrentRace()->id;
            $sql->bindValue(':dnfStatus', RACE_ENTRY_DNF_STATUS);
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $logDescription = "Racer was marked as DNF from the event.";
            $this->logIntoRaceEventTable($race, $racer, 'dnfRacerFromRace', $logDescription);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("dqRacerFromRace()", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    
    public function unDqRacerFromRace($race, $racer, $reason) {
        try {
            $sql = $this->db->prepare("UPDATE racesEntry SET status = :notDqStatus, notes = :reason WHERE racer = :racer AND race = :race");
            $sql->bindValue(':notDqStatus', RACE_ENTRY_RACING_STATUS);
            $sql->bindParam(':reason', $reason);
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $logDescription = "Racer #$racer was un-DQ'd from the event because $reason";
            $this->logIntoRaceEventTable($race, $racer, 'unDqRacerFromRace', $logDescription);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("unDqRacerFromRace($race, $racer, $reason)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //Starts the clock on a racer, sets points to 0.00 and set's thier status to racing
    public function startRacer($race, $racer) {
        try {
            //Racer must be in RACE_ENTRY_ENTERED_STATUS for them to start
            $sql = $this->db->prepare("SELECT status FROM racesEntry WHERE racer = :racer AND race = :race");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $status = $sql->fetchAll(PDO::FETCH_ASSOC);
                if ($status[0] != RACE_ENTRY_ENTERED_STATUS) {
                    $logDescription = "Racer #$racer was attempted to be started on race #$race but he is not elgible to start this race.";
                    $this->logIntoRaceEventTable($race, $racer, 'startRacerClock', $logDescription);
                    $view = new RaceView();
                    $view->badRequest();
                    return;
                }
                //Alright cool, racer is good to start
                $sql = $this->db->prepare("UPDATE racesEntry SET startTime = NOW(), status = :racingStatus, points = 0.00 WHERE race = :race, racer = :racer");
                $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                $logDescription = "Racer #$racer has started race #$race.";
                $this->logIntoRaceEventTable($race, $racer, 'startRacerClock', $logDescription);
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("startRacerClock($race, $racer)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //Removes the start time, sets the points null, and sets racer back to entered status
    //Please note this is not reversible, so be sure to prompt user before doing this shit.
    
    public function unStartRacer() {
        try {
            $this->db->beginTransaction();
            //Racer must be in RACE_ENTRY_RACING_STATUS for them to be un-started
            $racer = $this->requestParams['racer'];
            $race = $this->getCurrentRace()->id;
            
            $sql = $this->db->prepare("SELECT status FROM racesEntry WHERE racer = :racer AND race = :race");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $status = $sql->fetch(PDO::FETCH_ASSOC);
                if ($status['status'] != RACE_RACING_STATUS) {
                    $logDescription = "Racer #$racer was attempted to be un-started on race #$race but he is not elgible cause he is not currently racing.";
                    $this->logIntoRaceEventTable($race, $racer, 'unStartRacer', $logDescription);
                    $view = new RaceView();
                    $view->badRequest();
                    $this->db->commit();
                    return;
                }
                //Alright cool, racer is good to un-start
                $sql = $this->db->prepare("UPDATE racesEntry SET startTime = null, status = :notRacingStatus, points = null WHERE race = :race AND racer = :racer");
                $sql->bindValue(':notRacingStatus', RACE_ENTRY_ENTERED_STATUS);
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                $logDescription = "Racer #$racer has been un-started from race #$race.";
                $this->logIntoRaceEventTable($race, $racer, 'unStartRacer', $logDescription);
                
                //Now Remove all of thier runs too
                $sql = $this->db->prepare("UPDATE runs SET status = :status, pickTime = NULL, completeTime = NULL WHERE race = :race AND racer = :racer");
                $sql->bindValue(':status', RUN_STATUS_ASSIGNED_NOT_DISPATCHED);
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                
                $sql = $this->db->prepare("UPDATE runsCheckpoints INNER JOIN runs ON runsCheckpoints.run = runs.id  SET runsCheckpoints.timeDropped = NULL WHERE race = :race AND racer = :racer");
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                
                //Should cascade delete the other runsCheckpoints

                $this->db->commit();
            }
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("unStartRacer()", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //Starts all racers who have the status of RACE_ENTRY_ENTERED_STATUS
    public function startAllRacers($race) {
        try {
            $sql = $this->db->prepare("UPDATE racesEntry SET startTime = NOW(), points = 0.00, status = :racingStatus WHERE race = :race AND status = :enteredStatus");
            $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
            $sql->bindParam(':race', $race);
            $sql->bindValue(':enteredStatus', RACE_ENTRY_ENTERED_STATUS);
            $sql->execute();
            $logDescription = "All elgible racers have been started for race #$race.";
            $this->logIntoRaceEventTable($race, $racer, 'unStartRacer', $logDescription);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("startAllRacers($race, $racer)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //This is a giant fucking bazooka, be careful when calling this guy
    public function unStartAllRacers($race) {
        try {
            $sql = $this->db->prepare("UPDATE racesEntry SET startTime = null, points = null, status = :notRacingStatus WHERE race = :race AND status = :racingStatus");
            $sql->bindValue(':notRacingStatus', RACE_RACING_STATUS);
            $sql->bindParam(':race', $race);
            $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
            $sql->execute();
            $logDescription = "All elgible racers have been un-started for race #$race.";
            $this->logIntoRaceEventTable($race, $racer, 'unStartRacer', $logDescription);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("unStartAllRacers($race, $racer)", $ex);
            $view = new RaceView();
            $view->badRequest();
        }
    }
    
    //1 racer finishes race
    public function racerFinishRace($race, $racer) {
        try {
            $this->db->beginTransaction();
            //Check to make sure the racer is actually currently racing
            $sql = $this->db->prepare("SELECT status FROM racesEntry WHERE racer = :racer AND race = :race");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $status = $sql->fetchAll(PDO::FETCH_ASSOC);
                if ($status[0] != RACE_RACING_STATUS) {
                    $logDescription = "Racer #$racer was attempted to be marked finished but is not currently racing.";
                    $this->logIntoRaceEventTable($race, $racer, 'racerFinishRace($race, $racer)', $logDescription);
                    $view = new RaceView();
                    $view->badRequest();
                    return;
                }
                $sql = $this->db->prepare("UPDATE racesEntry SET status = :finishStatus, endTime = NOW() WHERE race = :race AND racer = :racer");
                $sql->bindValue(':finishStatus', RACE_FINISHED_STATUS);
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                
                //Update the final Time
                $sql = $this->db->prepare("UPDATE racesEntry SET finalTime = TIMEDIFF(endTime - startTime) WHERE race = :race AND racer = :racer");
                $sql->bindParam(':race', $race);
                $sql->bindParam(':racer', $racer);
                $sql->execute();
                
                //Calculate the Points
                
            }
        }
        catch (PDOException $ex) {
            
        }
    }
    
    public function displayCurrentRace() {
        $race = $this->getCurrentRace();
        $view = new RaceView();
        $view->races = array($race);
        $view->generate();
    }
    
    public function userSetCurrentRace($race) {
        $this->setCurrentRace($race);
    }
    
    
}

?>