<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'conexion.php';
session_start();

// Verificar si es administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// Validar campos requeridos
$required_fields = ['maestro_id', 'materia_id', 'carrera_id', 'grupo_id'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos: ' . implode(', ', $missing_fields)]);
    exit;
}

// Asignar valores
$maestro_id = intval($_POST['maestro_id']);
$materia_id = intval($_POST['materia_id']);
$carrera_id = intval($_POST['carrera_id']);
$grupo_id = intval($_POST['grupo_id']);

try {
    // Verificar si la asignación ya existe
    $check = $conn->prepare("SELECT id FROM asignaciones WHERE maestro_id = ? AND materia_id = ? AND carrera_id = ? AND grupo_id = ?");
    $check->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Esta asignación ya existe.']);
        exit;
    }

    // Insertar nueva asignación
    $insert = $conn->prepare("INSERT INTO asignaciones (maestro_id, materia_id, carrera_id, grupo_id) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
    $insert_success = $insert->execute();

    if ($insert_success) {
        echo json_encode(['success' => true, 'message' => 'Asignación creada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

ob_end_flush();
?>