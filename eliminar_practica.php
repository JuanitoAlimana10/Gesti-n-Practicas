<?php
include 'conexion.php';
$id = $_GET['id'];

$conn->query("DELETE FROM practicas WHERE id=$id");
header("Location: index.php");
?>  