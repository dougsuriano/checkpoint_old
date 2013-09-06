<?php
/******************************************************************************
RaceView.php
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

RaceView
Race View
/******************************************************************************/

require_once('BaseView.php');
class RaceView extends BaseView {
    
    public $races;
    
    public function generate() {
        parent::generate();
        $output = array();
        foreach ($this->races as $race) {
            array_push($output, $this->serialize($race));
        }
        echo json_encode($output);
    }
    
    public function serialize($object) {
        $retval = array(
            'id'                => $object->id,
            'event'             => $object->event,
            'raceName'          => $object->raceName,
            'raceDateTime'      => $object->raceDateTime,
            'raceType'          => $object->raceType,
            'startStyle'        => $object->startStyle,
            'dispatchMode'      => $object->dispatchMode,
            'startTime'         => $object->startTime
            
        );
        return $retval;
    }
}