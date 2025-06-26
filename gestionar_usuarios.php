<?php
session_start();
include 'conexion.php';

echo "<pre>Rol en sesión: ";
print_r($_SESSION['rol']);
echo "</pre>";



$estado = $_GET['estado'] ?? 'pendientes';
$mensaje = $_GET['mensaje'] ?? '';

switch ($estado) {
    case 'aceptados':
        $filtro = "WHERE estado = 'activo'";
        break;
    case 'rechazados':
        $filtro = "WHERE estado = 'rechazado'";
        break;
    case 'todos':
        $filtro = "";
        break;
    default:
        $filtro = "WHERE estado = 'pendiente'"; // pendientes
        break;
}

$result = $conn->query("SELECT id, nombre, email, rol, estado FROM tipodeusuarios $filtro");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 30px; }
        .container { background: white; padding: 20px; border-radius: 10px; }
        .nav-link.active { font-weight: bold; color: #0d6efd !important; }
        form { display: inline-block; margin-bottom: 5px; }
        select { margin-right: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Gestión de Usuarios</h2>

    <!-- Tabs para filtro -->
    <ul class="nav nav-tabs mb-4 justify-content-center">
        <li class="nav-item"><a class="nav-link <?= $estado == 'pendientes' ? 'active' : '' ?>" href="?estado=pendientes">Pendientes</a></li>
        <li class="nav-item"><a class="nav-link <?= $estado == 'aceptados' ? 'active' : '' ?>" href="?estado=aceptados">Aceptados</a></li>
        <li class="nav-item"><a class="nav-link <?= $estado == 'rechazados' ? 'active' : '' ?>" href="?estado=rechazados">Rechazados</a></li>
        <li class="nav-item"><a class="nav-link <?= $estado == 'todos' ? 'active' : '' ?>" href="?estado=todos">Todos</a></li>
    </ul>

    <!-- Mensaje -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Tabla de usuarios -->
    <?php if ($result->num_rows === 0): ?>
        <p class="text-center">No hay usuarios en esta categoría.</p>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= ucfirst($u['rol']) ?></td>
                        <td>
                            <?php
                                echo match ($u['estado']) {
                                    'pendiente' => 'Pendiente',
                                    'activo' => 'Aceptado',
                                    'rechazado' => 'Rechazado',
                                    default => 'Desconocido'
                                };
                            ?>
                        </td>
                        <td>
                            <!-- Aceptar con selección de rol solo si está pendiente -->
                            <?php if ($u['estado'] == 'pendiente'): ?>
                                <form method="POST" action="acciones_usuarios.php" class="d-inline">
                                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="accion" value="aceptar">
                                    <select name="nuevo_rol" class="form-select form-select-sm d-inline w-auto" required>
                                        <option value="">Seleccionar rol</option>
                                        <option value="maestro">Maestro</option>
                                        <option value="administrador">Administrador</option>
                                        <option value="jefe_carrera">Jefe de Carrera</option>
                                    </select>
                                    <button class="btn btn-success btn-sm">Aceptar</button>
                                </form>

                                <!-- Rechazar -->
                                <form method="POST" action="acciones_usuarios.php" class="d-inline">
                                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="accion" value="rechazar">
                                    <button class="btn btn-danger btn-sm">Rechazar</button>
                                </form>
                            <?php endif; ?>

                            <!-- Eliminar, disponible siempre -->
                            <form method="POST" action="acciones_usuarios.php" class="d-inline">
                                <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button class="btn btn-warning btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="panel_admin.php" class="btn btn-secondary">Regresar al Panel Principal</a>
    </div>
</div>
</body>
</html>
