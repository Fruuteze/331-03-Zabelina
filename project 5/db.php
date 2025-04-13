<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flavianov331";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if(!$conn){
    die("Connection Failed". mysqli_connect_error());
} else {
    "Успех";
} ?>