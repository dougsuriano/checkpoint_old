<?php
/******************************************************************************
RunView.php
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

RunView
Run View
/******************************************************************************/

require_once('BaseView.php');

class RunView extends BaseView {
    
    public $runs;
    
    public function generate() {
        parent::generate();
        $output = array();
        foreach ($this->runs as $run) {
            array_push($output, $this->serialize($run));
        }
        echo json_encode($output);
    }
    
    public function serialize($object) {
        $retval = array(
            'id'                => $object->id,
            'code'              => $object->code,
            'job'               => $object->job,
            'racer'             => $object->racer,
            'status'            => $object->status,
            'pickTime'          => $object->pickTime,
            'determination'     => $object->determination,
            'payout'            => $object->payout,
            'finalTime'         => $object->finalTime
        );
        return $retval;
    }
}