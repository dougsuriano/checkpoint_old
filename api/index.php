<?php
/******************************************************************************
index.php
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

index.php
Main router for Checkpoint API
/******************************************************************************/

session_start();
if (!isset($_SESSION['loggedIn'])) {
    var_dump($_SESSION);
}
require '../lib/AltoRouter.php';
spl_autoload_register('apiAutoload');
function apiAutoload($classname)
{
    if (preg_match('/[a-zA-Z]+Controller$/', $classname)) {
        include __DIR__ . '/controllers/' . $classname . '.php';
        return true;
    }
}


$router = new AltoRouter();
$router->setBasePath('/api');

//Current Events/Races

//Event Routing
$router->map('GET','/events/', array('controller' => 'EventController', 'action' => 'getAllEvents'));
$router->map('GET','/events/current', array('controller' => 'EventController', 'action' => 'displayCurrentEvent'));
$router->map('POST','/events/current/[i:id]', array('controller' => 'EventController', 'action' => 'userSetCurrentEvent'));
$router->map('GET','/events/[i:id]', array('controller' => 'EventController', 'action' => 'getEventById'));
$router->map('POST','/events/', array('controller' => 'EventController', 'action' => 'createNewEvent'));
$router->map('DELETE','/events/[i:id]', array('controller' => 'EventController', 'action' => 'deleteEvent'));


//Race Routing
$router->map('GET','/races/', array('controller' => 'RaceController', 'action' => 'getAllRaces'));
$router->map('GET','/races/[i:id]', array('controller' => 'RaceController', 'action' => 'getOneRace'));
$router->map('POST','/races/', array('controller' => 'RaceController', 'action' => 'createRace'));
$router->map('PUT','/races/[i:id]', array('controller' => 'RaceController', 'action' => 'editRace'));
$router->map('DELETE','/races/[i:id]', array('controller' => 'RaceController', 'action' => 'deleteRace'));
$router->map('GET','/races/current', array('controller' => 'RaceController', 'action' => 'displayCurrentRace'));
$router->map('POST','/races/current/[i:id]', array('controller' => 'RaceController', 'action' => 'userSetCurrentRace'));

$router->map('GET','/races/[i:id]/jobs', array('controller' => 'JobController', 'action' => 'getAllJobsInRace'));
$router->map('GET','/races/[i:id]/racers/in', array('controller' => 'RacerController', 'action' => 'getRacersInRace'));
$router->map('GET','/races/[i:id]/racers/not', array('controller' => 'RacerController', 'action' => 'getRacersNotInRace'));
$router->map('POST','/races/[i:id]/racers/in', array('controller' => 'RaceController', 'action' => 'addRacersToRace'));
$router->map('POST','/races/[i:id]/racers/not', array('controller' => 'RaceController', 'action' => 'removeRacersFromRace'));


//Checkpoint Routing
$router->map('GET','/checkpoints/', array('controller' => 'CheckpointController', 'action' => 'getAllCheckpoints'));
$router->map('GET','/checkpoints/[i:id]', array('controller' => 'CheckpointController', 'action' => 'getOneCheckpoint'));
$router->map('POST','/checkpoints/', array('controller' => 'CheckpointController', 'action' => 'createCheckpoint'));
$router->map('PUT','/checkpoints/[i:id]', array('controller' => 'CheckpointController', 'action' => 'editCheckpoint'));
$router->map('DELETE','/checkpoints/[i:id]', array('controller' => 'CheckpointController', 'action' => 'deleteCheckpoint'));

//Racer Routing
$router->map('GET','/racers/', array('controller' => 'RacerController', 'action' => 'getAllRacers'));
$router->map('GET','/racers/[i:id]', array('controller' => 'RacerController', 'action' => 'getOneRacer'));
$router->map('POST','/racers/', array('controller' => 'RacerController', 'action' => 'createRacer'));
$router->map('PUT','/racers/[i:id]', array('controller' => 'RacerController', 'action' => 'editRacer'));
$router->map('DELETE','/racers/[i:id]', array('controller' => 'RacerController', 'action' => 'deleteRacer'));



//Job Routing
$router->map('GET','/jobs/[i:id]', array('controller' => 'JobController', 'action' => 'getAllJobs'));
//$router->map('GET','/jobs/[i:id]', array('controller' => 'JobController', 'action' => 'getOneJob'));
$router->map('POST','/jobs/', array('controller' => 'JobController', 'action' => 'createJob'));
$router->map('PUT','/jobs/[i:id]', array('controller' => 'JobController', 'action' => 'editJob'));
$router->map('DELETE','/jobs/[i:id]', array('controller' => 'JobController', 'action' => 'deleteJob'));

//User Routing
$router->map('GET','/users/', array('controller' => 'UserController', 'action' => 'getAllUsers'));
$router->map('POST','/users/', array('controller' => 'UserController', 'action' => 'createUser'));

//Dispatch Routing
$router->map('GET','/dispatch/[i:id]/speed', array('controller' => 'DispatchController', 'action' => 'getRacerInfoAndCurrentBoardForSpeedRace'));
$router->map('GET','/dispatch/[i:id]/worksim', array('controller' => 'DispatchController', 'action' => 'getRacerInfoAndCurrentBoardForWorkSim'));
$router->map('POST','/dispatch/speed/start', array('controller' => 'DispatchController', 'action' => 'dispatchRunAndStartRacerClock'));
$router->map('POST','/dispatch/speed/next', array('controller' => 'DispatchController', 'action' => 'markJobAsDroppedAndMoveToNextManifest'));
$router->map('POST','/dispatch/speed/finish', array('controller' => 'DispatchController', 'action' => 'markJobAsDroppedAndFinishRacer'));
$router->map('POST','/dispatch/dq', array('controller' => 'RaceController', 'action' => 'dqRacerFromRace'));
$router->map('POST','/dispatch/dnf', array('controller' => 'RaceController', 'action' => 'dnfRacerFromRace'));
$router->map('POST','/dispatch/unstart', array('controller' => 'RaceController', 'action' => 'unStartRacer'));
$router->map('GET','/dispatch/time', array('controller' => 'DispatchController', 'action' => 'displayRaceTime'));
$router->map('POST','/dispatch/worksim/dispatch', array('controller' => 'DispatchController', 'action' => 'dispatchJobForWorkSim'));  
$router->map('POST','/dispatch/worksim/drop', array('controller' => 'DispatchController', 'action' => 'dropJobForWorksim'));
$router->map('GET','/dispatch/worksim/run/[a:id]', array('controller' => 'DispatchController', 'action' => 'getRunForEditing'));
$router->map('PUT','/dispatch/worksim/run/[a:id]', array('controller' => 'DispatchController', 'action' => 'editRun'));
$router->map('DELETE','/dispatch/worksim/run/[a:id]', array('controller' => 'DispatchController', 'action' => 'removeRun'));
$router->map('GET','/dispatch/course/', array('controller' => 'DispatchController', 'action' => 'getListOfRacersOnCourse'));


$router->map('POST','/results/', array('controller' => 'ResultsController', 'action' => 'generateResults'));
  

//Match the request, then init a controller and call the action on that controller
$match = $router->match();


$controller = new $match["target"]["controller"]($_SESSION['username']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $json = file_get_contents('php://input');
    $controller->setRequestParams($json);
}

switch (count($match["params"])) {
    case 0:
    $controller->$match["target"]["action"](); 
    break;
    
    case 1:
    $controller->$match["target"]["action"]($match['params']['id']); 
    break;
}






?>