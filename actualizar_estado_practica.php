<?php
include 'conexion.php';
session_start();

// Validar que se reciban estados
if (!isset($_POST['estados']) || !is_array($_POST['estados'])) {
    die("No se recibieron estados válidos.");
}

$estados_validos = ['realizada', 'pendiente', 'no realizada'];

foreach ($_POST['estados'] as $practica_id => $nuevo_estado) {
    if (!in_array($nuevo_estado, $estados_validos)) continue;

    // Actualizar estado directamente en tabla fotesh
    $stmt = $conn->prepare("UPDATE fotesh SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $practica_id);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
