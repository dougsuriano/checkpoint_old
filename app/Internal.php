<?php
/******************************************************************************
Internal.php
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
class Internal {
	
	function __construct() {
		try {
			$this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
			$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
		catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}
	
	function getCookieIds($user) {
		$sql = $this->db->prepare("SELECT id, cookie FROM cookies WHERE user = :user");
		$sql->bindParam(":user", $user);
		$retval = array();
		if ($sql->execute()) {
			while($cookie = $sql->fetch(PDO::FETCH_ASSOC)) {
				array_push($retval, $cookie);
			}
			return $retval;
		}
	}
	
	function checkForCookieMatch($cookie, $user) {
		$ids = $this->getCookieIds($user);
		$retval = false;
		foreach ($ids as $id) {
			if ($id['cookie'] == $cookie) {
				return $id['id'];
			} 
		}
		return $retval;
	}
	
	function removeCookie($id) {
		$sql = $this->db->prepare("DELETE FROM cookies WHERE id = :id");
		$sql->bindParam(":id", $id);
		$sql->execute();
	}
	
	function makeNewCookieToken($user) {
		$random = hexdec(bin2hex(openssl_random_pseudo_bytes(128)));
		$randomString = number_format($random, 0, '.', ''); 
		$sql = $this->db->prepare("INSERT INTO cookies (user, cookie) VALUES (:user, :randomString)");
		$sql->bindParam(":user", $user);
		$sql->bindParam(":randomString", $randomString);
		$sql->execute();
		return $randomString;
	}
	
	function getPasswordHash($user) {
		$sql = $this->db->prepare("SELECT password FROM users WHERE username = :username");
		$sql->bindParam(":username", $user);
		if ($sql->execute()) {
			$hash = $sql->fetch(PDO::FETCH_ASSOC);
			return $hash['password'];
		}
	}
	
	function getUserId($user) {
		$sql = $this->db->prepare("SELECT id FROM users WHERE username = :username");
		$sql->bindParam(":username", $user);
		if ($sql->execute()) {
			$hash = $sql->fetch(PDO::FETCH_ASSOC);
			return $hash['id'];
		}
	}
    
    function getUserInfo($user) {
		$sql = $this->db->prepare("SELECT id, username, first, last, level FROM users WHERE username = :user");
		$sql->bindParam(':user', $user);
		if ($sql->execute()) {
			$user = $sql->fetch(PDO::FETCH_ASSOC);
		}
        return $user;
    }
}
    ?>