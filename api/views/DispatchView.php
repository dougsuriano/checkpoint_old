<?php
/******************************************************************************
DispatchView.php
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

DispatchView
Dispatch View
/******************************************************************************/

require_once('BaseView.php');
class DispatchView extends BaseView {
    
    public $racerInfo;
    public $board;
    public $courseCount;
    public $raceTime;
    public $raceStarted;
    public $racersOnCourses;
    
    public function generateBoardForIndRace() {
        parent::generate();
        $output = array('racer' => $this->serializeRacerInfoForSpeed($this->racerInfo), 'board' => $this->serializeBoard($this->board), 'count' => $this->courseCount);
        echo json_encode($output); 
    }
    
    public function generateBoardForWorkSim() {
        parent::generate();
        $output = array('racer' => $this->serializeRacerInfo($this->racerInfo), 'board' => $this->serializeBoardForWorksim($this->board), 'count' => $this->courseCount);
        echo json_encode($output); 
    }
    
    public function generateRun() {
        parent::generate();
        $output = array('board' => $this->serializeBoardForWorksim($this->board));
        echo json_encode($output);
    }
    
    public function generateErrorView($error) {
        parent::generate();
        $output = array('error' => $error);
        echo json_encode($output);
        exit;
    }
    
    public function generateRaceTime() {
        parent::generate();
        $output = array('raceTime' => $this->raceTime, 'raceStarted' => $this->raceStarted);
        echo json_encode($output);
    }
    
    public function serializeRacerInfo($object) {
        $retval = array(
            'id'                => $object->id,
            'racerNumber'       => $object->racerNumber,
            'firstName'         => $object->firstName,
            'nickName'          => $object->nickName,
            'lastName'          => $object->lastName,
            'city'              => $object->city,
            'country'           => $object->country,
            'sex'               => $object->sex,
            'category'          => $object->category,
            'status'            => $object->status,
            'notes'             => $object->notes,
            'startTime'         => $object->startTime,
            'endTime'           => $object->endTime,
            'finalTime'         => $object->finalTime,
            'points'            => $object->points
        );
        return $retval;
    }
    
    public function serializeRacerInfoForSpeed($object) {
        $retval = array(
            'id'                => $object->id,
            'racerNumber'       => $object->racerNumber,
            'firstName'         => $object->firstName,
            'nickName'          => $object->nickName,
            'lastName'          => $object->lastName,
            'city'              => $object->city,
            'country'           => $object->country,
            'sex'               => $object->sex,
            'category'          => $object->category,
            'status'            => $object->status,
            'notes'             => $object->notes,
            'startTime'         => $object->startTime,
            'endTime'           => $object->endTime,
            'finalTime'         => $object->finalTime,
            'points'            => $object->points,
            'elapsed'           => $object->elapsed
        );
        return $retval;
    }
    
    public function serializeBoard($board) {
        $retval = array();
        foreach ($board as $object) {
            $aBoard = array(
                'id'                => $object->id,
                'name'              => $object->name,
                'race'              => $object->race,
                'code'              => $object->code,
                'racer'             => $object->racer,
                'status'            => $object->status,
                'pickTime'          => $object->pickTime,
                'completeTime'      => $object->completeTime,
                'determination'     => $object->determination,
                'payout'            => $object->payout,
                'finalTime'         => $object->finalTime,
                'dropOff'           => $object->dropOff,
                'dropOffTime'       => $object->dropOffTime,
                'pickUpCheckpoint'  => $object->pickUpCheckpoint,
                'stops'             => $object->stops
            );
            array_push($retval, $aBoard);
        }
        
        return $retval;
        
    }  
    
    public function serializeBoardForWorksim($board) {
        $retval = array();
        foreach ($board as $object) {
            $aBoard = array(
                'id'                => $object->id,
                'name'              => $object->name,
                'race'              => $object->race,
                'code'              => $object->code,
                'racer'             => $object->racer,
                'status'            => $object->status,
                'pickTime'          => $object->pickTime,
                'completeTime'      => $object->completeTime,
                'determination'     => $object->determination,
                'payout'            => $object->payout,
                'finalTime'         => $object->finalTime,
                'dropOff'           => $object->dropOff,
                'dropOffTime'       => $object->dropOffTime,
                'pickUpCheckpoint'  => $object->pickUpCheckpoint,
                'dueTime'           => $object->dueTime,
                'stops'             => $object->stops,
                'runsCheckpointsId' => $object->runsCheckpointsId
            );
            array_push($retval, $aBoard);
        }
        
        return $retval;
        
    }  
    
    public function generate() {
        parent::generate();
        $output = array();
        foreach ($this->checkpoints as $checkpoint) {
            array_push($output, $this->serialize($checkpoint));
        }
        echo json_encode($output);
    }
    
    public function serialize($object) {
        $retval = array(
            'id'                => $object->id,
            'name'              => $object->name,
            'address'           => $object->address
        );
        return $retval;
    }
    
    public function generateRacerOnCourseList() {
        parent::generate();
        echo json_encode($this->racersOnCourse);
        
    }
    
}

?>