<?php
include 'conexion.php';
$mensaje = '';
$correo = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['email'];
    $nueva = $_POST['nueva'];
    $confirmar = $_POST['confirmar'];

    if ($nueva !== $confirmar) {
        $mensaje = "❌ Las contraseñas no coinciden.";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "❌ La contraseña debe tener al menos 6 caracteres.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM tipodeusuarios WHERE email = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();

            $hashed = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE tipodeusuarios SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $correo);

            if ($stmt->execute()) {
                $mensaje = "✅ Contraseña actualizada correctamente. <a href='login.php'>Iniciar sesión</a>";
            } else {
                $mensaje = "❌ Error al actualizar la contraseña.";
            }
        } else {
            $mensaje = "❌ El correo no está registrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Establecer Nueva Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Nueva Contraseña</h2>
    <?php if ($mensaje): ?>
        <div class="alert alert-info text-center"><?= $mensaje ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($correo) ?>">
        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" name="nueva" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar Contraseña</label>
            <input type="password" name="confirmar" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Actualizar Contraseña</button>
    </form>
</div>
</body>
</html>
