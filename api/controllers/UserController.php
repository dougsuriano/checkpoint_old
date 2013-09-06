<?php
/******************************************************************************
UserController.php
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

UserController
User Controller
/******************************************************************************/

require_once('BaseController.php');
require_once('../api/util.php');
require_once('../api/models/User.php');
require_once('../api/views/UserView.php');
require_once('../lib/Password.php');


class UserController extends BaseController {
    
    public function getAllUsers() {
        try {
            $sql = $this->db->prepare("SELECT id, username, first, last, level FROM users");
            if ($sql->execute()) {
                $users = $sql->fetchAll(PDO::FETCH_CLASS, 'User');
                $view = new UserView();
                $view->users = $users;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getAllUsers", $ex);
            $view = new UserView();
            $view->badRequest();
        }
    }
    
    public function getOneUsers($id) {
        try {
            $sql = $this->db->prepare("SELECT id, username, first, last, level FROM users WHERE id = :id");
            $sql->bindParam(':id', $id);
            if ($sql->execute()) {
                $runs = $sql->fetchAll(PDO::FETCH_CLASS, 'User');
                $view = new UserView();
                $view->users = $users;
                $view->generate();
            }
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("getOneUser($id)", $ex);
            $view = new UserView();
            $view->badRequest();
        }
    }
    
    public function createUser() {
        try {
            $sql = $this->db->prepare("INSERT INTO users (username, password, first, last, level) VALUES (:username, :password, :first, :last, :level)");
            $sql->bindParam(':username', $this->requestParams['username']);
            $sql->bindParam(':password', $this->generateHash($this->requestParams['password']));
            $sql->bindParam(':first', $this->requestParams['first']);
            $sql->bindParam(':last', $this->requestParams['last']);
            $sql->bindParam(':level', $this->requestParams['level']);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("createUser()", $ex);
            $view = new UserView();
            $view->badRequest();
        }
    }
    
    public function editUser($id) {
        try {
            $sql = $this->db->prepare("UPDATE users SET username = :username, first = :first, last = :last, level = :level WHERE id = :id");
            $sql->bindParam(':username', $this->requestParams['username']);
            $sql->bindParam(':first', $this->requestParams['first']);
            $sql->bindParam(':last', $this->requestParams['last']);
            $sql->bindParam(':level', $this->requestParams['level']);
            $sql->bindParam(':id', $id);
            $sql->execute();
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("editUser($id)", $ex);
            $view = new UserView();
            $view->badRequest();
        }    
    }
    
    public function deleteUser($id) {
        try {
           $sql = $this->db->prepare("DELETE FROM users WHERE id = :id");
           $sql->bindParam(':id', $id);
           $sql->execute(); 
        }
        catch (PDOException $ex) {
            $this->logIntoErrorTable("deleteUser($id)", $ex);
            $view = new UserView();
            $view->badRequest();
        }
    }
    
    private function generateHash($password) {
        $pw = new Password(PASSWORD_ROUNDS);
        $hash = $pw->hash($password);
        return $hash;
    }
    
}

?>