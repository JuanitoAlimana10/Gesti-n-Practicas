<?php
include 'conexion.php';
session_start();

// Solo jefes de carrera pueden cambiar el estado
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_carrera') {
    header("Location: login.php");
    exit;
}

$pdf_id = $_POST['pdf_id'] ?? null;
$nuevo_estado = $_POST['estado'] ?? null;

// Validar estado permitido
$estados_validos = ['realizada', 'pendiente', 'no realizada'];
if (!$pdf_id || !in_array($nuevo_estado, $estados_validos)) {
    die("Datos inválidos.");
}

// Actualizar estado
$stmt = $conn->prepare("UPDATE pdfs SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $nuevo_estado, $pdf_id);
$stmt->execute();

// Redirigir a la página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
