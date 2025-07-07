<?php
include 'autorizacion_compartida.php';

$carrera_id = $_GET['carrera_id'] ?? null;
if (!$carrera_id || !is_numeric($carrera_id)) {
    die("ID de carrera no especificado.");
}

// Obtener nombre de la carrera
$stmt1 = $conn->prepare("SELECT nombre FROM carreras WHERE id = ?");
$stmt1->bind_param("i", $carrera_id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$carrera = $result1->fetch_assoc();

if (!$carrera) {
    die("Carrera no encontrada.");
}

// Contar prácticas por estado directamente desde columna 'estado'
$query = "
    SELECT f.estado, COUNT(*) AS total
    FROM fotesh f
    JOIN asignaciones a ON f.Maestro_id = a.maestro_id
    WHERE a.carrera_id = ?
    GROUP BY f.estado
";

$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $carrera_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$datos = ["realizada" => 0, "pendiente" => 0, "no realizada" => 0];
while ($row = $result2->fetch_assoc()) {
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
    <title>Reportes Generales - <?= htmlspecialchars($carrera['nombre']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Reportes Generales de la Carrera</span>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($_SESSION['nombre']) ?> |
            <a href="<?= $_SESSION['rol'] === 'administrador' ? 'panel_admin.php' : 'panel_jefe.php' ?>" class="text-white ms-3">Volver al Panel</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="mb-4">Resumen de prácticas - <?= htmlspecialchars($carrera['nombre']) ?></h3>
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
                    <h5 class="card-title">No realizadas</h5>
                    <p class="card-text fs-4"><?= $datos['no realizada'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Gráfica de Distribución</h4>
    <canvas id="grafica" height="120"></canvas>
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
