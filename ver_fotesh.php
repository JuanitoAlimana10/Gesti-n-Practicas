
<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id || !is_numeric($docente_id)) {
    die("Docente no especificado correctamente.");
}

// Obtener nombre del docente y su carrera
$stmt_nombre = $conn->prepare("
    SELECT u.nombre AS nombre_docente, c.nombre AS nombre_carrera
    FROM tipodeusuarios u
    LEFT JOIN carreras c ON u.carrera_id = c.id
    WHERE u.id = ?
");
$stmt_nombre->bind_param("i", $docente_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

$nombre_docente = "Desconocido";
$nombre_carrera = "Sin carrera";

if ($row_nombre = $result_nombre->fetch_assoc()) {
    $nombre_docente = $row_nombre['nombre_docente'];
    $nombre_carrera = $row_nombre['nombre_carrera'] ?? "Sin carrera";
}

// Obtener FO-TESH del docente
$stmt = $conn->prepare("SELECT nombre, ruta, fecha FROM pdfs WHERE usuario_id = ?");
$stmt->bind_param("i", $docente_id);
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

    <div class="mb-4">
        <span class="badge bg-info text-dark fs-6">Total de archivos FO-TESH: <?= $total ?></span>
    </div>

    <?php if ($total > 0): ?>
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Nombre del PDF</th>
                    <th>Fecha</th>
                    <th>Ver</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($row['ruta']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Ver PDF</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">Este docente no ha subido ningún archivo FO-TESH.</div>
    <?php endif; ?>
</div>
</body>
</html>
