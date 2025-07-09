
<?php
require 'conexion.php';
require 'validacion_roles.php';
verificarPermiso('jefe_carrera');


$carrera_id = $_SESSION['carrera_id'] ?? null;
$nombre_jefe = $_SESSION['nombre'] ?? 'Jefe de Carrera';
if (!$carrera_id) {
    die("No tienes carrera asignada.");
}

// Obtener nombre de la carrera
$nombre_carrera = 'Sin definir';
$stmtC = $conn->prepare("SELECT nombre FROM carreras WHERE id = ?");
$stmtC->bind_param("i", $carrera_id);
$stmtC->execute();
$result = $stmtC->get_result();
if ($row = $result->fetch_assoc()) {
    $nombre_carrera = $row['nombre'];
}

// Obtener docentes
$stmt1 = $conn->prepare("SELECT id, nombre, email FROM tipodeusuarios WHERE rol = 'maestro' AND estado = 'activo' AND carrera_id = ?");
$stmt1->bind_param("i", $carrera_id);
$stmt1->execute();
$docentes = $stmt1->get_result();

// Obtener materias asignadas
$stmt2 = $conn->prepare("
    SELECT u.nombre AS docente, m.nombre AS materia, g.nombre AS grupo
    FROM asignaciones a
    JOIN tipodeusuarios u ON a.maestro_id = u.id
    JOIN materias m ON a.materia_id = m.id
    JOIN grupos g ON a.grupo_id = g.id
    WHERE a.carrera_id = ?
    ORDER BY u.nombre, m.nombre
");
$stmt2->bind_param("i", $carrera_id);
$stmt2->execute();
$asignaciones = $stmt2->get_result();

// Obtener estadísticas
$stmt3 = $conn->prepare("
    SELECT u.id as docente_id, u.nombre as docente, COUNT(f.id) as total_practicas
    FROM tipodeusuarios u
    LEFT JOIN fotesh f ON u.id = f.maestro_id
    WHERE u.rol = 'maestro' AND u.estado = 'activo' AND u.carrera_id = ?
    GROUP BY u.id, u.nombre
    ORDER BY total_practicas DESC
");
$stmt3->bind_param("i", $carrera_id);
$stmt3->execute();
$estadisticas = $stmt3->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Jefe de Carrera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Panel Jefe de Carrera</span>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($nombre_jefe) ?> - <?= htmlspecialchars($nombre_carrera) ?> |
            <a href="logout.php" class="text-white ms-2">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Docentes de tu carrera</div>
        <div class="card-body">
            <?php if ($docentes->num_rows > 0): ?>
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($docente = $docentes->fetch_assoc()): ?>
                            <tr>
                                <td><?= $docente['id'] ?></td>
                                <td><?= htmlspecialchars($docente['nombre']) ?></td>
                                <td><?= htmlspecialchars($docente['email']) ?></td>
                                <td>
                                    <a href="ver_fotesh.php?docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-info">FO-TESH</a>
                                    <a href="ver_reportes_docente.php?docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-secondary">Reportes</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No hay docentes activos asignados.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Materias Asignadas a los Docentes</div>
        <div class="card-body">
            <?php if ($asignaciones->num_rows > 0): ?>
                <table class="table table-bordered table-sm">
                    <thead class="table-secondary">
                        <tr>
                            <th>Docente</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $asignaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['docente']) ?></td>
                                <td><?= htmlspecialchars($row['materia']) ?></td>
                                <td><?= htmlspecialchars($row['grupo']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">No hay materias asignadas registradas.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Estadísticas de Prácticas por Docente</div>
        <div class="card-body">
            <?php if ($estadisticas->num_rows > 0): ?>
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Total de Prácticas Realizadas</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $estadisticas->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['docente']) ?></td>
                                <td><span class="badge bg-success"><?= $row['total_practicas'] ?></span></td>
                                <td><a href="detalle_estadisticas_docente.php?docente_id=<?= $row['docente_id'] ?>" class="btn btn-sm btn-primary">Ver Detalle</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No se encontraron estadísticas de prácticas.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-end">
        <a href="ver_reportes_carrera.php?carrera_id=<?= $carrera_id ?>" class="btn btn-primary">Ver Reportes Generales</a>
    </div>
</div>
</body>
</html>
