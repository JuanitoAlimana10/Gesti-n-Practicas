<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$nombre_docente = $_SESSION['nombre'] ?? '';

// Obtener prácticas directamente con su estado
$sql = "
SELECT id, Nombre_Practica AS nombre, Fecha_Propuesta AS fecha, Fecha_Real AS realizada, estado
FROM fotesh
WHERE Maestro_id = ?
ORDER BY Fecha_Propuesta DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Inicializar contadores
$total_practicas = 0;
$realizadas = 0;
$pendientes = 0;
$no_realizadas = 0;
$practicas = [];

while ($row = $resultado->fetch_assoc()) {
    $estado = strtolower($row['estado']);
    $practicas[] = $row;
    $total_practicas++;

    if ($estado === 'realizada') {
        $realizadas++;
    } elseif ($estado === 'pendiente') {
        $pendientes++;
    } elseif ($estado === 'no realizada') {
        $no_realizadas++;
    }
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
    <h2 class="mb-4 text-center">Resumen de Prácticas FOTESH</h2>

    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body bg-primary text-white rounded-3">
                    <h5 class="card-title">Total de Prácticas</h5>
                    <p class="card-text fs-2"><?= $total_practicas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body bg-success text-white rounded-3">
                    <h5 class="card-title">Realizadas</h5>
                    <p class="card-text fs-2"><?= $realizadas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body bg-warning text-dark rounded-3">
                    <h5 class="card-title">Pendientes</h5>
                    <p class="card-text fs-2"><?= $pendientes ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 offset-md-8">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body bg-danger text-white rounded-3">
                    <h5 class="card-title">No Realizadas</h5>
                    <p class="card-text fs-2"><?= $no_realizadas ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="my-5">
        <h4 class="mb-3 text-center">Gráfica de Estado de Prácticas</h4>
        <canvas id="graficaReportes" height="120"></canvas>
    </div>

    <h4 class="mt-5 mb-3">Listado de Prácticas</h4>
    <?php if (count($practicas) > 0): ?>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Nombre</th>
                    <th>Fecha Propuesta</th>
                    <th>Fecha Real</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($practicas as $p): ?>
                <tr class="text-center">
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['fecha']) ?></td>
                    <td><?= $p['realizada'] ? htmlspecialchars($p['realizada']) : '-' ?></td>
                    <td>
                        <?php if (strtolower($p['estado']) === 'realizada'): ?>
                            <span class="badge bg-success fs-6 px-3 py-2">Realizada</span>
                        <?php elseif (strtolower($p['estado']) === 'no realizada'): ?>
                            <span class="badge bg-danger fs-6 px-3 py-2">No realizada</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Pendiente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No hay prácticas registradas.</div>
    <?php endif; ?>
</div>

<script>
const ctx = document.getElementById('graficaReportes').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Realizadas', 'Pendientes', 'No realizadas'],
        datasets: [{
            label: 'Cantidad',
            data: [<?= $realizadas ?>, <?= $pendientes ?>, <?= $no_realizadas ?>],
            backgroundColor: ['#198754', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                stepSize: 1,
                precision: 0,
                ticks: {
                    precision:0
                }
            }
        }
    }
});
</script>

</body>
</html>
