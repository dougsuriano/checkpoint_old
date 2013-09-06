<?php
/******************************************************************************
CheckpointWorker.php
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

CheckpointWorker
Stripped down API for checkpoint workers.
/******************************************************************************/


require_once("../api/views/CheckpointView.php");
require_once("../api/models/Checkpoint.php");
require_once("../api/models/User.php");
require_once("../api/views/UserView.php");
require_once("../api/models/Racer.php");
require_once("../api/views/RacerView.php");
require_once("../api/util.php");
require_once("../api/views/RunView.php");
require_once("../api/models/Run.php");
require_once("../api/models/Race.php");
require_once('../api/models/Event.php');
class CheckpointWorker {
    
    public $SUPER_CODE_IN_USE = false;
    
    public $db; //PDO Database Connection Object
    public $currentUser;
    public $requestParams;
    
    function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
            $this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    }
    
    public function setRequestParams($incommingJson) {
        $this->requestParams = json_decode($incommingJson, TRUE);
    }
    
    //All errors should be logged into error table. 
    public function logIntoErrorTable($method, $error) {
        $sql = $this->db->prepare("INSERT INTO errors (errorUser, errorMethod, errorMessage) VALUES (:errorUser, :errorMethod, :errorMessage)");
        $sql->bindParam(':errorUser', $this->currentUser->id);
        $sql->bindParam(':errorMethod', $method);
        $sql->bindParam(':errorMessage', $error);
        $sql->execute();  
    }
    
    //Every single action should always be logged into the Race Event table.
    public function logIntoRaceEventTable($race, $racerId, $method, $description) {
        $event = $this->getCurrentEvent()->id;
        //***
        $sql = $this->db->prepare("INSERT INTO eventLog (user, event, race, racerId, method, description) VALUES (:user, :event, :race, :racerId, :method, :description)");
        $sql->bindParam(':user', $this->currentUser->username);
        $sql->bindParam(':event', $event);
        $sql->bindParam(':race', $race);
        $sql->bindParam(':racerId', $racerId);
        $sql->bindParam(':method', $method);
        $sql->bindParam(':description', $description);
        $sql->execute();
    }
    
    public function getCurrentEvent() {
        //Check the memcache
        $memcache = new Memcached();
        $memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
        if (!($retval = $memcache->get('currentEvent'))) {
            $sql = $this->db->prepare("SELECT * FROM events WHERE id = (SELECT currentEvent FROM params LIMIT 1)");
            if ($sql->execute()) {
                $event = $sql->fetchAll(PDO::FETCH_CLASS, "Event");
                //Now store the SQL event in the memcache
                $memcache->set('currentEvent', $event[0], 0);
                $retval = $event[0];
            }   
                
        }
        return $retval;
    }
    
    public function getCurrentRace() {
        //Check the memcache
        $memcache = new Memcached();
        $memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
        if (!($retval = $memcache->get('currentRace'))) {
            $sql = $this->db->prepare("SELECT * FROM races WHERE id = (SELECT currentRace FROM params LIMIT 1)");
            if ($sql->execute()) {
                $race = $sql->fetchAll(PDO::FETCH_CLASS, "Race");
                //Now store the SQL event in the memcache
                $memcache->set('currentRace', $race[0], 0);
                $retval = $race[0];
            }   
                
        }
        return $retval;
    }
    
    
    public function getUser($username) {
        try {
           $sql = $this->db->prepare("SELECT * FROM users WHERE username = :username"); 
           $sql->bindParam(':username', $username);
           if ($sql->execute()) {
               $users = $sql->fetchAll(PDO::FETCH_CLASS, 'User');
               return $users[0];
           }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getUser($username)", $ex);
        }
    }
    
    public function getUserInfo() {
        try {
           $sql = $this->db->prepare("SELECT * FROM users WHERE username = :username"); 
           $sql->bindParam(':username', $this->currentUser->username);
           if ($sql->execute()) {
               $users = $sql->fetchAll(PDO::FETCH_CLASS, 'User');
               $view = new UserView();
               $view->users = $users;
               $view->generate();
               
           }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getUserInfo($username)", $ex);
        }
    }
    
    public function getListOfAuthorizedCheckpoints() {
        try {
            $sql = $this->db->prepare("SELECT checkpoints.* FROM checkpoints INNER JOIN authorizedCheckpoints ON checkpoints.id = authorizedCheckpoints.checkpoint WHERE authorizedCheckpoints.userId = :user");
            $sql->bindParam(':user', $this->currentUser->id);
            if ($sql->execute()) {
                $checkpoints = $sql->fetchAll(PDO::FETCH_CLASS, 'Checkpoint');
                $view = new CheckpointView();
                $view->checkpoints = $checkpoints;
                $view->generate();
                
            }
            
        }
        catch (Exception $ex) {
            $this->logIntoErrorTable('setCurrentEvent', $ex);
        }
        
    }
    
    public function getRacer($racerNumber) {
        try {
            $currentRace = $this->getCurrentRace();
            //Check to make sure checkpoint is active
            if ($this->checkIfCheckpointActive() == false) {
                $view = new RacerView();
                $view->generateError("Checkpoint data entry has been disabled by HQ.");
            }
            $sql = $this->db->prepare("SELECT racers.*, racesEntry.status FROM racers INNER JOIN racesEntry ON racers.id = racesEntry.racer WHERE racers.racerNumber = :racerNumber AND race = :currentRace");
            $sql->bindParam(':racerNumber', $racerNumber);
            $sql->bindParam(':currentRace', $currentRace->id);
            if ($sql->execute()) {
                $racers = $sql->fetchAll(PDO::FETCH_CLASS, 'Racer');
                //Check to see if racer is entered in current race and status is racing
                $view = new RacerView();
                if (sizeof($racers) < 1) {
                    $view->generateError("Racer #$racerNumber was not found entered in current event. Please try again.");
                }
                if ($racers[0]->status == RACE_ENTRY_DQ_STATUS) {
                    $view->generateError("Racer #$racerNumber has been disquallified. Have rider go to dispatch for reason.");
                }
                $view = new RacerView();
                $view->racers = $racers;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneRacer($racerNumber)", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
    }
    
    public function pickUp() {
        //header('Content-Type: application/json');
        
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace()->id;
            $racer = $this->requestParams['racerNumber'];
            $job = $this->requestParams['job'];
            $checkpoint = $this->requestParams['checkpoint'];

            //Check to make sure racer hasn't been dq'd
            $sql = $this->db->prepare("SELECT status FROM racesEntry WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racerNumber)");
            $sql->bindParam(':racerNumber', $racer);
            $sql->execute();
            $racerStatus = $sql->fetch(PDO::FETCH_ASSOC);
            if ($racerStatus['status'] == "2") {
                $view = new RunView();
                $error = "Racer #$racer has been disquallified. Have rider go to dispatch for reason.";
                $view->generateError($error); 
            }
            
            //Check to make sure the job number is valid
            $sql = $this->db->prepare("SELECT id FROM jobs WHERE id = :job");
            $sql->bindParam(':job', $job);
            $sql->execute();
            if (!$sql->fetch()) {
                $view = new RunView();
                $error = "No job with id #$job.";
                $view->generateError($error); 
            }
            
            //Check to make sure the racer is at right checkpoint
            $sql = $this->db->prepare("SELECT id FROM jobs WHERE pickUpCheckpoint = :checkpoint AND id = :job");
            $sql->bindParam(':checkpoint', $checkpoint);
            $sql->bindParam(':job', $job);
            $sql->execute();
            if (!$sql->fetch()) {
                $view = new RunView();
                $error = "Wrong Checkpoint";
                $view->generateError($error); 
            }
            
            
            //Check to make sure racer has not picked up package
            $sql = $this->db->prepare("SELECT code, status, TIME(pickTime) AS pickTime, TIME(completeTime) AS completeTime FROM runs WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racer) AND job = :job");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':job', $job);
            $sql->execute();
            $jobs = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($jobs) != 0) {
                $code = $jobs[0]['code'];
                if ($jobs[0]['status'] == '3') {
                    $time = $jobs[0]['completeTime'];
                    $view = new RunView();
                    $error = "Racer already has already completed the job at time $time with code $code.";
                    $view->generateError($error);
                }
                $view = new RunView();
                $time = $jobs[0]['pickTime'];
                $error = "Racer already has run assigned at time $time with code $code.";
                $view->generateError($error);
            }
            
            
            
            
            
            //Now check to make sure they can pick up package
            $sql = $this->db->prepare("SELECT ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT readyTime FROM jobs WHERE id = :job)) AS jobReadyTime, IF(ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT readyTime FROM jobs WHERE id = :job)) < NOW(), 1, 0) AS ready");
            $sql->bindParam(':job', $job);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $ready = $sql->fetch(PDO::FETCH_ASSOC);
            if ($ready['ready'] == 0) {
                $view = new RunView();
                $error = "Job is not ready yet.";
                $view->generateError($error); 
            }
            
            //Check to make sure job is not dead
            $sql = $this->db->prepare("SELECT ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT timeDue FROM jobs WHERE id = :job)) AS jobDueTime, IF(ADDTIME((SELECT startTime FROM races WHERE id = :race), (SELECT timeDue FROM jobs WHERE id = :job)) < NOW(), 1, 0) AS dead");
            $sql->bindParam(':job', $job);
            $sql->bindParam(':race', $race);
            $sql->execute();
            $dead = $sql->fetch(PDO::FETCH_ASSOC);
            if ($dead['dead'] == 1) {
                $view = new RunView();
                $error = "Job has been dead since " . $dead['jobDueTime'];
                $view->generateError($error); 
            }

            //If they are good, let's make a job
            do {
                $sql = $this->db->prepare("INSERT INTO runs (race, code, job, racer, status, pickTime) VALUES (:race, :code, :job, (SELECT id FROM racers WHERE racerNumber = :racer), :status, NOW())");
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
            $this->logIntoRaceEventTable($race, $racer, 'pickUp', $logDescription);
            
            $this->db->commit();
            $sql = $this->db->prepare("SELECT * FROM runs WHERE id = $runId");
            $sql->execute();
            $theRuns = $sql->fetchAll(PDO::FETCH_CLASS, 'Run');
            $view = new RunView();
            $view->runs = $theRuns;
            $view->generate();
                        
            
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("pickUp()", $ex);
            $view = new RunView();
            $view->badRequest();
        }
        
    }
    
    public function dropOff() {
        try {
            $this->db->beginTransaction();
            $race = $this->getCurrentRace();
            $racer = $this->requestParams['racerNumber'];
            $code = $this->requestParams['code'];
            $checkpoint = $this->requestParams['checkpoint'];
            
            if ($this->SUPER_CODE_IN_USE == true) {
                $this->checkForSuperCode($code, $checkpoint, $racer);
            }
            
            //Check to make sure racer hasn't been dq'd
            $sql = $this->db->prepare("SELECT status FROM racesEntry WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racerNumber)");
            $sql->bindParam(':racerNumber', $racer);
            $sql->execute();
            $racerStatus = $sql->fetch(PDO::FETCH_ASSOC);
            if ($racerStatus['status'] == "2") {
                $view = new RunView();
               $error = "Racer #$racer has been disquallified. Have rider go to dispatch for reason.";
                $view->generateError($error); 
            }
            
            //First Check if racer has package with that code and racer is at right checkpoint
            $sql = $this->db->prepare("SELECT id FROM runs WHERE code = :code AND racer = (SELECT id FROM racers WHERE racerNumber = :racer)");
            $sql->bindParam(':code', $code);
            $sql->bindParam(':racer', $racer);
            $sql->execute();
            $runs = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($runs) == 0) {
                $view = new RunView();
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
                $view = new RunView();
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
                $view = new RunView();
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
                $sql = $this->db->prepare("UPDATE racesEntry SET points = (SELECT SUM(payout) FROM runs WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racer) AND race = :race) WHERE racer = (SELECT id FROM racers WHERE racerNumber = :racer) AND race = :race");
                $sql->bindParam(':code', $code);
                $sql->bindParam(':racer', $racer);
                $sql->bindParam(':race', $race->id);
                $sql->execute();
                
            }
            $logDescription = "Racer dropped off package with code $code to checkpoint $checkpoint.";
            $this->logIntoRaceEventTable($race->id, $racer, "dropOff", $logDescription);
            
            $this->db->commit();
            $sql = $this->db->prepare("SELECT * FROM runs WHERE code = :code");
            $sql->bindParam(':code', $code);
            $sql->execute();
            $theRuns = $sql->fetchAll(PDO::FETCH_CLASS, 'Run');
            $view = new RunView();
            $view->runs = $theRuns;
            $view->generate();
            
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("dropJobFor()", $ex);
            $view = new RunView();
            $view->badRequest();
        }        
    }
    
    public function generateCode() {
        $charset = "ABCDEFGHIJKLMNOPQRSTVWXYZ";
        $retval = "";
        for ($i = 0; $i < RUN_CODE_LENGTH; $i++) {
            $retval .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
        }
        return $retval;
    }
    
    public function checkIfCheckpointActive() {
        try {
            $memcache = new Memcached();
            $memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
            if (!($active = $memcache->get('checkpointOn'))) {
                $sql = $this->db->prepare("SELECT checkpointsOn FROM params LIMIT 1");
                $sql->execute();
                $active = $sql->fetch(PDO::FETCH_ASSOC);
                $active = $active['checkpointsOn'];
            }

            if ($active == '1') {
                return true;
            }
            return false;
        }
        catch (PDOException $ex) {
            
        }
    }
    
    public function checkForSuperCode($code, $checkpoint, $racer) {
        try {
            //First check if code is supercode
            if (strlen($code) != 3) {
                return;
            }

            
            if (substr($code, 0, 1) != 'U') {
                if (substr($code, 1, 1) != 'U') {
                    if (substr($code, 2, 1) != 'U') {
                        return false;
                    }
                }
            }
            
            //Get the info on the code
            $sql = $this->db->prepare("SELECT * FROM supercodes WHERE code = :code");
            $sql->bindParam(':code', $code);
            $sql->execute();
            if (!$codeResults = $sql->fetch(PDO::FETCH_ASSOC)) {
                return;
            }
            
            //Check to make sure supercode is the right supercode for the checkpoint
            $sql = $this->db->prepare("SELECT id FROM jobsCheckpoints WHERE job = :job AND checkpoint = :checkpoint");
            $sql->bindParam(':job', $codeResults['job']);
            $sql->bindParam(':checkpoint', $checkpoint);
            $sql->execute();
            $supercodes = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($supercodes) == 0) {
                return;
            }
            
            //Log the super code entry and return an Job
            $sql = $this->db->prepare("INSERT INTO supercodeUses (racer, supercode) VALUES (:racer, :supercode)");
            $sql->bindParam(':racer', $racer);
            $sql->bindParam(':supercode', $codeResults['id']);
            $sql->execute();
            
            $retval = new Run();
            $retval->id = '666';
            $retval->race = "1";
            $retval->code = $code;
            $retval->job = $codeResults['job'];
            $retval->racer = $racer;
            $retval->status = "1";
            $retval->pickTime = "1";
            $retval->dropTimes = "1";
            $retval->determination = "1";
            $retval->payout = "1";
            $retval->finalTime = "1";
            
            $logDescription = "Supercode $code was used to drop off at checkpoint $checkpoint by racer $racer.";
            $this->logIntoRaceEventTable($this->getCurrentRace()->id, $racer, "checkForSuperCode()", $logDescription);
            
            $view = new RunView();
            $view->runs = array($retval);
            $view->generate();
            $this->db->commit();
            exit;
            
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("checkForSuperCode", $ex);
            $view = new RunView();
            $view->badRequest();
        }
        
    }
    

}


?>