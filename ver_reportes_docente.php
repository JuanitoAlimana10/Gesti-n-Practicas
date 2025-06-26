<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_carrera') {
    header("Location: login.php");
    exit;
}

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id) {
    die("Docente no especificado.");
}

// Obtener nombre del docente
$stmt1 = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt1->bind_param("i", $docente_id);
$stmt1->execute();
$docente = $stmt1->get_result()->fetch_assoc();

// Obtener PDFs del docente
$stmt2 = $conn->prepare("
    SELECT id, nombre, ruta, fecha, estado 
    FROM pdfs 
    WHERE usuario_id = ? 
    ORDER BY fecha DESC
");
$stmt2->bind_param("i", $docente_id);
$stmt2->execute();
$reportes = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de <?= htmlspecialchars($docente['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h3>Prácticas Subidas por <?= htmlspecialchars($docente['nombre']) ?></h3>
    <?php if ($reportes->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre del PDF</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Ver</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $reportes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nombre']) ?></td>
                        <td>
                            <form method="POST" action="actualizar_estado_pdf.php" class="d-flex">
                                <input type="hidden" name="pdf_id" value="<?= $r['id'] ?>">
                                <select name="estado" class="form-select form-select-sm me-1" onchange="this.form.submit()">
                                    <option value="realizada" <?= $r['estado'] === 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="pendiente" <?= $r['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="no realizada" <?= $r['estado'] === 'no realizada' ? 'selected' : '' ?>>No realizada</option>
                                </select>
                            </form>
                        </td>
                        <td><?= $r['fecha'] ?></td>
                        <td><a href="<?= htmlspecialchars($r['ruta']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Este docente no ha subido reportes.</div>
    <?php endif; ?>
</body>
</html>
