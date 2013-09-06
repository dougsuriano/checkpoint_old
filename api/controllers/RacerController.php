<?php
/******************************************************************************
RacerController.php
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

RacerController
Racer Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/Racer.php');
require_once('../api/views/RacerView.php');

class RacerController extends BaseController {
    
    public function getAllRacers() {
        try {
            $sql = $this->db->prepare("SELECT * FROM racers ORDER BY racerNumber");
            if ($sql->execute()) {
                $racers = $sql->fetchAll(PDO::FETCH_CLASS, 'Racer');
                $view = new RacerView();
                $view->racers = $racers;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllRacers()", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
    }
    
    public function getOneRacer($racerNumber) {
        try {
            $sql = $this->db->prepare("SELECT * FROM racers WHERE racerNumber = :racerNumber");
            $sql->bindParam(':racerNumber', $racerNumber);
            if ($sql->execute()) {
                $racers = $sql->fetchAll(PDO::FETCH_CLASS, 'Racer');
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
    
    public function createRacer() {
        try {
            $sql = $this->db->prepare("INSERT INTO racers (racerNumber, firstName, lastName, nickName, city, country, category, sex, bikeType, paid) VALUES (:racerNumber, :firstName, :lastName, :nickName, :city, :country, :category, :sex, :bikeType, :paid)");
            $sql->bindParam(':racerNumber', $this->requestParams['racerNumber']);
            $sql->bindParam(':firstName', $this->requestParams['firstName']);
            $sql->bindParam(':lastName', $this->requestParams['lastName']);
            $sql->bindParam(':nickName', $this->requestParams['nickName']);
            $sql->bindParam(':city', $this->requestParams['city']);
            $sql->bindParam(':country', $this->requestParams['country']);
            $sql->bindParam(':category', $this->requestParams['category']);
            $sql->bindParam(':sex', $this->requestParams['sex']);
            $sql->bindParam(':bikeType', $this->requestParams['bikeType']);
            $sql->bindParam(':paid', $this->requestParams['paid']);
            $sql->execute();
            $this->getOneRacer($this->db->lastInsertId());
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("createRacer", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
    }
    
    public function editRacer($racerNumber) {
        try {
            $sql = $this->db->prepare("UPDATE racers SET racerNumber = :racerNumber, firstName = :firstName, lastName = :lastName, nickName = :nickName, city = :city, country = :country, category = :category, sex = :sex, bikeType = :bikeType, paid = :paid WHERE racerNumber = :old_racerNumber");
            $sql->bindParam(':racerNumber', $this->requestParams['racerNumber']);
            $sql->bindParam(':firstName', $this->requestParams['firstName']);
            $sql->bindParam(':lastName', $this->requestParams['lastName']);
            $sql->bindParam(':nickName', $this->requestParams['nickName']);
            $sql->bindParam(':city', $this->requestParams['city']);
            $sql->bindParam(':country', $this->requestParams['country']);
            $sql->bindParam(':category', $this->requestParams['category']);
            $sql->bindParam(':old_racerNumber', $racerNumber);
            $sql->bindParam(':sex', $this->requestParams['sex']);
            $sql->bindParam(':bikeType', $this->requestParams['bikeType']);
            $sql->bindParam(':paid', $this->requestParams['paid']);
            $sql->execute();
            $this->getOneRacer($this->requestParams['racerNumber']);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editRacer($racerNumber)", $ex);
            $view = new RacerView();
            $view->badRequest();
        } 
    }
    
    public function deleteRacer($id) {
        echo "HERE";
        try {
            $sql = $this->db->prepare("DELETE FROM racers WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
        } 
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteRacer($id)", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
    }
    
    public function getRacersInRace($race) {
        try {
            $sql = $this->db->prepare("SELECT racers.* FROM racers INNER JOIN racesEntry ON racers.id = racesEntry.racer WHERE racesEntry.race = :race ORDER BY racers.racerNumber");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $racers = $sql->fetchAll(PDO::FETCH_CLASS, 'Racer');
                $view = new RacerView();
                $view->racers = $racers;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRacersinRace($race)", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
        
    }
    
    public function getRacersNotInRace($race) {
        try {
            $sql = $this->db->prepare("SELECT * FROM racers WHERE id NOT IN (SELECT racer FROM racesEntry WHERE race = :race) ORDER BY racers.racerNumber");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $racers = $sql->fetchAll(PDO::FETCH_CLASS, 'Racer');
                $view = new RacerView();
                $view->racers = $racers;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getRacersinRace($race)", $ex);
            $view = new RacerView();
            $view->badRequest();
        }
    }
}

?>