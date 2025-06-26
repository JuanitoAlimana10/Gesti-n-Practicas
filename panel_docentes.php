<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    echo "Acceso denegado.";
    exit;
}

$conexion = new mysqli("localhost", "root", "", "gestion_practicas");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$docente_id = $_SESSION['id'];
$nombre_maestro = $_SESSION['nombre'] ?? '';

// Consulta para obtener las materias del docente
$sql = "
SELECT 
    m.nombre AS materia, 
    c.nombre AS carrera,
    g.nombre AS grupo
FROM asignaciones a
JOIN materias m ON a.materia_id = m.id
JOIN carreras c ON a.carrera_id = c.id
JOIN grupos g ON a.grupo_id = g.id
WHERE a.maestro_id = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Consulta los estados de reportes del docente
$datos = ["realizada" => 0, "pendiente" => 0, "no realizada" => 0];
$sql_estadisticas = "
    SELECT estado, COUNT(*) AS total
    FROM pdfs
    WHERE usuario_id = ?
    GROUP BY estado
";
$stmt2 = $conexion->prepare($sql_estadisticas);
$stmt2->bind_param("i", $docente_id);
$stmt2->execute();
$res_estadisticas = $stmt2->get_result();
while ($row = $res_estadisticas->fetch_assoc()) {
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
    <title>Panel de Maestro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Panel del Docente</span>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-light fw-bold">
                        <?= htmlspecialchars($_SESSION['nombre']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Perfil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes.php">Estadísticas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="formulario_pdf.php">Registrar FOTESH</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Cerrar sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4">Materias asignadas</h3>

    <?php if ($resultado->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Carrera</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['materia']) ?></td>
                        <td><?= htmlspecialchars($fila['grupo']) ?></td>
                        <td><?= htmlspecialchars($fila['carrera']) ?></td>
                        <td>
                            <a href="formulario_pdf.php?carrera=<?= urlencode($fila['carrera']) ?>&materia=<?= urlencode($fila['materia']) ?>&grupo=<?= urlencode($fila['grupo']) ?>&docente=<?= urlencode($nombre_maestro) ?>" 
                               class="btn btn-sm btn-primary">
                                Registrar FOTESH
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No tienes materias asignadas actualmente.</div>
    <?php endif; ?>

    <h4 class="mt-5">Estadísticas de tus reportes FOTESH</h4>
    <canvas id="grafica" width="400" height="200"></canvas>
</div>

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
