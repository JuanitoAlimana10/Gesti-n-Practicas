<?php
include 'conexion.php';
session_start();

header('Content-Type: application/json');

// Verificar que se haya enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$docente_id = $_POST['docente_id'] ?? null;
$carrera_id = $_POST['carrera_id'] ?? null;
$estados = $_POST['estados'] ?? [];

if (!$docente_id || !$carrera_id || !is_numeric($docente_id) || !is_numeric($carrera_id)) {
    echo json_encode(['success' => false, 'message' => 'Datos de docente o carrera inválidos.']);
    exit;
}

$errores = 0;

// Validar y actualizar estado solo si la práctica pertenece a esa carrera
foreach ($estados as $practica_id => $estado) {
    $estado = strtolower(trim($estado));
    if (!in_array($estado, ['pendiente', 'realizada', 'no realizada'])) {
        continue; // Saltar si es estado inválido
    }

    // Verificar si la práctica corresponde a la carrera seleccionada
    $stmt_verifica = $conn->prepare("
        SELECT f.id
        FROM fotesh f
        JOIN asignaciones a ON a.maestro_id = f.Maestro_id AND a.materia_id = f.Materia_id
        WHERE f.id = ? AND f.Maestro_id = ? AND a.carrera_id = ?
        LIMIT 1
    ");
    $stmt_verifica->bind_param("iii", $practica_id, $docente_id, $carrera_id);
    $stmt_verifica->execute();
    $res_verifica = $stmt_verifica->get_result();

    if ($res_verifica->num_rows > 0) {
        // Actualizar el estado si la práctica es válida para esa carrera
        $stmt_actualiza = $conn->prepare("UPDATE fotesh SET estado = ? WHERE id = ?");
        $stmt_actualiza->bind_param("si", $estado, $practica_id);
        if (!$stmt_actualiza->execute()) {
            $errores++;
        }
    }
}

if ($errores === 0) {
    echo json_encode(['success' => true, 'message' => 'Estados actualizados correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ocurrieron errores al actualizar algunos estados.']);
}
?>
