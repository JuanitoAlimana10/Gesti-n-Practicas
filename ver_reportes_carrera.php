<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_carrera') {
    header("Location: login.php");
    exit;
}

$carrera_id = $_GET['carrera_id'] ?? null;
if (!$carrera_id) {
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

// Contar reportes por estado de la carrera
$stmt2 = $conn->prepare("
    SELECT estado, COUNT(*) AS total
    FROM pdfs
    WHERE carrera = ?
    GROUP BY estado
");
$carrera_nombre = $carrera['nombre'];
$stmt2->bind_param("s", $carrera_nombre);
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
<body class="container mt-4">
    <h3>Reportes Generales de la Carrera: <?= htmlspecialchars($carrera['nombre']) ?></h3>
    <canvas id="grafica" width="400" height="200"></canvas>
    <a href="panel_jefe.php" class="btn btn-secondary mt-3">Volver</a>

    <script>
        const ctx = document.getElementById('grafica').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Realizada', 'Pendiente', 'No realizada'],
                datasets: [{
                    label: 'Reportes',
                    data: [
                        <?= $datos['realizada'] ?>,
                        <?= $datos['pendiente'] ?>,
                        <?= $datos['no realizada'] ?>
                    ],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
