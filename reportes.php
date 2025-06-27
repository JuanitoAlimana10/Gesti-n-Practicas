
<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$nombre_docente = $_SESSION['nombre'] ?? '';

// Obtener prácticas del maestro
$sql = "
SELECT f.id, f.Nombre_Practica AS nombre, f.Fecha_Propuesta AS fecha, f.Fecha_Real AS realizada
FROM fotesh f
WHERE f.Maestro_id = ?
ORDER BY f.Fecha_Propuesta DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Contadores y clasificación
$total_practicas = 0;
$realizadas = 0;
$pendientes = 0;
$no_realizadas = 0;
$hoy = date('Y-m-d');
$practicas = [];

while ($row = $resultado->fetch_assoc()) {
    $estado = 'pendiente';
    if (!empty($row['realizada'])) {
        $estado = 'realizada';
        $realizadas++;
    } elseif ($row['fecha'] < $hoy) {
        $estado = 'no realizada';
        $no_realizadas++;
    } else {
        $pendientes++;
    }
    $total_practicas++;
    $practicas[] = array_merge($row, ['estado' => $estado]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Mis Reportes de Prácticas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Reportes del Docente</span>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><span class="nav-link text-light fw-bold"><?= htmlspecialchars($nombre_docente) ?></span></li>
                <li class="nav-item"><a class="nav-link" href="panel_docentes.php">Regresar al panel</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">Resumen de Prácticas FOTESH</h2>
    <div class="row text-center">
        <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total de Prácticas</h5>
                    <p class="card-text fs-4"><?= $total_practicas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Realizadas</h5>
                    <p class="card-text fs-4"><?= $realizadas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pendientes / No Realizadas</h5>
                    <p class="card-text fs-4"><?= $pendientes + $no_realizadas ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="mb-3">Gráfica de Estado de Prácticas</h4>
        <canvas id="graficaReportes" height="100"></canvas>
    </div>

    <h4 class="mt-5">Listado de Prácticas</h4>
    <?php if (count($practicas) > 0): ?>
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Fecha Propuesta</th>
                <th>Fecha Real</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($practicas as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['fecha']) ?></td>
                <td><?= $p['realizada'] ?? '-' ?></td>
                <td>
                    <?php if ($p['estado'] === 'realizada'): ?>
                        <span class="badge bg-success">Realizada</span>
                    <?php elseif ($p['estado'] === 'no realizada'): ?>
                        <span class="badge bg-danger">No realizada</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No hay prácticas registradas.</div>
    <?php endif; ?>
</div>

<script>
const ctx = document.getElementById('graficaReportes').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Realizada', 'Pendiente', 'No realizada'],
        datasets: [{
            label: 'Total de Reportes',
            data: [<?= $realizadas ?>, <?= $pendientes ?>, <?= $no_realizadas ?>],
            backgroundColor: ['#198754', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

</body>
</html>
