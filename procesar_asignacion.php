<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'conexion.php';
session_start();

// 1) Verificar permiso de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// 2) Validar campos requeridos (ya renombramos a 'grupo_id')
$required_fields = ['maestro_id', 'materia_id', 'carrera_id', 'grupo_id'];
$missing = [];
foreach ($required_fields as $f) {
    if (empty($_POST[$f])) $missing[] = $f;
}
if ($missing) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos: '.implode(', ', $missing)]);
    exit;
}

// 3) Asignar valores
$maestro_id = intval($_POST['maestro_id']);
$materia_id = intval($_POST['materia_id']);
$carrera_id = intval($_POST['carrera_id']);
$grupo_id   = intval($_POST['grupo_id']);

try {
    // 4) Verificar si ya existe esa asignación
    $check = $conn->prepare("
        SELECT id FROM asignaciones 
        WHERE maestro_id = ? AND materia_id = ? AND carrera_id = ? AND grupo_id = ?
    ");
    $check->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Esta asignación ya existe.']);
        exit;
    }

    // 5) Insertar la nueva asignación
    $ins = $conn->prepare("
        INSERT INTO asignaciones (maestro_id, materia_id, carrera_id, grupo_id)
        VALUES (?, ?, ?, ?)
    ");
    $ins->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
    if ($ins->execute()) {
        echo json_encode(['success' => true, 'message' => 'Asignación creada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

ob_end_flush();
