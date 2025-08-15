<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_secure' => false,
        'cookie_samesite' => 'Lax'
    ]);
}

// Verificar que el usuario tenga rol válido
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_carrera', 'administrador', 'maestro'])) {
    header("Location: login.php");
    exit;
}

// Inicializamos $carrera_id en null
$carrera_id = null;

// Si es jefe de carrera, usamos su carrera_id de sesión
if ($_SESSION['rol'] === 'jefe_carrera') {
    $carrera_id = $_SESSION['carrera_id'] ?? null;
}

// Si es administrador o maestro y se pasa por GET
if (isset($_GET['carrera_id'])) {
    $carrera_id = (int)$_GET['carrera_id'];
}

// Nota: Si es maestro y no se pasa carrera_id, permitimos que sea null
// Para scripts donde se obtiene carrera directamente desde la DB, no se bloqueará
