<?php
include 'autorizacion_compartida.php';
require 'conexion.php';

// Validar que exista el ID de práctica
$practica_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$practica_id) {
    die("ID de práctica inválido o no proporcionado.");
}

// Obtener datos de la práctica
$stmt = $conn->prepare("SELECT Maestro_id, pdf_id FROM fotesh WHERE id = ?");
$stmt->bind_param("i", $practica_id);
$stmt->execute();
$practica = $stmt->get_result()->fetch_assoc();

if (!$practica) {
    die("Práctica no encontrada con el ID proporcionado.");
}

$maestro_id = $practica['Maestro_id'];
$pdf_id     = $practica['pdf_id'];

// Obtener nombre de la carrera y periodo_id desde el PDF
$stmt_pdf = $conn->prepare("SELECT carrera, periodo_id FROM pdfs WHERE id = ?");
$stmt_pdf->bind_param("i", $pdf_id);
$stmt_pdf->execute();
$pdf_info = $stmt_pdf->get_result()->fetch_assoc();

if (!$pdf_info) {
    die("No se pudo obtener la información del PDF.");
}

$carrera_nombre = $pdf_info['carrera'];
$periodo_id     = $pdf_info['periodo_id'];

// Obtener carrera_id a partir del nombre de carrera
$stmt_carrera = $conn->prepare("SELECT id FROM carreras WHERE nombre = ? LIMIT 1");
$stmt_carrera->bind_param("s", $carrera_nombre);
$stmt_carrera->execute();
$carrera_row = $stmt_carrera->get_result()->fetch_assoc();

if (!$carrera_row || empty($carrera_row['id'])) {
    die("No se pudo determinar la carrera.");
}

$carrera_id = $carrera_row['id'];

// Redirigir al formulario de edición con todos los parámetros correctos
header("Location: formulario_edicion_pdf.php?id=$practica_id&maestro_id=$maestro_id&carrera_id=$carrera_id&periodo_id=$periodo_id");
exit;
