<?php
require 'conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'autorizacion_compartida.php';

// ====== Validar parámetros ======
$docente_id = $_GET['docente_id'] ?? null;
$carrera_id = $_GET['carrera_id'] ?? null;
$periodo_id = $_GET['periodo_id'] ?? null;
$materia_id = $_GET['materia_id'] ?? null;

if (!$docente_id || !is_numeric($docente_id)) die("ID de docente no válido.");
if (!$carrera_id || !is_numeric($carrera_id)) die("ID de carrera no válido.");
if (!$periodo_id || !is_numeric($periodo_id)) die("ID de periodo no válido.");

// Si no hay materia_id, tomar la primera materia asignada como predeterminada
if (!$materia_id || !is_numeric($materia_id)) {
    $stmt_def = $conn->prepare("SELECT materia_id FROM asignaciones WHERE maestro_id=? AND carrera_id=? LIMIT 1");
    $stmt_def->bind_param("ii", $docente_id, $carrera_id);
    $stmt_def->execute();
    $res_def = $stmt_def->get_result();
    $row_def = $res_def->fetch_assoc();
    if (!$row_def) die("No hay materias asignadas.");
    $materia_id = $row_def['materia_id'];
}

// ====== Obtener datos de docente y materia ======
$stmt = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$res = $stmt->get_result();
$docente = $res->fetch_assoc();
if (!$docente) die("Docente no encontrado.");
$nombre_docente = $docente['nombre'];

$stmt_mat = $conn->prepare("SELECT nombre FROM materias WHERE id = ?");
$stmt_mat->bind_param("i", $materia_id);
$stmt_mat->execute();
$res_mat = $stmt_mat->get_result();
$materia = $res_mat->fetch_assoc();
$nombre_materia = $materia['nombre'] ?? 'Materia desconocida';

// ====== Contar prácticas de la materia en ese periodo ======
$datos = ["realizada" => 0, "pendiente" => 0, "no realizada" => 0];

$sql = "
    SELECT estado, COUNT(*) AS total
    FROM fotesh
    WHERE maestro_id = ? 
      AND materia_id = ?
      AND periodo_id = ?
    GROUP BY estado
";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("iii", $docente_id, $materia_id, $periodo_id);
$stmt2->execute();
$resultado = $stmt2->get_result();

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
    <title>Estadísticas - <?= htmlspecialchars($nombre_docente) ?> - <?= htmlspecialchars($nombre_materia) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Estadísticas por Materia</span>
        <div class="navbar-text text-white">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <!-- Botón para volver al Panel del Docente -->
    <a href="panel_docente.php?periodo=<?= $periodo_id ?>" class="btn btn-secondary mb-3">
        ← Volver al Panel del Docente
    </a>

    <h3 class="mb-4">Estadísticas - <?= htmlspecialchars($nombre_docente) ?> <br><small class="text-muted"><?= htmlspecialchars($nombre_materia) ?></small></h3>

    <!-- Lista de materias con botones -->
    <div class="mb-4">
        <h5>Materias asignadas:</h5>
        <div class="list-group">
            <?php
            $stmt_list = $conn->prepare("
                SELECT m.id, m.nombre
                FROM asignaciones a
                JOIN materias m ON a.materia_id = m.id
                WHERE a.maestro_id = ? AND a.carrera_id = ?
            ");
            $stmt_list->bind_param("ii", $docente_id, $carrera_id);
            $stmt_list->execute();
            $res_list = $stmt_list->get_result();
            while ($m = $res_list->fetch_assoc()):
                $url = "reporte_docente_materia.php?docente_id={$docente_id}&carrera_id={$carrera_id}&periodo_id={$periodo_id}&materia_id={$m['id']}";
            ?>
                <a href="<?= $url ?>" class="list-group-item list-group-item-action <?= ($m['id'] == $materia_id) ? 'active' : '' ?>">
                    <?= htmlspecialchars($m['nombre']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Tarjetas de datos -->
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

    <!-- Gráfica -->
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
        plugins: { legend: { display: false } }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
