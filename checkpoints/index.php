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
Main router for Checkpoint Worker API
/******************************************************************************/

require '../lib/AltoRouter.php';
require '../lib/Password.php';
require_once 'CheckpointWorker.php';


//Perform Authorization

$password = new Password(10);
//No password/username entered
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="checkpoint"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}
else {
    $cw = new CheckpointWorker();
    $userInfo = $cw->getUser($_SERVER['PHP_AUTH_USER']);
    if ($password->verify($_SERVER['PHP_AUTH_PW'], $userInfo->password) != 1) {
        header('WWW-Authenticate: Basic realm="checkpoint"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
    
    $cw->currentUser = $userInfo;

    $router = new AltoRouter();
    $router->setBasePath('/checkpoints');

    //User
    $router->map('GET','/user/', array('action' => 'getUserInfo'));
    $router->map('GET','/auth/', array('action' => 'getListOfAuthorizedCheckpoints'));
    $router->map('GET','/racer/[i:id]', array('action' => 'getRacer'));
    $router->map('POST','/picking/', array('action' => 'pickUp'));
    $router->map('POST','/dropping/', array('action' => 'dropOff'));
    $router->map('GET','/picking/', array('action' => 'pickUp'));



    //Match the request, then init a controller and call the action on that controller
    $match = $router->match();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT') {
        $json = file_get_contents('php://input');
        $cw->setRequestParams($json);
    }


    switch (count($match["params"])) {
        case 0:
        $cw->$match["target"]["action"](); 
        break;
    
        case 1:
        $cw->$match["target"]["action"]($match['params']['id']); 
        break;
    }


}



?>