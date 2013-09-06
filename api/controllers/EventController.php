<?php
/******************************************************************************
EventController.php
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

EventController
Event Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/Event.php');
require_once('../api/views/EventView.php');


class EventController extends BaseController {
    
    public function getAllEvents() {
        try {
            $sql = $this->db->prepare("SELECT * FROM events");
            if ($sql->execute()) {
                $events = $sql->fetchAll(PDO::FETCH_CLASS, "Event");
                $view = new EventView();
                $view->events = $events;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable('getAllEvents()', $ex);
            $view = new EventView();
            $view->badRequest();
        } 
    }
    
    public function getEventById($id) {
        try {
            $sql = $this->db->prepare("SELECT * FROM events WHERE id = :id");
            $sql->bindParam(':id', $id);
            if ($sql->execute()) {
                $events = $sql->fetchAll(PDO::FETCH_CLASS, "Event");
                $view = new EventView();
                $view->events = $events;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getEventById($id)", $ex);
            $view = new EventView();
            $view->badRequest();
        }
    }
    
    public function createNewEvent() {
        var_dump($this->requestParams);
        try {
            $sql = $this->db->prepare("INSERT INTO events (eventName, eventStartDate, eventEndDate, eventCity) VALUES (:eventName, :eventStartDate, :eventEndDate, :eventCity)");
            $sql->bindParam(':eventName', $this->requestParams['eventName']);
            $sql->bindParam(':eventStartDate', $this->requestParams['eventStartDate']);
            $sql->bindParam(':eventEndDate', $this->requestParams['eventEndDate']);
            $sql->bindParam(':eventCity', $this->requestParams['eventCity']);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("createNewEvent", $ex);
            $view = new EventView();
            $view->badRequest();
        }
    }
    
    public function editEvent($id) {
        try {
            $sql = $this->db->prepare("UPDATE events SET eventName = :eventName, eventStartDate = :eventStartDate, eventEndDate = :eventEndDate, eventCity = :eventCity WHERE id = :id");
            $sql->bindParam(':eventName', $requestParams['eventName']);
            $sql->bindParam(':eventStartDate', $requestParams['eventStartDate']);
            $sql->bindParam(':eventEndDate', $requestParams['eventEndDate']);
            $sql->bindParam(':eventCity', $requestParams['eventCity']);
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editEvent($id)", $ex);
            $view = new EventView();
            $view->badRequest();
        }
    }
    
    public function deleteEvent($id) {
        try {
            $sql = $this->db->prepare("DELETE FROM events WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteEvent($id)", $ex);
            $view = new EventView();
            $view->badRequest();
        }
    }
    
    public function displayCurrentEvent() {
        $event = $this->getCurrentEvent();
        $view = new EventView();
        $view->events = array($event);
        $view->generate();
    }
    
    public function userSetCurrentEvent($event) {
        $this->setCurrentEvent($event);
    }
}