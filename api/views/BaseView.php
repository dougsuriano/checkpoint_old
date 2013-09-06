<?php
/******************************************************************************
BaseView.php
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

Base View
Base Vie
/******************************************************************************/

class BaseView {
    
    public function generate() {
        header('Content-Type: application/json');
    }
    
    public function badRequest() {
        http_response_code(400);
        echo "400 - Bad Request";
        exit;
    }
    
    public function generateError($errorMessage) {
        http_response_code(420);
        header('Content-Type: application/json');
        $errorOutput = array(
            'errorMessage'  => $errorMessage
        );
        echo json_encode($errorOutput);
        exit;
    }
}

?>