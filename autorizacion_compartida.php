
<?php
include 'conexion.php';
session_start();

// Aceptar tanto jefe_carrera como administrador
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_carrera', 'administrador'])) {
    header("Location: login.php");
    exit;
}

$carrera_id = null;

// Si es jefe de carrera, usamos su carrera_id de sesión
if ($_SESSION['rol'] === 'jefe_carrera') {
    $carrera_id = $_SESSION['carrera_id'] ?? null;
}

// Si es administrador y se pasa carrera_id por GET
if ($_SESSION['rol'] === 'administrador' && isset($_GET['carrera_id'])) {
    $carrera_id = (int)$_GET['carrera_id'];
}

if (!$carrera_id) {
    die("No se pudo determinar la carrera.");
}
?>
