<?php 
include 'autorizacion_compartida.php';

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id || !is_numeric($docente_id)) {
    die("Docente no especificado.");
}

// Obtener nombre del docente
$stmt = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$docente = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes del Docente <?= htmlspecialchars($docente['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

    <h3 class="mb-4">📄 PDFs Subidos por <?= htmlspecialchars($docente['nombre']) ?></h3>
    <?php
    $stmt2 = $conn->prepare("SELECT nombre, ruta, fecha FROM pdfs WHERE usuario_id = ? ORDER BY fecha DESC");
    $stmt2->bind_param("i", $docente_id);
    $stmt2->execute();
    $reportes = $stmt2->get_result();
    ?>

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
                        <td>
                            <a href="<?= htmlspecialchars($r['ruta']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Este docente no ha subido PDFs.</div>
    <?php endif; ?>

    <hr class="my-5">

    <h3 class="mb-4">🧪 Prácticas Registradas por <?= htmlspecialchars($docente['nombre']) ?></h3>
    <?php
    $stmt3 = $conn->prepare("
        SELECT id, Nombre_Practica, Objetivo, Laboratorio, Horario, 
               Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio
        FROM fotesh 
        WHERE Maestro_id = ? 
        ORDER BY Fecha_Propuesta DESC
    ");
    $stmt3->bind_param("i", $docente_id);
    $stmt3->execute();
    $practicas = $stmt3->get_result();
    ?>

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
                    <th>Editar</th>
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
                        <td>
                            <a href="editar_practica.php?id=<?= $p['id'] ?>&maestro_id=<?= $docente_id ?>" class="btn btn-sm btn-warning">Editar</a>
                        </td>
                        <td>
                            <?php
                                if (!empty($p['Fecha_Real'])) {
                                    echo '<span class="badge bg-success">Realizada</span>';
                                } elseif (!empty($p['Fecha_Propuesta']) && date('Y-m-d') < $p['Fecha_Propuesta']) {
                                    echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                                } else {
                                    echo '<span class="badge bg-danger">No realizada</span>';
                                }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">Este docente no tiene prácticas registradas.</div>
    <?php endif; ?>

</body>
</html>  
