<?php
/******************************************************************************
memcacheCheck.php
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

/******************************************************************************/
include("session.php");

require_once('../api/util.php');
require_once('../api/models/Race.php');

$memcache = new Memcached();
$memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);

//Get the memcache value
$memRace = $memcache->get('currentRace');

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);

    $sql = $db->prepare("SELECT * FROM races WHERE id = (SELECT currentRace FROM params LIMIT 1)");
    if ($sql->execute()) {
        $race = $sql->fetchAll(PDO::FETCH_CLASS, "Race");
        //Now store the SQL event in the memcache
        $mysqlRace = $race[0];
    }        


?>
<html>
<!DOCTYPE html>
<head>
	<title>CHECKPOINT</title>
		<link rel="stylesheet" href="../app/css/checkpoint.css" type="text/css" charset="utf-8">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script src="scripts/users.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	</head>
<body>
	<div class = "container">
        <table>
            <tr>
                <th></th><th>MySql</th><th>Memcached</th>
            </tr>
            <tr>
                <th>Id</th><?php echo "<td>" . $memRace->id . "</td><td>" . $mysqlRace->id  . "</td>";?>
            </tr>
            <tr>
                <th>Event</th><?php echo "<td>" . $memRace->event . "</td><td>" . $mysqlRace->event  . "</td>";?>
            </tr>
            <tr>
                <th>Race Name</th><?php echo "<td>" . $memRace->raceName . "</td><td>" . $mysqlRace->raceName  . "</td>";?>
            </tr>
            <tr>
                <th>Race Date Time</th><?php echo "<td>" . $memRace->raceDateTime . "</td><td>" . $mysqlRace->raceDateTime  . "</td>";?>
            </tr>
            <tr>
                <th>Race Type</th><?php echo "<td>" . $memRace->raceType . "</td><td>" . $mysqlRace->raceType  . "</td>";?>
            </tr>
            <tr>
                <th>Start Style</th><?php echo "<td>" . $memRace->startStyle . "</td><td>" . $mysqlRace->startStyle  . "</td>";?>
            </tr>
            <tr>
                <th>Dispatch Mode</th><?php echo "<td>" . $memRace->dispatchMode . "</td><td>" . $mysqlRace->dispatchMode  . "</td>";?>
            </tr>
            <tr>
                <th>Start Time</th><?php echo "<td>" . $memRace->startTime . "</td><td>" . $mysqlRace->startTime  . "</td>";?>
            </tr>
            
            
            
		
    </div>
</div>
</body>
</html>