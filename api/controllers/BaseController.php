<?php
/******************************************************************************
BaseController.php
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

BaseController
Base Controller class for Checkpoint API.
/******************************************************************************/

require_once('../api/util.php');
require_once('../api/models/Event.php');
require_once('../api/models/Race.php');

class BaseController {
    
    public $db; //PDO Database Connection Object
    public $currentUser;
    public $requestParams;
    
    function __construct($user) {
        $this->currentUser = $user;
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
        $sql->bindParam(':errorUser', $this->currentUser);
        $sql->bindParam(':errorMethod', $method);
        $sql->bindParam(':errorMessage', $error);
        $sql->execute();  
    }
    
    //Every single action should always be logged into the Race Event table.
    public function logIntoRaceEventTable($race, $racerId, $method, $description) {
        $event = $this->getCurrentEvent()->id;
        $race = $this->getCurrentRace()->id;
        //***
        $sql = $this->db->prepare("INSERT INTO eventLog (user, event, race, racerId, method, description) VALUES (:user, :event, :race, (SELECT racerNumber FROM racers WHERE id = :racerId), :method, :description)");
        $sql->bindParam(':user', $this->currentUser);
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
    
    public function setCurrentEvent($event) {
        //Update the DB
        try {
            $sql = $this->db->prepare("UPDATE params SET currentEvent = :eventId");
            $sql->bindParam(':eventId', $event);
            $sql->execute();
            $sql = $this->db->prepare("SELECT * FROM events WHERE id = (SELECT currentEvent FROM params LIMIT 1)");
            if ($sql->execute()) {
                $event = $sql->fetchAll(PDO::FETCH_CLASS, "Event");
                $memcache = new Memcached();
                $memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
                $memcache->set('currentEvent', $event[0], 0);
            }
            
        }
        catch (Exception $ex) {
            $this->logIntoErrorTable('setCurrentEvent', $ex);
        }
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
    
    public function setCurrentRace($race) {
        //Update the DB
        try {
            $sql = $this->db->prepare("UPDATE params SET currentRace = :raceId");
            $sql->bindParam(':raceId', $race);
            $sql->execute();
            $sql = $this->db->prepare("SELECT * FROM races WHERE id = (SELECT currentRace FROM params LIMIT 1)");
            if ($sql->execute()) {
                $race = $sql->fetchAll(PDO::FETCH_CLASS, "Race");
                $memcache = new Memcached();
                $memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
                $memcache->set('currentRace', $race[0], 0);
            }
            
        }
        catch (Exception $ex) {
            $this->logIntoErrorTable('setCurrentRace', $ex);
        }
    }
    
    public function generateCode() {
        $charset = "ABCDEFGHIJKLMNOPQRSTVXYZ";
        $retval = "";
        for ($i = 0; $i < RUN_CODE_LENGTH; $i++) {
            $retval .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
        }
        return $retval;
    }
}
?>