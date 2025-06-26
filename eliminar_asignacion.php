<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'conexion.php';
session_start();

// Verificar permisos
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

// Validar ID
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}

$asignacion_id = intval($_POST['id']);

try {
    // Eliminar la asignación
    $delete = $conn->prepare("DELETE FROM asignaciones WHERE id = ?");
    $delete->bind_param("i", $asignacion_id);
    $delete_success = $delete->execute();

    if ($delete_success) {
        echo json_encode(['success' => true, 'message' => 'Asignación eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la asignación.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

ob_end_flush();
?>
