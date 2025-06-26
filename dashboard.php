<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

$rol = $_SESSION['rol'] ?? 'Desconocido';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <div class="card p-4 shadow-sm">
            <h1 class="mb-3">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h1>
            <h4 class="text-muted">(<?php echo ucfirst(htmlspecialchars($rol)); ?>)</h4>

            <div class="mt-4">
                <?php if ($rol == 'maestro') { ?>
                    <a href="agregar_practica.php" class="btn btn-primary">Agregar Nueva Práctica</a>
                <?php } ?>

                <?php if ($rol == 'administrador' || $rol == 'jefe_carrera') { ?>
                    <a href="gestionar_usuarios.php" class="btn btn-warning">Gestionar Usuarios</a>
                <?php } ?>

                <a href="logout.php" class="btn btn-danger mt-3">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>

