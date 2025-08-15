<?php
require 'conexion.php';
session_start();
if ($_SESSION['rol'] !== 'administrador') exit(json_encode(["success"=>false,"message"=>"No autorizado."]));
$id = $_POST['id'] ?? null;
$maestro = $_POST['maestro_id'] ?? null;
$materia = $_POST['materia_id'] ?? null;
$carrera = $_POST['carrera_id'] ?? null;
$grupo = $_POST['grupo_id'] ?? null;
if (!$id || !$maestro || !$materia || !$carrera || !$grupo) exit(json_encode(["success"=>false,"message"=>"Faltan datos."]));
$stmt = $conn->prepare("UPDATE asignaciones SET maestro_id=?, materia_id=?, carrera_id=?, grupo_id=? WHERE id=?");
$stmt->bind_param("iiiii", $maestro, $materia, $carrera, $grupo, $id);
if ($stmt->execute()) {
    echo json_encode(["success"=>true,"message"=>"Asignación actualizada"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Error al actualizar"]);
}
