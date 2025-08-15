<?php
require 'conexion.php';
require 'validacion_roles.php';
verificarPermiso('administrador');

// Manejar creación de nuevo periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Crear nuevo periodo
    if (isset($_POST['crear_periodo'])) {
        $nombre = trim($_POST['nombre_periodo'] ?? '');
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? '';

        if ($nombre && $fecha_inicio && $fecha_fin) {
            // Desactivar periodo activo
            $conn->query("UPDATE periodos SET activo = 0 WHERE activo = 1");

            $stmtInsert = $conn->prepare("
                INSERT INTO periodos (nombre, fecha_inicio, fecha_fin, activo) 
                VALUES (?, ?, ?, 1)
            ");
            $stmtInsert->bind_param("sss", $nombre, $fecha_inicio, $fecha_fin);

            if ($stmtInsert->execute()) {
                $_SESSION['mensaje'] = "Nuevo periodo creado y activado correctamente.";
            } else {
                $_SESSION['mensaje'] = "Error al crear el periodo.";
            }
            $stmtInsert->close();
        } else {
            $_SESSION['mensaje'] = "Por favor, completa todos los campos.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Activar periodo existente
    if (isset($_POST['activar_periodo'])) {
        $periodo_id = (int)$_POST['periodo_id_activar'];

        // Desactivar periodo activo
        $conn->query("UPDATE periodos SET activo = 0 WHERE activo = 1");

        // Activar seleccionado
        $stmtAct = $conn->prepare("UPDATE periodos SET activo = 1 WHERE id = ?");
        $stmtAct->bind_param("i", $periodo_id);
        $stmtAct->execute();
        $stmtAct->close();

        $_SESSION['mensaje'] = "Periodo activado correctamente. Ahora puedes ver las asignaciones, prácticas y PDFs de este periodo.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Eliminar periodo (solo si no es activo)
    if (isset($_POST['eliminar_periodo'])) {
        $periodo_eliminar = (int)$_POST['periodo_id_eliminar'];

        $res = $conn->query("SELECT activo FROM periodos WHERE id = $periodo_eliminar");
        $periodo = $res->fetch_assoc();

        if ($periodo && !$periodo['activo']) {
            $stmtDel = $conn->prepare("DELETE FROM periodos WHERE id = ?");
            $stmtDel->bind_param("i", $periodo_eliminar);
            if ($stmtDel->execute()) {
                $_SESSION['mensaje'] = "Periodo eliminado correctamente.";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el periodo.";
            }
            $stmtDel->close();
        } else {
            $_SESSION['mensaje'] = "No se puede eliminar el periodo activo.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Obtener todos los periodos
$periodos = $conn->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC");

// Obtener periodo activo para filtrar asignaciones, prácticas y PDFs
$resActivo = $conn->query("SELECT id FROM periodos WHERE activo = 1 LIMIT 1");
$periodo_activo = $resActivo->fetch_assoc();
$periodo_id_activo = $periodo_activo ? $periodo_activo['id'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Periodos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">

    <h3>Gestionar Periodos Escolares</h3>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <!-- Crear nuevo periodo -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="nombre_periodo" class="form-label">Nombre del Periodo</label>
            <input type="text" name="nombre_periodo" id="nombre_periodo" class="form-control" required placeholder="Ej: Febrero - Julio 2025">
        </div>
        <div class="col-md-3">
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="crear_periodo" class="btn btn-success w-100">
                Crear
            </button>
        </div>
    </form>

    <!-- Tabla de periodos -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $periodos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_inicio']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_fin']) ?></td>
                    <td><?= $p['activo'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                    <td>
                        <?php if (!$p['activo']): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="periodo_id_activar" value="<?= $p['id'] ?>">
                                <button type="submit" name="activar_periodo" class="btn btn-sm btn-primary">Activar</button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este periodo?');">
                                <input type="hidden" name="periodo_id_eliminar" value="<?= $p['id'] ?>">
                                <button type="submit" name="eliminar_periodo" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">No se puede eliminar</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="panel_admin.php" class="btn btn-secondary mt-3">Volver al Panel</a>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
