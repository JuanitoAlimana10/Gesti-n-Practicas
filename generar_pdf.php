<?php
include 'conexion.php';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_practicas.pdf"');

$total = $conn->query("SELECT COUNT(*) as total FROM practicas")->fetch_assoc()['total'];
$completadas = $conn->query("SELECT COUNT(*) as total FROM practicas WHERE estado='completada'")->fetch_assoc()['total'];
$pendientes = $conn->query("SELECT COUNT(*) as total FROM practicas WHERE estado='pendiente'")->fetch_assoc()['total'];

$content = "REPORTE DE PRÁCTICAS\n\n";
$content .= "Total: $total\n";
$content .= "Completadas: $completadas\n";
$content .= "Pendientes: $pendientes\n";

echo $content;