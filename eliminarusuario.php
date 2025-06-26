<?php
include 'conexion.php';
$id = $_GET['id'];
$conn->query("DELETE FROM tipousuarios WHERE id = $id");
header("Location: gestionar_usuarios.php");
?>