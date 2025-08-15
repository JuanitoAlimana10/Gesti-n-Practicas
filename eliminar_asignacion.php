<?php
require 'conexion.php';
session_start();
if ($_SESSION['rol'] !== 'administrador') exit(json_encode(["success"=>false,"message"=>"No autorizado."]));
$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) exit(json_encode(["success"=>false,"message"=>"ID inválido."]));
$stmt = $conn->prepare("DELETE FROM asignaciones WHERE id=?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(["success"=>true,"message"=>"Asignación eliminada"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Error al eliminar"]);
}
