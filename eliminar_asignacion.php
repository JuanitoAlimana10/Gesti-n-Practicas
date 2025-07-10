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
if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID inválido o no proporcionado.']);
    exit;
}

$asignacion_id = intval($_POST['id']);

try {
    // Verificar si la asignación existe antes de eliminar
    $verificar = $conn->prepare("SELECT id FROM asignaciones WHERE id = ?");
    $verificar->bind_param("i", $asignacion_id);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'La asignación no existe.']);
        $verificar->close();
        exit;
    }
    $verificar->close();

    // Eliminar asignación
    $stmt = $conn->prepare("DELETE FROM asignaciones WHERE id = ?");
    $stmt->bind_param("i", $asignacion_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Asignación eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la asignación.']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

ob_end_flush();
