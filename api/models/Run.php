<?php
/******************************************************************************
Run.php
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

Run.php
A run is a specific instance of a job attempted by a racer. A race organizer
will create Jobs. Jobs can be thought of as the 'template' of the order. All
the actual parameters about a run is stored in the Job (Pickup time, payout etc).
The Run is the instance of that job, normally at race time. 
/******************************************************************************/


class Run {
    public $id;
    public $race;
    public $code;
    public $job;
    public $racer;
    public $status;
    public $pickTime;
    public $dropTimes;
    public $determination;
    public $payout;
    public $finalTime;
}

?>