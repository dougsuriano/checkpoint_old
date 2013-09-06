<?php
/******************************************************************************
dispatch.php
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
require_once('../api/util.php');
require_once('../api/models/Race.php');

$memcache = new Memcached();
$memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
if (!($retval = $memcache->get('currentRace'))) {
    $sql = $db->prepare("SELECT * FROM races WHERE id = (SELECT currentRace FROM params LIMIT 1)");
    if ($sql->execute()) {
        $race = $sql->fetchAll(PDO::FETCH_CLASS, "Race");
        //Now store the SQL event in the memcache
        $memcache->set('currentRace', $race[0], 0);
        $retval = $race[0];
    }        
}

switch($retval->dispatchMode) {
    case "1":
    include('dispatchers/dispatch_work_sim.php');
    break;
    case "2":
    include('dispatchers/dispatch_speed_ind.php');
    break;
}
?>