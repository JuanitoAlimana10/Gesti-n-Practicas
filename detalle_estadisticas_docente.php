<?php
include 'autorizacion_compartida.php';

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id || !is_numeric($docente_id)) {
    die("ID de docente no válido.");
}

// Obtener nombre y carrera del docente
$stmt = $conn->prepare("SELECT nombre, carrera_id FROM tipodeusuarios WHERE id = ?");
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$res = $stmt->get_result();
$docente = $res->fetch_assoc();

if (!$docente) {
    die("Docente no encontrado.");
}

$nombre_docente = $docente['nombre'];

// Contar prácticas por estado directamente desde columna 'estado'
$stmt2 = $conn->prepare("
    SELECT estado, COUNT(*) AS total
    FROM fotesh
    WHERE maestro_id = ?
    GROUP BY estado
");
$stmt2->bind_param("i", $docente_id);
$stmt2->execute();
$resultado = $stmt2->get_result();

$datos = ["realizada" => 0, "pendiente" => 0, "no realizada" => 0];
while ($row = $resultado->fetch_assoc()) {
    $estado = strtolower($row['estado']);
    if (isset($datos[$estado])) {
        $datos[$estado] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas - <?= htmlspecialchars($nombre_docente) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Estadísticas del Docente</span>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($_SESSION['nombre']) ?> |
            <a href="<?= $_SESSION['rol'] === 'administrador' ? 'panel_admin.php' : 'panel_jefe.php' ?>" class="text-white ms-3">Volver al Panel</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Estadísticas de prácticas - <?= htmlspecialchars($nombre_docente) ?></h3>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Realizadas</h5>
                    <p class="card-text fs-4"><?= $datos['realizada'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pendientes</h5>
                    <p class="card-text fs-4"><?= $datos['pendiente'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">No Realizadas</h5>
                    <p class="card-text fs-4"><?= $datos['no realizada'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Gráfica de Distribución</h4>
    <canvas id="grafica" height="100"></canvas>
</div>

<script>
const ctx = document.getElementById('grafica').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Realizadas', 'Pendientes', 'No realizadas'],
        datasets: [{
            label: 'Total',
            data: [<?= $datos['realizada'] ?>, <?= $datos['pendiente'] ?>, <?= $datos['no realizada'] ?>],
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
