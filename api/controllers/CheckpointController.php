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

CheckpointController
Checkpoint Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/Checkpoint.php');
require_once('../api/views/CheckpointView.php');

class CheckpointController extends BaseController {
    
    public function getAllCheckpoints() {
        try {
            $sql = $this->db->prepare("SELECT * FROM checkpoints");
            if ($sql->execute()) {
                $checkpoints = $sql->fetchAll(PDO::FETCH_CLASS, 'Checkpoint');
                $view = new CheckpointView();
                $view->checkpoints = $checkpoints;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllCheckpoints", $ex);
            $view = new CheckpointView();
            $view->badRequest();
        }
    }
    
    public function getOneCheckpoint($id) {
        try {
            $sql = $this->db->prepare("SELECT * FROM checkpoints WHERE id = :id");
            $sql->bindParam(':id', $id);
            if ($sql->execute()) {
                $checkpoints = $sql->fetchAll(PDO::FETCH_CLASS, 'Checkpoint');
                $view = new CheckpointView();
                $view->checkpoints = $checkpoints;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneCheckpoint($id)", $ex);
            $view = new CheckpointView();
            $view->badRequest();
        }
    }
    
    public function createCheckpoint() {
        try {
            $sql = $this->db->prepare("INSERT INTO checkpoints (name, address) VALUES (:name, :address)");
            $sql->bindParam(':name', $this->requestParams['name']);
            $sql->bindParam(':address', $this->requestParams['address']);
            $sql->execute();
            $this->getOneCheckpoint($this->db->lastInsertId());
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("createCheckpoint()", $ex);
            $view = new CheckpointView();
            $view->badRequest();
        }
    }
    
    public function editCheckpoint($id) {
        try {
            $sql = $this->db->prepare("UPDATE checkpoints SET name = :name, address = :address WHERE id = :id");
            $sql->bindParam(':name', $this->requestParams['name']);
            $sql->bindParam(':address', $this->requestParams['address']);
            $sql->bindParam(':id', $id);
            $sql->execute();
            $this->getOneCheckpoint($id);
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editCheckpoint($id)", $ex);
            $view = new CheckpointView();
            $view->badRequest();
        }
    }
    
    public function deleteCheckpoint($id) {
        try {
            $sql = $this->db->prepare("DELETE FROM checkpoints WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteCheckpoint($id)", $ex);
            $view = new CheckpointView();
            $view->badRequest();
        }
    }
}