<?php
include 'conexion.php';
$id = $_GET['id'];
$conn->query("UPDATE tipodeusuarios SET estado = 'activo' WHERE id = $id");
header("Location: gestionar_usuarios.php");
?>