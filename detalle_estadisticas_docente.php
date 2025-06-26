<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_carrera') {
    header("Location: login.php");
    exit;
}

$docente_id = $_GET['docente_id'] ?? null;
if (!$docente_id) {
    die("ID de docente no especificado.");
}

// Obtener nombre del docente
$stmt1 = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt1->bind_param("i", $docente_id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$docente = $result1->fetch_assoc();

if (!$docente) {
    die("Docente no encontrado.");
}

// Contar reportes por estado
$stmt2 = $conn->prepare("
    SELECT estado, COUNT(*) AS total
    FROM pdfs
    WHERE usuario_id = ?
    GROUP BY estado
");
$stmt2->bind_param("i", $docente_id);
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
    <title>Detalle de Estadísticas - <?= htmlspecialchars($docente['nombre']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h3>Estadísticas de Reportes del Docente: <?= htmlspecialchars($docente['nombre']) ?></h3>
    <canvas id="grafica" width="400" height="200"></canvas>
    <a href="panel_jefe.php" class="btn btn-secondary mt-3">Volver</a>

    <script>
        const ctx = document.getElementById('grafica').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Realizada', 'Pendiente', 'No realizada'],
                datasets: [{
                    label: 'Total de Reportes',
                    data: [
                        <?= $datos['realizada'] ?>,
                        <?= $datos['pendiente'] ?>,
                        <?= $datos['no realizada'] ?>
                    ],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</body>
</html>
