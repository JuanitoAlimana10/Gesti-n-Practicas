<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'conexion.php';
session_start();

// Verificar permisos de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// Validar que se recibió el ID
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}

$asignacion_id = intval($_POST['id']);

try {
    // Preparar y ejecutar DELETE
    $stmt = $conn->prepare("DELETE FROM asignaciones WHERE id = ?");
    $stmt->bind_param("i", $asignacion_id);
    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Asignación eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la asignación.']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

ob_end_flush();
