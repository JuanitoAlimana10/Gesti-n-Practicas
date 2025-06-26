<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "gestion_practicas";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
