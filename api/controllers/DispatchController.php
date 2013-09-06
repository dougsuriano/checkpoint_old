<?php
/******************************************************************************
CheckpointController.php
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

DispatchController
Dispatch Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/views/DispatchView.php');

class DispatchController extends BaseController {
    
    //Returns the racer info plus all current runs
    public function getRacerInfoAndCurrentBoardForSpeedRace($racer) {
        try {
            //Get the racer info
            $sql = $this->db->prepare("SELECT racers.id, racers.racerNumber, racers.firstName, racers.lastName, racers.nickName, racers.city, racers.country, racers.sex, racers.category, racesEntry.status, racesEntry.notes, DATE_FORMAT(racesEntry.startTime, '%k:%i:%s') AS startTime, DATE_FORMAT(racesEntry.endTime, '%k:%i:%s') AS endTime, DATE_FORMAT(racesEntry.finalTime, '%k:%i:%s') AS finalTime, racesEntry.points, TIME(TIMEDIFF(NOW(), racesEntry.startTime)) AS elapsed FROM racers INNER JOIN racesEntry ON racers.id = racesEntry.racer WHERE racerNumber = :racer AND race = :race");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $this->getCurrentRace()->id);
            if ($sql->execute()) {
                $racerInfo = $sql->fetch(PDO::FETCH_OBJ);
            }
            
            if (!$racerInfo) {
                $view = new DispatchView();
                $error = "Racer Number not found";
                $view->generateErrorView($error);
            }
            
            $sql = $this->db->prepare("SELECT runs.id, jobs.name, runs.race, runs.code, runs.job, runs.racer, runs.status, DATE_FORMAT(runs.pickTime, '%k:%i:%s') AS pickTime, DATE_FORMAT(runs.completeTime, '%k:%i:%s') AS completeTime, runs.determination, runs.payout, DATE_FORMAT(runs.finalTime, '%k:%i:%s') AS finalTime, dropCheckpoints.name AS dropOff, pickCheckpoints.name AS pickUpCheckpoint, DATE_FORMAT(runsCheckpoints.timeDropped, '%k:%i:%s') AS dropOffTime, 0 AS stops FROM runs 
INNER JOIN runsCheckpoints ON runs.id = runsCheckpoints.run 
INNER JOIN jobs ON runs.job = jobs.id 
INNER JOIN checkpoints AS pickCheckpoints ON pickCheckpoints.id = jobs.pickUpCheckpoint
INNER JOIN checkpoints AS dropCheckpoints ON dropCheckpoints.id = runsCheckpoints.checkpoint
WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racer) AND runs.race = :race ORDER BY runs.id");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $this->getCurrentRace()->id);
            if ($sql->execute()) {
                $board = $sql->fetchAll(PDO::FETCH_OBJ);
            }
            
            if (!$board) {
                $view = new DispatchView();
                $error = "Racer not entered in Race";
                $view->generateErrorView($error);
            }
            
            $view = new DispatchView();
            $view->racerInfo = $racerInfo;
            $view->board = $board;
            $view->courseCount = $this->getRacerOnCourseCount();
            $view->generateBoardForIndRace();
            
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRacerInfoAndCurrentBoard($racer)", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function getRacerInfoAndCurrentBoardForWorkSim($racer) {
        try {
            $currentRace = $this->getCurrentRace();
            //Get the racer info
            $sql = $this->db->prepare("SELECT racers.id, racers.racerNumber, racers.firstName, racers.lastName, racers.nickName, racers.city, racers.country, racers.sex, racers.category, racesEntry.status, racesEntry.notes, DATE_FORMAT(racesEntry.startTime, '%k:%i:%s') AS startTime, DATE_FORMAT(racesEntry.endTime, '%k:%i:%s') AS endTime, DATE_FORMAT(racesEntry.finalTime, '%k:%i:%s') AS finalTime, IFNULL(racesEntry.points, 0.00) AS points FROM racers INNER JOIN racesEntry ON racers.id = racesEntry.racer WHERE racerNumber = :racer AND race = :race");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $this->getCurrentRace()->id);
            if ($sql->execute()) {
                $racerInfo = $sql->fetch(PDO::FETCH_OBJ);
            }
            
            if (!$racerInfo) {
                $view = new DispatchView();
                $error = "Racer Number not found";
                $view->generateErrorView($error); 
            }
            
            $sql = $this->db->prepare("SELECT jobs.id, jobs.name, runs.race, runs.code, runs.job, runs.racer, runs.status, DATE_FORMAT(runs.pickTime, '%k:%i:%s') AS pickTime, DATE_FORMAT(runs.completeTime, '%k:%i:%s') AS completeTime, runs.determination, runs.payout, runsCheckpoints.id as runsCheckpointsId, DATE_FORMAT(runs.finalTime, '%k:%i:%s') AS finalTime, dropCheckpoints.name AS dropOff, pickCheckpoints.name AS pickUpCheckpoint, DATE_FORMAT(runsCheckpoints.timeDropped, '%k:%i:%s') AS dropOffTime, jobs.stops, DATE_FORMAT(ADDTIME(:start, jobs.timeDue), '%k:%i:%s') AS dueTime FROM runs 
INNER JOIN runsCheckpoints ON runs.id = runsCheckpoints.run 
INNER JOIN jobs ON runs.job = jobs.id 
INNER JOIN checkpoints AS pickCheckpoints ON pickCheckpoints.id = jobs.pickUpCheckpoint
INNER JOIN checkpoints AS dropCheckpoints ON dropCheckpoints.id = runsCheckpoints.checkpoint
WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racer) AND runs.race = :race ORDER BY runs.id");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':race', $currentRace->id);
            $sql->bindParam(':start', $currentRace->startTime);
            if ($sql->execute()) {
                $board = $sql->fetchAll(PDO::FETCH_OBJ);
            }
            
            $view = new DispatchView();
            $view->racerInfo = $racerInfo;
            $view->board = $board;
            $view->courseCount = $this->getRacerOnCourseCount();
            $view->generateBoardForWorkSim();
            
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRacerInfoAndCurrentBoardForWorkSim($racer)", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function getRacerOnCourseCount() {
        $sql = $this->db->prepare("SELECT count(id) AS theCount FROM racesEntry WHERE race = :race AND status = :racingStatus");
        $sql->bindParam(':race', $this->getCurrentRace()->id);
        $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
        if ($sql->execute()) {
            $count = $sql->fetch(PDO::FETCH_ASSOC);
            return $count['theCount'];
        }
    }
    
    //For Speed Race
    public function dispatchRunAndStartRacerClock() {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace()->id;
            //Update racer entry table, change racer status to racing and set the start time as now.
            $sql = $this->db->prepare("UPDATE racesEntry SET startTime = NOW(), status = :status WHERE racer = :racer AND  race = :race");
            $sql->bindValue(':status', RACE_RACING_STATUS);
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindParam(':race', $race);
            $sql->execute();
            
            //Log it
            $logDescription = "Marked racer as started race";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'dispatchRunAndStartRacerClock()', $logDescription);
            
            //Update the runs table, mark the picktime equal to picktime as start time, update status
            $sql = $this->db->prepare("UPDATE runs SET pickTime = NOW(), status = :status WHERE id = :run");
            //$sql->bindParam(':racer', $this->params['racer']);
            //$sql->bindParam(':race', $this->getCurrentRace()->id);
            $sql->bindValue(':status', RUN_PICKED);
            $sql->bindParam(':run', $this->requestParams['run']);
            $sql->execute();
            //Log it.
            $logDescription = "Marked package " . $this->requestParams['run'] . " as picked up.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'dispatchRunAndStartRacerClock()', $logDescription);
            $this->db->commit();
            
            
        }
        catch (PDOException $ex) {
            $this->db->rollback();
            $this->logIntoErrorTable("dispatchRunAndStartRacerClock()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function markJobAsDroppedAndMoveToNextManifest() {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace()->id;
            //Find the racers current open job.
            $sql = $this->db->prepare("SELECT id FROM runs WHERE racer = :racer AND status = :status AND race = :race");
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindValue(':status', RUN_PICKED);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $results = $sql->fetch(PDO::FETCH_ASSOC);
            $oldJob = $results['id'];

            //Mark that job as completed
            $sql = $this->db->prepare("UPDATE runs SET completeTime = NOW(), status = :status WHERE id = :oldJob");
            $sql->bindValue(':status', RUN_DROPPED);
            $sql->bindParam(':oldJob', $oldJob);
            $sql->execute();
            $logDescription = "Marked run #$oldJob as completed.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndMoveToNextManifest()', $logDescription);
            
            $sql = $this->db->prepare("UPDATE runsCheckpoints SET timeDropped = NOW() WHERE run = :oldJob");
            $sql->bindParam(':oldJob', $oldJob);
            $sql->execute();
            $logDescription = "Marked time dropped on #$oldJob checkpoint.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndMoveToNextManifest()', $logDescription);
            
            
            
            //Now update the run the racer is picking up as raced.
            $sql = $this->db->prepare("UPDATE runs SET pickTime = NOW(), status = :status WHERE id = :run");
            //$sql->bindParam(':racer', $this->params['racer']);
            //$sql->bindParam(':race', $this->getCurrentRace()->id);
            $sql->bindValue(':status', RUN_PICKED);
            $sql->bindParam(':run', $this->requestParams['run']);
            $sql->execute();
            $logDescription = "Marked package " . $this->requestParams['run'] . " as picked up.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndMoveToNextManifest()', $logDescription);
            $this->db->commit();
            
        }
        catch (PDOException $ex) {
            $this->db->rollback();
            $this->logIntoErrorTable("markJobAsDroppedAndMoveToNextManifest()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function markJobAsDroppedAndFinishRacer() {
        try {
            //Find the current run being worked
            $this->db->beginTransaction();
            $race = $this->getCurrentRace()->id;
            //Find the racers current open job.
            $sql = $this->db->prepare("SELECT id FROM runs WHERE racer = :racer AND status = :status AND race = :race");
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindValue(':status', RUN_PICKED);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $results = $sql->fetch(PDO::FETCH_ASSOC);
            $oldJob = $results['id'];

            //Mark that job as completed
            $sql = $this->db->prepare("UPDATE runs SET completeTime = NOW(), status = :status WHERE id = :oldJob");
            $sql->bindValue(':status', RUN_DROPPED);
            $sql->bindParam(':oldJob', $oldJob);
            $sql->execute();
            $logDescription = "Marked run #$oldJob as completed.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndFinishRacer()', $logDescription);
            
            //Now update the raceEntry, set the complete time to now, and update status
            
            $sql = $this->db->prepare("UPDATE racesEntry SET endTime = NOW(), status = :status WHERE racer = :racer AND race = :race");
            $sql->bindValue(':status', RACE_FINISHED_STATUS);
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindParam(':race', $race);
            $sql->execute();
            
            $logDescription = "Marked race #$race as completed.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndFinishRacer()', $logDescription);
            
            
            
            //Now update the time
            $sql = $this->db->prepare("UPDATE racesEntry SET finalTime = TIMEDIFF(endTime, startTime) WHERE racer = :racer AND race = :race");
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindParam(':race', $race);
            $sql->execute();
            
            $logDescription = "Updated Final Time";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'markJobAsDroppedAndFinishRacer()', $logDescription);
            
            $this->db->commit();
            
            
        }
        catch (PDOException $ex) {
            $this->db->rollback();
            $this->logIntoErrorTable("markJobAsDroppedAndFinishRacer()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    //Checks to see if race has started
    public function getRaceTime() {
        try {
            $sql = $this->db->prepare("SELECT TIMEDIFF(NOW(), (SELECT startTime FROM races WHERE id = :id)) AS raceTime, IF(TIMEDIFF(NOW(), (SELECT startTime FROM races WHERE id = :id)) > '00:00:00', 1, 0) AS raceStarted");
            $sql->bindParam(':id', $this->getCurrentRace()->id);
            $sql->execute();
            $time = $sql->fetch(PDO::FETCH_ASSOC);
            return $time;

        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRaceTime()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function displayRaceTime() {
        $raceTime = $this->getRaceTime();
        $view = new DispatchView();
        $view->raceTime = $raceTime['raceTime'];
        $view->raceStarted = $raceTime['raceStarted'];
        $view->generateRaceTime();
    }
    
    public function dispatchJobForWorkSim() {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace()->id;
            $racer = $this->requestParams['racer'];
            $job = $this->requestParams['job'];
            //Check to make sure racer has not picked up package
            $sql = $this->db->prepare("SELECT id FROM runs WHERE racer = :racer AND job = :job");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':job', $job);
            $sql->execute();
            $jobs = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($jobs) != 0) {
                $view = new DispatchView();
                $error = "Racer already has job assigned to them";
                $view->generateError($error); 
            }
            
            //Check to make sure the job number is valid
            $sql = $this->db->prepare("SELECT id FROM jobs WHERE id = :job");
            $sql->bindParam(':job', $job);
            $sql->execute();
            if (!$sql->fetch()) {
                $view = new DispatchView();
                $error = "No job with id #$job.";
                $view->generateError($error); 
            }
            
            //Now check to make sure they can pick up package
            $sql = $this->db->prepare("SELECT ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT readyTime FROM jobs WHERE id = :job)) AS jobReadyTime, IF(ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT readyTime FROM jobs WHERE id = :job)) < NOW(), 1, 0) AS ready");
            $sql->bindParam(':job', $job);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $ready = $sql->fetch(PDO::FETCH_ASSOC);
            if ($ready['ready'] == 0) {
                $view = new DispatchView();
                $error = "Job is not ready until " . $ready['jobReadyTime'];
                $view->generateError($error); 
            }
            
            //Check to make sure job is not dead
            $sql = $this->db->prepare("SELECT ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT timeDue FROM jobs WHERE id = :job)) AS jobDueTime, IF(ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT timeDue FROM jobs WHERE id = :job)) < NOW(), 1, 0) AS dead");
            $sql->bindParam(':job', $job);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $dead = $sql->fetch(PDO::FETCH_ASSOC);
            if ($dead['dead'] == 1) {
                $view = new DispatchView();
                $error = "Job has been dead since " . $dead['jobDueTime'];
                $view->generateError($error); 
            }

            //If they are good, let's make a job
            do {
                $sql = $this->db->prepare("INSERT INTO runs (race, code, job, racer, status, pickTime) VALUES (:race, :code, :job, :racer, :status, NOW())");
                $sql->bindParam(':race', $race);
                $sql->bindValue(':code', $this->generateCode());
                $sql->bindParam(':job', $job);
                $sql->bindParam(':racer', $racer);
                $sql->bindValue(':status', RUN_PICKED);
            } while(!$sql->execute());
            
            $runId = $this->db->lastInsertId();
            //Now create the run's checkpoints
            $sql = $this->db->prepare("SELECT * FROM jobsCheckpoints WHERE job = :job");
            $sql->bindParam(':job', $job);
            $sql->execute();
            while($checkpoint = $sql->fetch(PDO::FETCH_ASSOC)) {
                $innerSql = $this->db->prepare("INSERT INTO runsCheckpoints (run, checkpoint) VALUES (:run, :checkpoint)");
                $innerSql->bindParam(':run', $runId);
                $innerSql->bindParam(':checkpoint', $checkpoint['checkpoint']);
                $innerSql->execute();
            }
            
            $logDescription = "Dispatched $job to racer.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], 'dispatchJobForWorkSim()', $logDescription);
            
            $this->db->commit();            
            
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("dispatchJobForWorkSim()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function dropJobForWorksim() {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace();
            $racer = $this->requestParams['racer'];
            $code = $this->requestParams['code'];
            $checkpoint = $this->requestParams['checkpoint'];
            
            //First Check if racer has package with that code and racer is at right checkpoint
            $sql = $this->db->prepare("SELECT id FROM runs WHERE code = :code AND racer = :racer");
            $sql->bindParam(':code', $code);
            $sql->bindParam(':racer', $racer);
            $sql->execute();
            $runs = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($runs) == 0) {
                $view = new DispatchView();
                $error = "Racer does not have a package with code: $code";
                $view->generateError($error);
            }
            
            //Check the checkpoint is valid
            $sql = $this->db->prepare("SELECT id FROM runsCheckpoints WHERE run = :run AND checkpoint = :checkpoint");
            $sql->bindParam(':run', $runs[0]['id']);
            $sql->bindParam(':checkpoint', $checkpoint);
            $sql->execute();
            $checkpointTest = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$checkpointTest) {
                $view = new DispatchView();
                $error = "Job has no stop at this checkpoint";
                $view->generateError($error);
            }

            //Double check the package has not already been dropped off
            $sql = $this->db->prepare("SELECT timeDropped FROM runsCheckpoints WHERE run = :run AND checkpoint = :checkpoint");
            $sql->bindParam(':run', $runs[0]['id']);
            $sql->bindParam(':checkpoint', $checkpoint);
            $sql->execute();
            $dropOff = $sql->fetch(PDO::FETCH_ASSOC);
            if (!is_null($dropOff['timeDropped'])) {
                $view = new DispatchView();
                $error = "Racer already dropped off package at " . $dropOff['timeDropped'];
                $view->generateError($error);
            }

            //Mark the runsCheckpoint as dropped off
            $sql = $this->db->prepare("UPDATE runsCheckpoints SET timeDropped = NOW() WHERE run = :run AND checkpoint = :checkpoint");
            $sql->bindParam(':run', $runs[0]['id']);
            $sql->bindParam(':checkpoint', $checkpoint);
            $sql->execute();
            
            //Now determine if the racer has finished the job (all runsCheckpoints dropped)
            $sql = $this->db->prepare("SELECT id FROM runsCheckpoints WHERE run = :run AND timeDropped IS NULL");
            $sql->bindParam(':run', $runs[0]['id']);
            $sql->execute();
            $finish = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($finish) == 0) {
                $sql = $this->db->prepare("UPDATE runs SET completeTime = NOW(), status = :status WHERE code = :code");
                $sql->bindParam(':code', $code);
                $sql->bindValue(':status', RUN_DROPPED);
                $sql->execute();
                
                //Determine if the package as on time or late, and assign the appropiate payout
                $sql = $this->db->prepare("SELECT job, completeTime FROM runs WHERE code = :code");
                $sql->bindParam(':code', $code);
                $sql->execute();
                $jobId = $sql->fetch(PDO::FETCH_ASSOC);
                
                $sql = $this->db->prepare("UPDATE runs SET determination = IF(ADDTIME(:start, (SELECT jobs.timeDue FROM jobs WHERE id = :jobId)) >= :completeTime, :ontime, :late) WHERE code = :code");
                $sql->bindParam(':start', $race->startTime);
                $sql->bindParam(':code', $code);
                $sql->bindValue(':ontime', RUN_DETERMINATION_ON_TIME);
                $sql->bindValue(':late', RUN_DETERMINATION_LATE);
                $sql->bindParam(':jobId', $jobId['job']);
                $sql->bindParam(':completeTime', $jobId['completeTime']);
                $sql->execute();
                
                //Finally, update the payout
                $sql = $this->db->prepare("UPDATE runs SET payout = IF(determination = :ontime, (SELECT payout FROM jobs WHERE id = :job), (SELECT latePayout FROM jobs WHERE id = :job)) WHERE code = :code");
                $sql->bindValue(':ontime', RUN_DETERMINATION_ON_TIME);
                $sql->bindParam(':job', $jobId['job']);
                $sql->bindParam(':code', $code);
                $sql->execute();
                
                //And calculate earnings
                $sql = $this->db->prepare("UPDATE racesEntry SET points = (SELECT SUM(payout) FROM runs WHERE racer = :racer) WHERE racer = :racer AND race = :race");
                $sql->bindParam(':code', $code);
                $sql->bindParam(':racer', $racer);
                $sql->bindParam(':race', $race->id);
                $sql->execute();
                
            }
            $this->db->commit();
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("dropJobForWorksim()", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function getRunForEditing($code) {
        try {
            $currentRace = $this->getCurrentRace();
            $sql = $this->db->prepare("SELECT runs.id, jobs.name, runs.race, runs.code, runs.job, runs.racer, runs.status, DATE_FORMAT(runs.pickTime, '%k:%i:%s') AS pickTime, DATE_FORMAT(runs.completeTime, '%k:%i:%s') AS completeTime, runs.determination, runs.payout, DATE_FORMAT(runs.finalTime, '%k:%i:%s') AS finalTime, runsCheckpoints.id as runsCheckpointsId, dropCheckpoints.name AS dropOff, pickCheckpoints.name AS pickUpCheckpoint, DATE_FORMAT(runsCheckpoints.timeDropped, '%k:%i:%s') AS dropOffTime, jobs.stops, DATE_FORMAT(ADDTIME(:start, jobs.timeDue), '%k:%i:%s') AS dueTime FROM runs 
INNER JOIN runsCheckpoints ON runs.id = runsCheckpoints.run 
INNER JOIN jobs ON runs.job = jobs.id 
INNER JOIN checkpoints AS pickCheckpoints ON pickCheckpoints.id = jobs.pickUpCheckpoint
INNER JOIN checkpoints AS dropCheckpoints ON dropCheckpoints.id = runsCheckpoints.checkpoint
WHERE runs.code = :code AND runs.race = :race ORDER BY runs.id");

            $sql->bindParam(':race', $currentRace->id);
            $sql->bindParam(':start', $currentRace->startTime);
            $sql->bindParam(':code', $code);
            if ($sql->execute()) {
                $board = $sql->fetchAll(PDO::FETCH_OBJ);
                $view = new DispatchView();
                $view->board = $board;
                $view->generateRun();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRunForEditing", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function editRun($code) {
        try {
           $race = $this->getCurrentRace();
           $this->db->beginTransaction();

            $sql = $this->db->prepare("UPDATE runs SET status = :status, pickTime = CONCAT_WS(' ',DATE(pickTime), :pickTime), completeTime = CONCAT_WS(' ',DATE(completeTime), :completeTime), determination = :determination, payout = :payout WHERE code = :code");
            $sql->bindParam(':status', $this->requestParams['status']);
            $sql->bindParam(':pickTime', $this->requestParams['pickTime']);
            $sql->bindParam(':completeTime', $this->requestParams['completeTime']);
            $sql->bindParam(':determination', $this->requestParams['determination']);
            $sql->bindParam(':payout', $this->requestParams['payout']);
            $sql->bindParam(':code', $code);
            $sql->execute();
            
            foreach ($this->requestParams['runsCheckpoints'] as $rc) {
                $sql = $this->db->prepare("UPDATE runsCheckpoints SET timeDropped = CONCAT_WS(' ',DATE(timeDropped), :timeDropped) WHERE id = :id");
                $sql->bindParam(':timeDropped', $rc['timeDropped']);
                $sql->bindParam(':id', $rc['id']);
                $sql->execute();

            }
            
            //Now recalculate 
            $sql = $this->db->prepare("UPDATE racesEntry SET points = (SELECT SUM(payout) FROM runs WHERE racer = :racer AND race = :race) WHERE racer = :racer AND race = :race");
            $sql->bindParam(':code', $code);
            $sql->bindParam(':racer', $this->requestParams['racer']);
            $sql->bindParam(':race', $race->id);
            $sql->execute();
            
            $logDescription = "Racer had run with code " . $code . " edited.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], "editRun($code)", $logDescription);
            
            $this->db->commit();
        }
        catch (PDOException $ex) {
            $this->rollBack();
            $this->logIntoErrorTable("editRun", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function removeRun($code) {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace();
            $sql = $this->db->prepare("SELECT * FROM runs WHERE code = :code");
            $sql->bindParam(':code', $code);
            $sql->execute();
            $run = $sql->fetch(PDO::FETCH_ASSOC);
            
            $sql = $this->db->prepare("DELETE FROM runs WHERE code = :code");
            $sql->bindParam(':code', $code);
            $sql->execute();
            
            $logDescription = "Racer had run with code " . $code . " removed.";
            $this->logIntoRaceEventTable($race, $this->requestParams['racer'], "editRun($code)", $logDescription);
            
            //Now recalculate 
            $sql = $this->db->prepare("UPDATE racesEntry SET points = (SELECT SUM(payout) FROM runs WHERE racer = :racer) WHERE racer = :racer AND race = :race");
            $sql->bindParam(':code', $code);
            $sql->bindParam(':racer', $run['racer']);
            $sql->bindParam(':race', $race->id);
            $sql->execute();
            $this->db->commit();
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("removeRun", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
    public function getListOfRacersOnCourse() {
        try {
            $sql = $this->db->prepare("SELECT racers.racerNumber, racers.firstName, racers.nickName, racers.lastName, TIME(TIMEDIFF(NOW(), racesEntry.startTime)) AS elapsed FROM racers INNER JOIN racesEntry ON racers.id = racesEntry.racer WHERE race = :race AND racesEntry.status = :racingStatus ORDER BY elapsed DESC");
            $sql->bindParam(':race', $this->getCurrentRace()->id);
            $sql->bindValue(':racingStatus', RACE_RACING_STATUS);
            $sql->execute();
            $racers = $sql->fetchAll(PDO::FETCH_ASSOC);
            $view = new DispatchView();
            $view->racersOnCourse = $racers;
            $view->generateRacerOnCourseList();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getListOfRacersOnCourse", $ex);
            $view = new DispatchView();
            $view->badRequest();
        }
    }
    
}