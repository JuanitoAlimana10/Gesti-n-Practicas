<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    die("Acceso denegado.");
}

$practica_id = $_GET['id'] ?? null;
if (!$practica_id || !is_numeric($practica_id)) {
    die("ID de práctica inválido.");
}

// Obtener la práctica y su Maestro_id
$stmt = $conn->prepare("SELECT Maestro_id FROM fotesh WHERE id = ?");
$stmt->bind_param("i", $practica_id);
$stmt->execute();
$result = $stmt->get_result();
$practica = $result->fetch_assoc();

if (!$practica) {
    die("Práctica no encontrada.");
}

// Redirigir a formulario_edicion_pdf.php con los parámetros correctos
$maestro_id = $practica['Maestro_id'];
header("Location: formulario_edicion_pdf.php?id=$practica_id&maestro_id=$maestro_id");
exit;
?>
