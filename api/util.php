<?php
/******************************************************************************
util.php
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

util.php
Defines constants and other utility functions
/******************************************************************************/

//Database Comunications
include('db.php');

//Password
define('PASSWORD_ROUNDS', 10);

//Memcache Settings
define('MEMCACHE_HOST', 'localhost');
define('MEMCACHE_PORT', 11211);

//Racer Categories
define('RACER_CATEGORY_MESSENGER', 1);
define('RACER_CATEGORY_EX_MESSENGER', 2);
define('RACER_CATEGORY_NON_MESSENGER', 3);

//Race Entry Status
define('RACE_ENTRY_ENTERED_STATUS', 1);
define('RACE_ENTRY_DQ_STATUS', 2);
define('RACE_ENTRY_SCRATCH_STATUS', 3);
define('RACE_ENTRY_DROP_STATUS', 4);
define('RACE_RACING_STATUS', 5);
define('RACE_FINISHED_STATUS', 6);
define('RACE_ENTRY_DNF_STATUS', 7);

//Dispatch Modes
define('DISPATCH_MODE_WORK_SIM', 1);
define('DISPATCH_MODE_INDVIDUAL', 2);

//Users
define('USER_LEVEL_ADMIN', 1);
define('USER_LEVEL_DISPATCHER', 2);
define('USER_LEVEL_CHECKPOINT_WORKER', 3);

//Race TYPES
define('RACE_TYPE_INDIVIDUAL_TIME', 1); //Racer has to complete all the jobs, 
//person who complets jobs first wins.
define('RACE_TYPE_WORK_SIM', 2); //Racer has to make most money.

//Win MEthods
define('WIN_TYPE_FASTEST_TIME', 1);
define('WIN_TYPE_MOST_POINTS', 2);

//Start Methods
define('START_STYLE_LE_MANS', 1);
define('START_STYLE_INDIVIDUAL', 2);

//Runs
define('RUN_CODE_LENGTH', 3);

//RUN Status
define('RUN_STATUS_ASSIGNED_NOT_DISPATCHED', 1);
define('RUN_PICKED', 2);
define('RUN_DROPPED', 3);

//Job Determinations
define('RUN_DETERMINATION_ON_TIME',1);
define('RUN_DETERMINATION_LATE',2);
define('RUN_DETERMINATION_NOT_DROPPED',3);

//Bike Type
define('RACER_BIKE_TYPE_TRACK', 1);
define('RACER_BIKE_TYPE_NOT_TRACK', 2);




?>