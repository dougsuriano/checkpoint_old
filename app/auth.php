<?php
/******************************************************************************
auth.php
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
session_start();
require_once("Internal.php");
require_once("../lib/Password.php");
require_once("../api/util.php");

$internal = new Internal();
//Check to see if a cookie exisits for the user name
if (isset($_COOKIE['loggedIn'])) {
	$cookie = explode(" ", $_COOKIE['loggedIn']);
	$user = $cookie[0];
	if ($id = $internal->checkForCookieMatch($cookie[1], $cookie[0])) {
		$internal->removeCookie($id);
		$newcookie = $internal->makeNewCookieToken($cookie[0]);
		$inTwoMonths = 60 * 60 * 24 * 60 + time(); 
		setcookie('loggedIn', "$user $newcookie" , $inTwoMonths); 
		$_SESSION['loggedIn'] = true;
		$_SESSION['userId'] = $user;
		
		if (isset($_GET['redirect'])) {
			header("Location: " . $_GET['redirect']);
		}
		else {
			header("Location: index.php");
		}
	}
	else {
		if (isset($_SERVER['HTTP_COOKIE'])) {
		    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		    foreach($cookies as $cookie) {
		        $parts = explode('=', $cookie);
		        $name = trim($parts[0]);
		        setcookie($name, '', time()-1000);
		        setcookie($name, '', time()-1000, '/');
		    }
		}
		header("Location: loginpage.php");
	}
}
else {
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$password = new Password(7);
		$dbhash = $internal->getPasswordHash($_POST['username']);
		if ($password->verify($_POST['password'], $dbhash) != 1) {
			header("Location: loginpage.php?error=BadLogin");
			
		}
		else {
			$user = $internal->getUserInfo($_POST['username']);
			$newcookie = $internal->makeNewCookieToken($user['id']);
			$inTwoMonths = 60 * 60 * 24 * 60 + time(); 
			$userId = $user['id'];
            setcookie('loggedIn', "$userId $newcookie" , $inTwoMonths); 
			$_SESSION['loggedIn'] = true;
			$_SESSION['userId'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first'] = $user['first'];
            $_SESSION['last'] = $user['last'];
            $_SESSION['level'] = $user['level'];
			if (isset($_GET['redirect'])) {
				header("Location: " . $_GET['redirect']);
			}
			else {
				header("Location: index.php");
			}
			
		}
	}
	else {
		header("Location:loginpage.php");
	}
}
?>