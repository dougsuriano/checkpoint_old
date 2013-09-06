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
require_once('../api/models/Job.php');
require_once('../api/views/JobView.php');

class JobController extends BaseController { 
    
    public function getAllJobsInRace($race) {
        try {
            $sql = $this->db->prepare("SELECT * FROM jobs WHERE race = :race");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $jobs = $sql->fetchAll(PDO::FETCH_CLASS, 'Job');
                $view = new JobView();
                $view->jobs = $jobs;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllJobs", $ex);
            $view = new JobView();
            $view->badRequest();
        }
    }
    
    public function getOneJob($id) {
        try {
            $sql = $this->db->prepare("SELECT * FROM jobs WHERE id = :id");
            $sql->bindParam(':id', $id);
            if ($sql->execute()) {
                $jobs = $sql->fetchAll(PDO::FETCH_CLASS, 'Job');
                $view = new JobView();
                $view->jobs = $jobs;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneJob($id)", $ex);
            $view = new JobView();
            $view->badRequest();
        }
    }
    
    public function createJob() {
        try {
            $this->db->beginTransaction();
            $sql = $this->db->prepare("INSERT INTO jobs (name, race, readyTime, timeDue, jobType, payout, latePayout, noDropPayout, quantity, pickUpCheckpoint, stops) VALUES (:name, :race, :readyTime, :timeDue, :jobType, :payout, :latePayout, :noDropPayout, :quantity, :pickUpCheckpoint, :stops)");
            $sql->bindParam(':name', $this->requestParams['name']);
            $sql->bindParam(':race', $this->requestParams['race']);
            $sql->bindParam(':readyTime', $this->requestParams['readyTime']);
            $sql->bindParam(':timeDue', $this->requestParams['timeDue']);
            $sql->bindParam(':jobType', $this->requestParmas['jobType']);
            $sql->bindParam(':payout', $this->requestParams['payout']);
            $sql->bindParam(':latePayout', $this->requestParams['latePayout']);
            $sql->bindParam(':noDropPayout', $this->requestParams['noDropPayout']);
            $sql->bindParam(':quantity', $this->requestParams['quantity']);
            $sql->bindParam(':pickUpCheckpoint', $this->requestParams['pickUpCheckpoint']);
            $sql->bindValue(':stops', sizeof($this->requestParams['dropOffCheckpoints']));
            $sql->execute();
            $jobId = $this->db->lastInsertId();
            foreach ($this->requestParams['dropOffCheckpoints'] as $checkpoint) {
                $sql = $this->db->prepare("INSERT INTO jobsCheckpoints (job, checkpoint) VALUES (:job, :checkpoint)");
                $sql->bindParam(':job', $jobId);
                $sql->bindParam(':checkpoint', $checkpoint['checkpoint']);
                $sql->execute();
            }
            $this->db->commit();
        }
        catch (PODException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("createJob()", $ex);
            $view = new JobView();
            $view->badRequest();
            
        }
    }
    
    public function editJob($id) {
        try {
            $sql = $this->db->prepare("UPDATE jobs SET name = :name, race = :race, readyTime = :readyTime, timeDue = :timeDue, jobType = :jobType, payout = :payout, latePayout = :latePayout, noDropPayout = :noDropPayout, quantity = :quantity, pickUpCheckpoint = :pickUpCheckpoint WHERE id = :id");
            $sql->bindParam(':name', $requestParams['name']);
            $sql->bindParam(':race', $requestParams['race']);
            $sql->bindParam(':readyTime', $requestParams['readyTime']);
            $sql->bindParam(':timeDue', $requestParams['timeDue']);
            $sql->bindParam(':jobType', $requestParmas['jobType']);
            $sql->bindParam(':payout', $requestParams['payout']);
            $sql->bindParam(':latePayout', $requestParams['latePayout']);
            $sql->bindParam(':noDropPayout', $requestParams['noDropPayout']);
            $sql->bindParam(':quantity', $requestParams['quantity']);
            $sql->bindParam(':pickUpCheckpoint', $requestParams['pickUpCheckpoints']);
            $sql->bindParam(':id', $id);
            $sql->execute();
        } 
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editJob($id)", $ex);
            $view = new JobView();
            $view->badRequest();
        }
    }
    
    public function deleteJob($id) {
        try {
            $sql = $this->db->prepare("DELETE FROM jobs WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteJob($id)", $ex);
            $view = new JobView();
            $view->badRequest();
        }
    }
    
    public function setJobCheckpoints($job) {
        try {
            $this->db->beginTransaction();
            //Delete the exisiting checkpoints
            $sql = $this->db->prepare("DELETE FROM jobsCheckpoints WHERE job = :job");
            $sql->bindParam(':job', $job);
            $sql->execute();
            //Insert the new/editied checkpoints
            foreach($this->requestParams as $stop) {
                $sql = $this->db->prepare("INSERT INTO jobsCheckpoints (job, checkpoint, order) VALUES (:job, :checkpoint, :order)");
                $sql->bindParam(':job', $job);
                $sql->bindParam(':checkpoint', $stop['checkpoint']);
                $sql->bindParam(':order', $stop['order']);
                $sql->execute();
                $this->db->commit();
            }
        }
        catch (PDOException $ex) {
            $this->db->rollBack();
            $this->logIntoErrorTable("setJobCheckpoints($id)", $ex);
            $view = new JobView();
            $view->badRequest();
        }
    }
    
}

?>