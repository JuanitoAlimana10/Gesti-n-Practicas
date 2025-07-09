<?php
include 'conexion.php';

// Seguridad avanzada de sesión
session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Lax'
]);

$mensaje = '';

// Función para redirigir por rol
function redirigirPorRol($rol) {
    switch ($rol) {
        case 'administrador':
            header("Location: panel_admin.php");
            break;
        case 'maestro':
            header("Location: panel_docentes.php");
            break;
        case 'jefe_carrera':
            header("Location: panel_jefe.php");
            break;
        default:
            die("Rol no reconocido.");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar correo institucional
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@huixquilucan\\.tecnm\\.mx$/', $correo)) {
        $mensaje = "Solo se permiten correos institucionales (@huixquilucan.tecnm.mx)";
    } else {
        $stmt = $conn->prepare("SELECT id, nombre, password, rol, carrera_id FROM tipodeusuarios WHERE email = ? AND estado = 'activo'");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nombre, $hashed_password, $rol, $carrera_id);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = $rol;
                $_SESSION['carrera_id'] = $carrera_id;

                redirigirPorRol($rol);
            } else {
                $mensaje = "Contraseña incorrecta.";
            }
        } else {
            $mensaje = "Usuario no encontrado o pendiente de aprobación.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Gestión de Prácticas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>

        <?php if ($mensaje): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Correo institucional</label>
                <input type="email" name="email" class="form-control" required placeholder="usuario@huixquilucan.tecnm.mx">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="********">
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary">Ingresar</button>
                <a href="registro.php" class="btn btn-secondary">Registrarse</a>
            </div>
            <div class="mt-3 text-center">
                <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</body>
</html>
