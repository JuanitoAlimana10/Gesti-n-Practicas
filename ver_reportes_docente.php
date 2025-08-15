<?php
// ---------------------------
// ver_reportes_docente.php
// ---------------------------

// Iniciar sesión de forma segura y compatible con localhost
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_secure' => false, // localhost no usa HTTPS
        'cookie_samesite' => 'Lax'
    ]);
}

// Conexión a la base de datos
require 'conexion.php';

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['rol'] !== 'administrador') {
    header("Location: acceso_denegado.php");
    exit;
}

// Obtener parámetros
$docente_id  = $_GET['docente_id'] ?? null;
$carrera_id  = $_GET['carrera_id'] ?? null;
$periodo_id  = $_GET['periodo_id'] ?? null;

if (!$periodo_id || !is_numeric($periodo_id)) die("Periodo no especificado correctamente.");
if (!$docente_id || !$carrera_id || !is_numeric($docente_id) || !is_numeric($carrera_id)) {
    die("Docente o carrera no especificados correctamente.");
}

// Obtener nombre del docente y de la carrera
$stmt = $conn->prepare("
    SELECT DISTINCT u.nombre AS docente, c.nombre AS carrera
    FROM tipodeusuarios u
    JOIN asignaciones a ON a.maestro_id = u.id
    JOIN carreras c ON a.carrera_id = c.id
    WHERE u.id = ? AND c.id = ?
");
$stmt->bind_param("ii", $docente_id, $carrera_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$nombre_docente = $info['docente'] ?? "Desconocido";
$nombre_carrera = $info['carrera'] ?? "Sin carrera";

// Determinar si el usuario es admin (para mostrar botones de editar)
$es_admin = $_SESSION['rol'] === 'administrador';

// ---------------------------
// PDFs Subidos
$stmt2 = $conn->prepare("
    SELECT nombre, ruta, fecha 
    FROM pdfs 
    WHERE usuario_id = ? 
    AND carrera = (SELECT nombre FROM carreras WHERE id = ?) 
    AND periodo_id = ?
    ORDER BY fecha DESC
");
$stmt2->bind_param("iii", $docente_id, $carrera_id, $periodo_id);
$stmt2->execute();
$reportes = $stmt2->get_result();

// ---------------------------
// Prácticas Registradas
$stmt3 = $conn->prepare("
    SELECT DISTINCT f.id, f.Nombre_Practica, f.Objetivo, f.Laboratorio, 
           f.hora_inicio, f.hora_fin, f.Fecha_Propuesta, f.Fecha_Real, f.Tipo_de_Laboratorio
    FROM fotesh f
    WHERE f.Maestro_id = ? 
      AND f.Materia_id IN (
          SELECT materia_id FROM asignaciones WHERE carrera_id = ? AND maestro_id = ?
      )
      AND f.periodo_id = ?
    ORDER BY f.Fecha_Propuesta DESC
");
$stmt3->bind_param("iiii", $docente_id, $carrera_id, $docente_id, $periodo_id);
$stmt3->execute();
$practicas = $stmt3->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes del Docente <?= htmlspecialchars($nombre_docente) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h3 class="mb-4">📄 PDFs Subidos por <?= htmlspecialchars($nombre_docente) ?> 
    <small class="text-muted">(<?= htmlspecialchars($nombre_carrera) ?>)</small>
</h3>

<?php if ($reportes->num_rows > 0): ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nombre del PDF</th>
            <th>Fecha</th>
            <th>Ver</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($r = $reportes->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($r['nombre']) ?></td>
            <td><?= $r['fecha'] ?></td>
            <td><a href="<?= htmlspecialchars($r['ruta']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<div class="alert alert-info">Este docente no ha subido PDFs para esta carrera.</div>
<?php endif; ?>

<hr class="my-5">

<h3 class="mb-4">🧪 Prácticas Registradas por <?= htmlspecialchars($nombre_docente) ?></h3>

<?php if ($practicas->num_rows > 0): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Objetivo</th>
            <th>Laboratorio</th>
            <th>Horario</th>
            <th>Fecha Propuesta</th>
            <th>Fecha Real</th>
            <?php if ($es_admin): ?><th>Editar</th><?php endif; ?>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($p = $practicas->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($p['Nombre_Practica']) ?></td>
            <td><?= htmlspecialchars($p['Objetivo']) ?></td>
            <td><?= htmlspecialchars($p['Laboratorio']) ?></td>
            <td><?= htmlspecialchars(date('H:i', strtotime($p['hora_inicio']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($p['hora_fin']))) ?></td>
            <td><?= htmlspecialchars($p['Fecha_Propuesta']) ?></td>
            <td><?= htmlspecialchars($p['Fecha_Real']) ?></td>
            <?php if ($es_admin): ?>
            <td><a href="editar_practica.php?id=<?= $p['id'] ?>&maestro_id=<?= $docente_id ?>" class="btn btn-sm btn-warning">Editar</a></td>
            <?php endif; ?>
            <td>
                <?php
                if (!empty($p['Fecha_Real'])) echo '<span class="badge bg-success">Realizada</span>';
                elseif (!empty($p['Fecha_Propuesta']) && date('Y-m-d') < $p['Fecha_Propuesta']) echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                else echo '<span class="badge bg-danger">No realizada</span>';
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<div class="alert alert-warning">Este docente no tiene prácticas registradas en esta carrera.</div>
<?php endif; ?>

</body>
</html>
