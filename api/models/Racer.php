<?php
/******************************************************************************
Racer.php
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

Racer.php
A racer is an entry into the race.
/******************************************************************************/


class Racer {
    public $id;
    public $racerNumber;
    public $firstName;
    public $lastName;
    public $nickName;
    public $city;
    public $country;
    public $imageUri;
    public $category;
    public $sex;
    public $bikeType;
    public $paid;
}
?>