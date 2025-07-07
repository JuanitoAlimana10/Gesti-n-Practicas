
<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

// Obtener todas las carreras
$carreras = $conn->query("SELECT id, nombre FROM carreras");

$carrera_id = isset($_GET['carrera_id']) ? (int)$_GET['carrera_id'] : null;
$docentes = [];
$asignaciones = [];
$estadisticas = [];

if ($carrera_id) {
    // Obtener docentes
$stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.nombre, u.email
    FROM tipodeusuarios u
    JOIN asignaciones a ON u.id = a.maestro_id
    WHERE u.rol = 'maestro' AND u.estado = 'activo' AND a.carrera_id = ?
");
    $stmt->bind_param("i", $carrera_id);
    $stmt->execute();
    $docentes = $stmt->get_result();

    // Materias asignadas
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

    // Estadísticas
    $stmt3 = $conn->prepare("
    SELECT u.id as docente_id, u.nombre as docente, COUNT(f.id) as total_practicas
    FROM tipodeusuarios u
    JOIN asignaciones a ON u.id = a.maestro_id
    LEFT JOIN fotesh f ON u.id = f.maestro_id
    WHERE u.rol = 'maestro' AND u.estado = 'activo' AND a.carrera_id = ?
    GROUP BY u.id, u.nombre
    ORDER BY total_practicas DESC
");

    $stmt3->bind_param("i", $carrera_id);
    $stmt3->execute();
    $estadisticas = $stmt3->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Panel Administrador</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="gestionar_usuarios.php">Aceptar usuarios</a></li>
                <li class="nav-item"><a class="nav-link" href="agregar_practica.php">Agregar práctica</a></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="ver_pdfs.php">Ver PDFs</a></li>
                <li class="nav-item"><a class="nav-link" href="asigancion.php">Asigna materias a docentes</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Seleccionar Carrera</h3>
    <form method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6">
                <select name="carrera_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Selecciona una carrera --</option>
                    <?php while ($c = $carreras->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $carrera_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($carrera_id): ?>
        <h4>Docentes de la carrera</h4>
        <?php if ($docentes->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>FO-TESH</th>
                        <th>Reportes Individuales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($docente = $docentes->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($docente['nombre']) ?></td>
                            <td><?= htmlspecialchars($docente['email']) ?></td>
                            <td><a href="ver_fotesh.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-info">Ver FO-TESH</a></td>
                            <td><a href="ver_reportes_docente.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $docente['id'] ?>" class="btn btn-sm btn-secondary">Ver Reportes</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No hay docentes asignados a esta carrera.</div>
        <?php endif; ?>

        <hr>
        <h5>Materias Asignadas a Docentes</h5>
        <?php if ($asignaciones->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead><tr><th>Docente</th><th>Materia</th><th>Grupo</th></tr></thead>
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
            <div class="alert alert-info">No hay materias asignadas para esta carrera.</div>
        <?php endif; ?>

        <hr>
        <h5>Estadísticas de Prácticas</h5>
        <?php if ($estadisticas->num_rows > 0): ?>
            <table class="table table-striped">
                <thead><tr><th>Docente</th><th>Total Prácticas</th><th>Detalle</th></tr></thead>
                <tbody>
                    <?php while ($r = $estadisticas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['docente']) ?></td>
                            <td><?= $r['total_practic   as'] ?></td>
                            <td><a href="detalle_estadisticas_docente.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $r['docente_id'] ?>" class="btn btn-sm btn-primary">Ver Detalle</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No hay estadísticas disponibles.</div>
        <?php endif; ?>

        <hr>
        <h5>Reporte General de la Carrera</h5>
        <a href="ver_reportes_carrera.php?carrera_id=<?= $carrera_id ?>" class="btn btn-primary">Ver Reporte General</a>
    <?php endif; ?>
</div>
</body>
</html>
