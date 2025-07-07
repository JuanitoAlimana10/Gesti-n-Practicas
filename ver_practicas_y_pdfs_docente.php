<?php
include 'autorizacion_compartida.php';

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id) {
    die("Docente no especificado.");
}

$stmt1 = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt1->bind_param("i", $docente_id);
$stmt1->execute();
$docente = $stmt1->get_result()->fetch_assoc();

$stmt2 = $conn->prepare("SELECT id, nombre, ruta, fecha, estado FROM pdfs WHERE usuario_id = ? ORDER BY fecha DESC");
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
    <h3>PDFs Subidos por <?= htmlspecialchars($docente['nombre']) ?></h3>
    <?php if ($reportes->num_rows > 0): ?>
        <form method="POST" action="actualizar_estado_pdf.php">
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
                                <select name="estados[<?= $r['id'] ?>]" class="form-select form-select-sm">
                                    <option value="realizada" <?= $r['estado'] === 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="pendiente" <?= $r['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="no realizada" <?= $r['estado'] === 'no realizada' ? 'selected' : '' ?>>No realizada</option>
                                </select>
                            </td>
                            <td><?= $r['fecha'] ?></td>
                            <td><a href="<?= htmlspecialchars($r['ruta']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    <?php else: ?>
        <div class="alert alert-info">Este docente no ha subido reportes.</div>
    <?php endif; ?>

    <hr class="my-5">
    <h3>Prácticas Registradas por <?= htmlspecialchars($docente['nombre']) ?></h3>
    <?php
    $stmt3 = $conn->prepare("SELECT id, Nombre_Practica, Objetivo, Laboratorio, Horario, Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio, estado FROM fotesh WHERE Maestro_id = ? ORDER BY Fecha_Propuesta DESC");
    $stmt3->bind_param("i", $docente_id);
    $stmt3->execute();
    $practicas = $stmt3->get_result();
    ?>
    <?php if ($practicas->num_rows > 0): ?>
        <form method="POST" action="actualizar_estado_practica.php">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Objetivo</th>
                        <th>Laboratorio</th>
                        <th>Horario</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Real</th>
                        <th>Tipo Lab.</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $practicas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['Nombre_Practica']) ?></td>
                            <td><?= htmlspecialchars($p['Objetivo']) ?></td>
                            <td><?= htmlspecialchars($p['Laboratorio']) ?></td>
                            <td><?= htmlspecialchars($p['Horario']) ?></td>
                            <td><?= htmlspecialchars($p['Fecha_Propuesta']) ?></td>
                            <td><?= htmlspecialchars($p['Fecha_Real']) ?></td>
                            <td><?= htmlspecialchars($p['Tipo_de_Laboratorio']) ?></td>
                            <td>
                                <select name="estados[<?= $p['id'] ?>]" class="form-select form-select-sm">
                                    <option value="realizada" <?= $p['estado'] === 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="pendiente" <?= $p['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="no realizada" <?= $p['estado'] === 'no realizada' ? 'selected' : '' ?>>No realizada</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Guardar Cambios de Prácticas</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Este docente no tiene prácticas registradas.</div>
    <?php endif; ?>
</body>
</html>
