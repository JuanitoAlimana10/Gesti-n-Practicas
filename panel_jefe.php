<?php
include 'conexion.php';
session_start();

// Verificar sesión y rol
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['rol'] !== 'jefe_carrera') {
    die("Acceso denegado. Solo para jefes de carrera");
}

$carrera_id = $_SESSION['carrera_id'] ?? null;
if (!$carrera_id) {
    die("No tienes carrera asignada.");
}

// Obtener docentes de la carrera
$stmt1 = $conn->prepare("SELECT id, nombre, email FROM tipodeusuarios WHERE rol = 'maestro' AND estado = 'activo' AND carrera_id = ?");
$stmt1->bind_param("i", $carrera_id);
$stmt1->execute();
$docentes = $stmt1->get_result();

// Obtener materias asignadas a docentes de la carrera
$stmt2 = $conn->prepare("
    SELECT 
        u.nombre AS docente,
        m.nombre AS materia,
        g.nombre AS grupo
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

// Obtener estadísticas de prácticas por docente
$stmt3 = $conn->prepare("
    SELECT 
        u.id as docente_id,
        u.nombre as docente,
        COUNT(f.id) as total_practicas
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
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Panel Jefe de Carrera</a>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($_SESSION['nombre']) ?> | 
            <a href="logout.php" class="text-white">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Docentes de tu Carrera</h3>
    <?php if ($docentes->num_rows > 0): ?>
        <table class="table table-striped">
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
                            <a href="ver_fotesh.php?docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-info">Ver FO-TESH</a>
                            <a href="ver_reportes_docente.php?docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-secondary">Ver Reportes</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No hay docentes activos asignados a tu carrera.</div>
    <?php endif; ?>

    <hr>
    <h4>Materias Asignadas a los Docentes</h4>
    <?php if ($asignaciones->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
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
        <div class="alert alert-info">No hay materias asignadas.</div>
    <?php endif; ?>

    <hr>
    <h4>Estadísticas de Prácticas por Docente</h4>
    <?php if ($estadisticas->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Total de Prácticas Realizadas</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $estadisticas->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['docente']) ?></td>
                        <td><?= $row['total_practicas'] ?></td>
                        <td>
                            <a href="detalle_estadisticas_docente.php?docente_id=<?= $row['docente_id'] ?>" class="btn btn-sm btn-primary">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No se encontraron estadísticas de prácticas.</div>
    <?php endif; ?>

    <hr>
    <h4>Reportes Generales de la Carrera</h4>
    <a href="ver_reportes_carrera.php?carrera_id=<?= $carrera_id ?>" class="btn btn-primary">Ver Reportes Generales</a>
</div>
</body>
</html>
