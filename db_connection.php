<?php
if (getenv('DATABASE_HOST')) {
    $host = "db";
    $username = "root";
    $password = "root";
    $database = "hira_property";
} else {
    $host = "localhost:3307";
    $username = "root";
    $password = "";
    $database = "hira_property_db";
}

$conn = mysqli_connect($host, $username, $password, $database);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
?>