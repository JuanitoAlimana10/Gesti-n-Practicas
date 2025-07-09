<?php
// Iniciar sesion
session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Lax'
]);

/**
 * Verifica que el usuario esté logueado y tenga el rol exacto requerido.
 * Si no es así, redirige a login o a una página de acceso denegado.
 */
function verificarPermiso($rolRequerido) {
    if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
        header("Location: login.php");
        exit;
    }

    if ($_SESSION['rol'] !== $rolRequerido) {
        header("Location: acceso_denegado.php");
        exit;
    }
}

/**
 * Verifica que el usuario tenga el rol requerido o sea administrador.
 * Ideal para roles con jerarquía donde el admin puede ver todo.
 */
function verificarAcceso($rolRequerido) {
    if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
        header("Location: login.php");
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol !== $rolRequerido && $rol !== 'administrador') {
        header("Location: acceso_denegado.php");
        exit;
    }
}
?>
