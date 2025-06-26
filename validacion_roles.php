<?php
session_start();

function verificarPermiso($rolRequerido) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $rol = $_SESSION['rol'];

    if ($rol !== $rolRequerido) {
        echo "Acceso denegado.";
        exit();
    }
}

function verificarAcceso($rolRequerido) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $rol = $_SESSION['rol'];
    
    if ($rol !== $rolRequerido && $rol !== 'administrador') {
        echo "Acceso denegado.";
        exit();
    }
}
?>