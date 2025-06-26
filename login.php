<?php
include 'conexion.php';
session_start();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar correo institucional
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@huixquilucan\.tecnm\.mx$/', $correo)) {
        $mensaje = "Solo se permiten correos institucionales (@huixquilucan.tecnm.mx)";
    } else {
        // Buscar usuario en la base de datos (solo si está activo)
$stmt = $conn->prepare("SELECT id, nombre, password, rol, carrera_id FROM tipodeusuarios WHERE email = ? AND estado = 'activo'");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
$stmt->bind_result($id, $nombre, $hashed_password, $rol, $carrera_id);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // 🔁 CAMBIO: guardamos el id como 'id'
                $_SESSION['id'] = $id;  
$_SESSION['nombre'] = $nombre;
$_SESSION['rol'] = $rol;
$_SESSION['carrera_id'] = $carrera_id;


                // Redirigir según el rol
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
                        $mensaje = "Rol no válido.";
                }
                exit;
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
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Iniciar Sesión</h2>

        <?php if ($mensaje): ?>
            <div class="alert alert-danger"><?= $mensaje ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Institucional</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
           <button type="submit" class="btn btn-primary">Ingresar</button>

<a href="registro.php" class="btn btn-secondary ms-2">Registrarse</a>

<p class="mt-3">
    <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
</p>


        </form>
    </div>
</body>
</html>
