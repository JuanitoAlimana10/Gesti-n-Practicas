<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$docente_id = $_GET['docente_id'] ?? null;
$carrera_id = $_GET['carrera_id'] ?? null;
$periodo_id = $_GET['periodo_id'] ?? null;

if (!$docente_id || !$carrera_id || !$periodo_id || 
    !is_numeric($docente_id) || !is_numeric($carrera_id) || !is_numeric($periodo_id)) {
    die("Docente, carrera o periodo no especificados correctamente.");
}

// Obtener nombre del docente y nombre de la carrera a través de asignaciones
$stmt_nombre = $conn->prepare("
    SELECT DISTINCT u.nombre AS nombre_docente, c.nombre AS nombre_carrera
    FROM tipodeusuarios u
    JOIN asignaciones a ON a.maestro_id = u.id
    JOIN carreras c ON a.carrera_id = c.id
    WHERE u.id = ? AND a.carrera_id = ?
");
$stmt_nombre->bind_param("ii", $docente_id, $carrera_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

$nombre_docente = "Desconocido";
$nombre_carrera = "Sin carrera";

if ($row_nombre = $result_nombre->fetch_assoc()) {
    $nombre_docente = $row_nombre['nombre_docente'];
    $nombre_carrera = $row_nombre['nombre_carrera'] ?? "Sin carrera";
}

// Obtener PDFs del docente filtrados por carrera y periodo
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

// Obtener prácticas del docente filtradas por carrera y periodo
$sql = "
    SELECT f.id AS practica_id, f.estado, f.Fecha_Propuesta, f.Fecha_Real, p.nombre AS nombre_pdf, p.ruta
    FROM fotesh f
    JOIN asignaciones a ON a.maestro_id = f.Maestro_id AND a.materia_id = f.Materia_id
    LEFT JOIN pdfs p ON f.pdf_id = p.id
    WHERE f.Maestro_id = ? AND a.carrera_id = ? AND f.periodo_id = ?
";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}

$stmt->bind_param("iii", $docente_id, $carrera_id, $periodo_id);
$stmt->execute();
$resultado = $stmt->get_result();

$total = $resultado->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FO-TESH del Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">FO-TESH del Docente</span>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($_SESSION['nombre']) ?> |
            <a href="panel_jefe.php" class="text-white ms-3">Volver al Panel</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-3"><?= htmlspecialchars($nombre_docente) ?> <small class="text-muted">(<?= htmlspecialchars($nombre_carrera) ?>)</small></h3>

    <h4> Prácticas Registradas</h4>
    <div class="mb-4">
        <span class="badge bg-info text-dark fs-6">Total de prácticas: <?= $total ?></span>
    </div>

    <!-- Aquí aparecerán los mensajes AJAX -->
    <div id="mensaje-ajax"></div>

    <?php if ($total > 0): ?>
        <form id="form-actualizar-estado" method="post" action="actualizar_estado_practica.php">
            <input type="hidden" name="docente_id" value="<?= $docente_id ?>">
            <input type="hidden" name="carrera_id" value="<?= $carrera_id ?>">
            <input type="hidden" name="periodo_id" value="<?= $periodo_id ?>">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre del PDF</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Real</th>
                        <th>Estado</th>
                        <th>Ver</th>
                        <th>Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombre_pdf'] ?? 'Sin PDF') ?></td>
                            <td><?= htmlspecialchars($row['Fecha_Propuesta']) ?></td>
                            <td><?= htmlspecialchars($row['Fecha_Real'] ?? 'No asignada') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['estado'] ?? 'pendiente') ?></span></td>
                            <td>
                                <?php if (!empty($row['ruta'])): ?>
                                    <a href="<?= htmlspecialchars($row['ruta']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Ver PDF</a>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select name="estados[<?= $row['practica_id'] ?>]" class="form-select form-select-sm">
                                    <option value="pendiente" <?= $row['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="realizada" <?= $row['estado'] == 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="no realizada" <?= $row['estado'] == 'no realizada' ? 'selected' : '' ?>>No realizada</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="text-end mt-3">
                <button type="submit" id="btn-guardar-cambios" class="btn btn-success">Guardar Cambios</button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Este docente no tiene prácticas registradas en esta carrera y periodo.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ENVÍO POR AJAX del formulario de estado
document.getElementById('form-actualizar-estado').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    document.getElementById('btn-guardar-cambios').disabled = true;

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('btn-guardar-cambios').disabled = false;
        let div = document.getElementById('mensaje-ajax');
        div.innerHTML = `
            <div class="alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    })
    .catch(err => {
        document.getElementById('btn-guardar-cambios').disabled = false;
        let div = document.getElementById('mensaje-ajax');
        div.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error al guardar cambios.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    });
});
</script>
</body>
</html>
