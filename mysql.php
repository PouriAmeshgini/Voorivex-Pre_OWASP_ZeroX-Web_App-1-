<?php
$host = 'localhost';
$username = 'dbuser';
$password = 'dbpassword';
$database = 'VooriMedDB';
$connection = new mysqli($host, $username, $password, $database);
if ($connection->connect_error) {
    die("Connection Failed: " . $connection->connect_error);
}