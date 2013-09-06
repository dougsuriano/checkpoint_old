<?php
/******************************************************************************
loginpage.php
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
?>
<html>
<!DOCTYPE html>
<head>
	<title>CHECKPOINT</title>
		<link rel="stylesheet" href="../app/css/checkpoint.css" type="text/css" charset="utf-8">
	</head>
<body>
	<div class = "container">
		<div class = "banner">
			<h1>CHECKPOINT</h1>
		</div>
		<div class = "menu">
	<form action = "auth.php" method = "post">
	<label>User</label> <input type="text" name="username" ><br \>
	<label>Password</label> <input type="password" name="password"><br \>
	<input type="submit" value="Submit">
	</form>
	</div>
</div>
</body>
</html>