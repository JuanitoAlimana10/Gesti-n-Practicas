<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id) {
    echo "Docente no especificado.";
    exit;
}

// Obtener nombre del docente y su carrera
$stmt_nombre = $conn->prepare("
    SELECT u.nombre AS nombre_docente, c.nombre AS nombre_carrera
    FROM tipodeusuarios u
    LEFT JOIN carreras c ON u.carrera_id = c.id
    WHERE u.id = ?
");
if (!$stmt_nombre) {
    die("Error al obtener el nombre del docente y carrera: " . $conn->error);
}
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
if (!$stmt) {
    die("Error en la consulta: " . $conn->error);
}
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FO-TESH del Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>FO-TESH del Docente: <?= htmlspecialchars($nombre_docente) ?></h3>
    <p><strong>Carrera:</strong> <?= htmlspecialchars($nombre_carrera) ?></p>

    <?php if ($resultado->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Archivo</th>
                    <th>Fecha de Subida</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><a href="<?= htmlspecialchars($row['ruta']) ?>" target="_blank">Ver PDF</a></td>
                        <td><?= $row['fecha'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning mt-3">No hay FO-TESH registrados.</div>
    <?php endif; ?>
</div>
</body>
</html>
