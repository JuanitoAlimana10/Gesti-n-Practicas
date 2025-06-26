<?php
session_start();
require 'conexion.php';

if (!isset($_POST['practicas'])) {
    echo 'No se recibieron prácticas';
    exit;
}

$input = json_decode($_POST['practicas'], true);

if (!is_array($input)) {
    echo 'Error: JSON inválido';
    exit;
}

// Simula que tienes sesión activa
$maestro_id = $_SESSION['id'] ?? 1;
$materia_id = $_SESSION['materia_id'] ?? 1;

// Recorremos cada práctica y guardamos en la base de datos
foreach ($input as $p) {
    $stmt = $conn->prepare("INSERT INTO fotesh 
        (Nombre_Practica, Objetivo, Laboratorio, Horario, Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio, Materia_id, Maestro_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssii",
        $p['nombre'],
        $p['objetivo'],
        $p['laboratorio'],
        $p['horario'],
        $p['fechaProgramada'],
        $p['fechaRealizada'],
        $p['rubrica'],
        $materia_id,
        $maestro_id
    );

    $stmt->execute();
}

echo 'Datos guardados correctamente';
