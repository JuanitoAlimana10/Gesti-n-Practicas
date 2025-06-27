
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "gestion_practicas");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$docente_id = $_SESSION['id'];
$nombre_maestro = $_SESSION['nombre'] ?? '';

// Obtener materias asignadas al docente
$sql = "
SELECT DISTINCT 
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Panel del Docente</span>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><span class="nav-link text-light fw-bold"><?= htmlspecialchars($nombre_maestro) ?></span></li>
                <li class="nav-item"><a class="nav-link" href="reportes.php">Ver Reportes</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
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
                    <a href="formulario_pdf.php?materia=<?= urlencode($fila['materia']) ?>&grupo=<?= urlencode($fila['grupo']) ?>&carrera=<?= urlencode($fila['carrera']) ?>" class="btn btn-primary btn-sm">
                        Registrar FOTESH
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning">No tienes materias asignadas.</div>
    <?php endif; ?>
</div>

</body>
</html>
