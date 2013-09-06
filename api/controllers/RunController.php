<?php
/******************************************************************************
RunController.php
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

RunController
Run Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/Run.php');
require_once('../api/views/RunView.php');
require_once('RunsCheckpoints.php');

class RunController extends BaseController {
    
    public class getAllRuns($race) {
        try {
            $sql = $this->db->prepare("SELECT * FROM runs WHERE race = :race");
            $sql->bindParam(':race', $race);
            if ($sql->execute()) {
                $runs = $sql->fetchAll(PDO::FETCH_CLASS, 'Run');
                $view = new RunView();
                $view->runs = $runs;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllRuns", $ex);
            $view = new RunView();
            $view->badRequest();
        }
    }
    
    public function getOneRun($run) {
        try {
            $sql = $this->db->prepare("SELECT * FROM runs WHERE run = :run");
            $sql->bindParam(':run', $run);
            if ($sql->execute()) {
                $runs = $sql->fetchAll(PDO::FETCH_CLASS, 'Run');
                $sql = $this->db->prepare("SELECT * FROM runsCheckpoints WHERE run = :run");
                $sql->bindParam(':run', $run);
                if ($sql->execute()) {
                    $runsCheckpoints = $sql->fetchAll(PDO::FETCH_CLASS, 'RunsCheckpoints');
                    $runs[0]->dropTimes = $runsCheckpoints;
                    $view = new RunView();
                    $view->runs = $runs;
                    $view->generate();
                }
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneRun($code)", $ex);
            $view = new RunView();
            $view->badRequest();
        }
    }
    
    public function getOneRunByCode($code) {
        try {
            $sql = $this->db->prepare("SELECT * FROM runs WHERE code = :code");
            $sql->bindParam(':code', $code);
            if ($sql->execute()) {
                $runs = $sql->fetchAll(PDO::FETCH_CLASS, 'Run');
                $sql = $this->db->prepare("SELECT * FROM runsCheckpoints WHERE run = (SELECT id FROM runs WHERE code = :code)");
                $sql->bindParam(':code', $code);
                if ($sql->execute()) {
                    $runsCheckpoints = $sql->fetchAll(PDO::FETCH_CLASS, 'RunsCheckpoints');
                    $runs[0]->dropTimes = $runsCheckpoints;
                    $view = new RunView();
                    $view->runs = $runs;
                    $view->generate();
                }
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneRunByCode($code)", $ex);
            $view = new RunView();
            $view->badRequest();
        }
    }
    
    public function editRun($run) {
        try {
            
        }
    }
    
    public function deleteRun($run) {
        try {
            $sql = $this->db->prepare("DELETE FROM runs WHERE id = :run");
            $sql->bindParam(':run', $run);
            $sql->execute();
        }
        catch (PDOExcpetion $ex) {
            $this->logIntoErrorTable("deleteRun($run)", $ex);
            $view = new RunView();
            $view->badRequest();
        }
    }
    
}

?>