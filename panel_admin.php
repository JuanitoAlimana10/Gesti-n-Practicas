<?php
require 'conexion.php';
require 'validacion_roles.php';
verificarPermiso('administrador');

// Obtener todas las carreras
$carreras = $conn->query("SELECT id, nombre FROM carreras");

// Obtener todos los periodos para filtro
$periodos = $conn->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC");

// Obtener periodo seleccionado (o el activo si no se selecciona)
$periodo_id = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : null;
if (!$periodo_id) {
    $activo = $conn->query("SELECT id FROM periodos WHERE activo = 1 LIMIT 1")->fetch_assoc();
    $periodo_id = $activo ? (int)$activo['id'] : null;
}

// Obtener carrera y materia seleccionadas
$carrera_id = isset($_GET['carrera_id']) ? (int)$_GET['carrera_id'] : null;
$materia_id = isset($_GET['materia_id']) ? (int)$_GET['materia_id'] : null;

$docentes = [];
$asignaciones = [];
$estadisticas = [];
$materias = [];

// Si hay carrera, traemos materias asignadas para ese periodo y carrera
if ($carrera_id) {
    $stmtMat = $conn->prepare("
        SELECT DISTINCT m.id, m.nombre 
        FROM asignaciones a
        JOIN materias m ON a.materia_id = m.id
        WHERE a.carrera_id = ?
        ORDER BY m.nombre
    ");
    $stmtMat->bind_param("i", $carrera_id);
    $stmtMat->execute();
    $materias = $stmtMat->get_result();
}

// Si hay carrera y periodo, traemos docentes, asignaciones y estadísticas filtradas
if ($carrera_id && $periodo_id) {
    // Filtrar docentes activos con asignaciones en esa carrera
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.nombre, u.email
        FROM asignaciones a
        JOIN tipodeusuarios u ON u.id = a.maestro_id
        WHERE u.rol = 'maestro' AND u.estado = 'activo' AND a.carrera_id = ?
        ORDER BY u.nombre
    ");
    $stmt->bind_param("i", $carrera_id);
    $stmt->execute();
    $docentes = $stmt->get_result();

    // Materias asignadas a docentes (filtrado por carrera)
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

    // Estadísticas de prácticas filtradas por periodo, carrera y opcional materia

    if ($materia_id) {
    $stmt3 = $conn->prepare("
        SELECT 
            u.id AS docente_id,
            u.nombre AS docente,
            COUNT(DISTINCT f.id) AS total_practicas
        FROM (
            SELECT DISTINCT u.id, u.nombre
            FROM asignaciones a
            JOIN tipodeusuarios u ON u.id = a.maestro_id
            WHERE u.rol = 'maestro' AND u.estado = 'activo' AND a.carrera_id = ?
        ) AS u
        LEFT JOIN fotesh f 
            ON f.Maestro_id = u.id 
            AND f.Materia_id = ?
            AND f.Carrera_id = ?
            AND f.periodo_id = ?
        GROUP BY u.id, u.nombre
        ORDER BY total_practicas DESC
    ");
    // Paréntesis del bind: carrera_id, materia_id, carrera_id, periodo_id
    $stmt3->bind_param("iiii", $carrera_id, $materia_id, $carrera_id, $periodo_id);
} else {
    $stmt3 = $conn->prepare("
        SELECT 
            u.id AS docente_id,
            u.nombre AS docente,
            COUNT(DISTINCT f.id) AS total_practicas
        FROM (
            SELECT DISTINCT u.id, u.nombre
            FROM asignaciones a
            JOIN tipodeusuarios u ON u.id = a.maestro_id
            WHERE u.rol = 'maestro' AND u.estado = 'activo' AND a.carrera_id = ?
        ) AS u
        LEFT JOIN fotesh f 
            ON f.Maestro_id = u.id 
            AND f.Carrera_id = ?
            AND f.periodo_id = ?
        GROUP BY u.id, u.nombre
        ORDER BY total_practicas DESC
    ");
    $stmt3->bind_param("iii", $carrera_id, $carrera_id, $periodo_id);
}
$stmt3->execute();
$estadisticas = $stmt3->get_result();


// Consulta para PDFs adaptada con LIKE para materia
$sql_pdfs = "
    SELECT 
        u.nombre AS docente,
        p.materia AS materia,
        COUNT(*) AS total_pdfs
    FROM pdfs p
    JOIN tipodeusuarios u ON p.usuario_id = u.id
    WHERE 1=1
";

if ($periodo_id > 0) $sql_pdfs .= " AND p.periodo_id = $periodo_id";
if ($carrera_id > 0) {
    // Obtenemos nombre de carrera para filtrar (porque en pdfs es texto)
    $carrera_nombre = '';
    $res_carrera = $conn->query("SELECT nombre FROM carreras WHERE id = $carrera_id");
    if ($res_carrera && $row = $res_carrera->fetch_assoc()) {
        $carrera_nombre = $conn->real_escape_string($row['nombre']);
        $sql_pdfs .= " AND p.carrera = '$carrera_nombre'";
    }
}
if ($materia_id > 0) {
    // Obtenemos nombre de materia para filtrar (porque en pdfs es texto)
    $materia_nombre = '';
    $res_materia = $conn->query("SELECT nombre FROM materias WHERE id = $materia_id");
    if ($res_materia && $row = $res_materia->fetch_assoc()) {
        $materia_nombre = $conn->real_escape_string($row['nombre']);
        // Cambiado a LIKE para evitar inconsistencias
        $sql_pdfs .= " AND p.materia LIKE '%$materia_nombre%'";
    }
}

$sql_pdfs .= "
    GROUP BY p.usuario_id, p.materia
    ORDER BY u.nombre, p.materia
";

$res_pdfs = $conn->query($sql_pdfs);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Panel Administrador</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="gestionar_usuarios.php">Aceptar usuarios</a></li>
                <li class="nav-item"><a class="nav-link" href="asigancion.php">Asignar materias</a></li>
                <li class="nav-item"><a class="nav-link" href="gestionar_periodos.php">Gestionar periodos</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <h3>Filtros</h3>
    <form method="GET" class="row g-3 mb-4">

        <div class="col-md-4">
            <label for="carrera_id" class="form-label">Carrera</label>
            <select name="carrera_id" id="carrera_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Selecciona una carrera --</option>
                <?php 
                $carreras->data_seek(0);
                while ($c = $carreras->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == $carrera_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="periodo_id" class="form-label">Periodo</label>
            <select name="periodo_id" id="periodo_id" class="form-select" onchange="this.form.submit()" <?= (!$carrera_id) ? 'disabled' : '' ?>>
                <option value="">-- Selecciona un periodo --</option>
                <?php 
                $periodos->data_seek(0);
                while ($p = $periodos->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == $periodo_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?> (<?= $p['fecha_inicio'] ?> - <?= $p['fecha_fin'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label for="materia_id" class="form-label">Materia</label>
            <select name="materia_id" id="materia_id" class="form-select" onchange="this.form.submit()" <?= (!$carrera_id) ? 'disabled' : '' ?>>
                <option value="">-- Todas las materias --</option>
                <?php if ($materias): ?>
                    <?php while ($m = $materias->fetch_assoc()): ?>
                        <option value="<?= $m['id'] ?>" <?= ($m['id'] == $materia_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
    </form>

    <?php if ($carrera_id && $periodo_id): ?>

        <!-- Docentes -->
<h4>Docentes de la carrera</h4>
<?php if ($docentes->num_rows > 0): ?>
    <table class="table table-bordered table-hover">
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
                <?php $materiaParam = $materia_id ? "&materia_id=$materia_id" : ""; ?>
                <tr>
                    <td><?= htmlspecialchars($docente['nombre']) ?></td>
                    <td><?= htmlspecialchars($docente['email']) ?></td>
                    <td>
                        <a href="ver_fotesh.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $docente['id'] ?>&periodo_id=<?= $periodo_id ?><?= $materiaParam ?>" class="btn btn-sm btn-info">
                            Ver FO-TESH
                        </a>
                    </td>
                    <td>
                        <a href="ver_reportes_docente.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $docente['id'] ?>&periodo_id=<?= $periodo_id ?><?= $materiaParam ?>" class="btn btn-sm btn-secondary">
                            Ver Reportes
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-warning">No hay docentes asignados a esta carrera.</div>
<?php endif; ?>

        <!-- Materias asignadas -->
        <h5>Materias Asignadas a Docentes</h5>
        <?php if ($asignaciones->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>Docente</th><th>Materia</th><th>Grupo</th></tr>
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
            <div class="alert alert-info">No hay materias asignadas para esta carrera.</div>
        <?php endif; ?>

        <hr>

        <!-- Estadísticas -->
        <h5>Estadísticas de Prácticas</h5>
        <?php if ($estadisticas->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Total Prácticas</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $estadisticas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['docente']) ?></td>
                            <td><?= $r['total_practicas'] ?></td>
                            <td>
                                <a href="detalle_estadisticas_docente.php?docente_id=<?= $r['docente_id'] ?>&carrera_id=<?= $carrera_id ?>&periodo_id=<?= $periodo_id ?>&materia_id=<?= $materia_id ?>" class="btn btn-sm btn-primary">Ver Detalle</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No hay estadísticas disponibles.</div>
        <?php endif; ?>

        <hr>

        <!-- Reporte General -->
        <h5>Reporte General de la Carrera</h5>
        <a href="ver_reportes_carrera.php?carrera_id=<?= $carrera_id ?>&periodo_id=<?= $periodo_id ?>&materia_id=<?= $materia_id ?>" class="btn btn-primary">
            Ver Reporte General
        </a>

        <hr>

        <!-- Reporte PDFs -->
        <h5>Reporte PDFs Generados</h5>
        <?php if ($res_pdfs && $res_pdfs->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Materia</th>
                        <th>Total PDFs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pdf = $res_pdfs->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($pdf['docente']) ?></td>
                            <td><?= htmlspecialchars($pdf['materia']) ?></td>
                            <td><?= $pdf['total_pdfs'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No hay PDFs generados para los filtros seleccionados.</div>
        <?php endif; ?>

    <?php endif; ?>
</div>
</body>
</html>
