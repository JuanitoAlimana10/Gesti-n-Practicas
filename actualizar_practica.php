<?php
require 'conexion.php';
require 'vendor/autoload.php';
use setasign\Fpdi\Fpdi;

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    die("Acceso denegado.");
}

// Validar datos recibidos
$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$objetivo = $_POST['objetivo'] ?? '';
$laboratorio = $_POST['laboratorio'] ?? '';
$horario = $_POST['horario'] ?? '';
$fechaProgramada = $_POST['fechaProgramada'] ?? '';
$fechaReal = $_POST['fechaReal'] ?? '';
$rubrica = $_POST['rubrica'] ?? '';

if (!$id) {
    die("ID de práctica no especificado.");
}

// Obtener pdf_id original
$stmt_old = $conn->prepare("SELECT pdf_id FROM fotesh WHERE id = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$res = $stmt_old->get_result()->fetch_assoc();
$pdf_id = $res['pdf_id'] ?? null;

if (!$pdf_id) {
    die("No se encontró el PDF vinculado.");
}

// Actualizar práctica
$stmt = $conn->prepare("UPDATE fotesh SET 
    Nombre_Practica = ?, 
    Objetivo = ?, 
    Laboratorio = ?, 
    Horario = ?, 
    Fecha_Propuesta = ?, 
    Fecha_Real = ?, 
    Tipo_de_Laboratorio = ?
    WHERE id = ?");
$stmt->bind_param("sssssssi", $nombre, $objetivo, $laboratorio, $horario, $fechaProgramada, $fechaReal, $rubrica, $id);
$stmt->execute();

// Recalcular estado
$estado = 'pendiente';
if (!empty($fechaReal)) {
    $estado = 'realizada';
} elseif (!empty($fechaProgramada) && strtotime($fechaProgramada) < time()) {
    $estado = 'no realizada';
}
$stmt_estado = $conn->prepare("UPDATE fotesh SET estado = ? WHERE id = ?");
$stmt_estado->bind_param("si", $estado, $id);
$stmt_estado->execute();

// Regenerar el PDF relacionado (puede haber más prácticas en él)
$stmt_ruta = $conn->prepare("SELECT ruta FROM pdfs WHERE id = ?");
$stmt_ruta->bind_param("i", $pdf_id);
$stmt_ruta->execute();
$pdf_row = $stmt_ruta->get_result()->fetch_assoc();
$pdf_path = $pdf_row['ruta'] ?? null;

if (!$pdf_path || !file_exists($pdf_path)) {
    die("No se encontró el archivo PDF original.");
}

// Obtener todas las prácticas vinculadas a este PDF
$stmt_practicas = $conn->prepare("SELECT * FROM fotesh WHERE pdf_id = ?");
$stmt_practicas->bind_param("i", $pdf_id);
$stmt_practicas->execute();
$practicas_result = $stmt_practicas->get_result();

// Crear nuevo PDF temporal
$tmp_pdf = "temp_actualizado_" . time() . ".pdf";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$y = 20;
while ($p = $practicas_result->fetch_assoc()) {
    $pdf->Text(10, $y, "Práctica: " . $p['Nombre_Practica']);
    $pdf->Text(10, $y + 10, "Objetivo: " . $p['Objetivo']);
    $pdf->Text(10, $y + 20, "Laboratorio: " . $p['Laboratorio']);
    $pdf->Text(10, $y + 30, "Horario: " . $p['Horario']);
    $pdf->Text(10, $y + 40, "Fechas: " . $p['Fecha_Propuesta'] . " / " . $p['Fecha_Real']);
    $pdf->Text(10, $y + 50, "Rubrica: " . $p['Tipo_de_Laboratorio']);
    $y += 70;
    if ($y > 750) {
        $pdf->AddPage();
        $y = 20;
    }
}

$pdf->Output("F", $tmp_pdf);

// Reemplazar el PDF anterior
if (file_exists($pdf_path)) {
    unlink($pdf_path);
}
rename($tmp_pdf, $pdf_path);

echo "✅ Práctica actualizada y PDF regenerado correctamente.";
?>
