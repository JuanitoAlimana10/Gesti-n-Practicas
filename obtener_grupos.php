<?php
require 'conexion.php';

$carrera_id = intval($_GET['carrera_id'] ?? 0);

if ($carrera_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre FROM grupos WHERE carrera_id = ? AND activo = 1 ORDER BY nombre");
$stmt->bind_param("i", $carrera_id);
$stmt->execute();

$res = $stmt->get_result();
$grupos = [];

while ($row = $res->fetch_assoc()) {
    $grupos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($grupos);
