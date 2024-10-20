<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "gestion_des_tâches_avec_missions";

$conn = new mysqli($host,$user,$password,$database);


// la verification 
if ($conn->connect_error){
    die("Connection failed : ".$conn->connect_error);
// $AdminACcount ici 
}