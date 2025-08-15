<?php 
session_start();
include 'conexion.php';

if (!isset($_SESSION['id'])) {
    echo "Debes iniciar sesión para ver tus prácticas.";
    exit;
}

$usuario_id = $_SESSION['id'];

// Recibir filtros de GET
$materia = $_GET['materia'] ?? null;
$grupo = $_GET['grupo'] ?? null;
$carrera = $_GET['carrera'] ?? null;

// Construir consulta y parámetros dinámicamente
$sql = "SELECT id, nombre, materia, carrera, grupo, fecha, ruta FROM pdfs WHERE usuario_id = ?";
$params = [$usuario_id];
$types = "i";

if ($materia !== null) { 
    $sql .= " AND materia = ?";
    $params[] = $materia;
    $types .= "s";
}

if ($grupo !== null) {
    $sql .= " AND grupo = ?";
    $params[] = $grupo;
    $types .= "s";
}

if ($carrera !== null) {
    $sql .= " AND carrera = ?";
    $params[] = $carrera;
    $types .= "s";
}

$sql .= " ORDER BY fecha DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

// Usar call_user_func_array para bind_param dinámico
$tmp = [];
foreach ($params as $key => $value) {
    $tmp[$key] = &$params[$key];
}

array_unshift($tmp, $types);
call_user_func_array([$stmt, 'bind_param'], $tmp);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de PDFs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Listado de PDFs Subidos</h4>
            <?php if ($materia): ?>
                <small>Materia: <strong><?= htmlspecialchars($materia) ?></strong></small><br>
            <?php endif; ?>
            <?php if ($grupo): ?>
                <small>Grupo: <strong><?= htmlspecialchars($grupo) ?></strong></small><br>
            <?php endif; ?>
            <?php if ($carrera): ?>
                <small>Carrera: <strong><?= htmlspecialchars($carrera) ?></strong></small>
            <?php endif; ?>
        </div>
        <div class="card-body">

            <?php
            if ($result->num_rows > 0) {
                echo '<table class="table table-striped table-hover">';
                echo '<thead class="table-dark"><tr>
                        <th>Nombre</th>
                        <th>Materia</th>
                        <th>Carrera</th>
                        <th>Grupo</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                        </tr></thead><tbody>';

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['nombre']) . "</td>
                            <td>" . htmlspecialchars($row['materia']) . "</td>
                            <td>" . htmlspecialchars($row['carrera']) . "</td>
                            <td>" . htmlspecialchars($row['grupo']) . "</td>
                            <td>" . htmlspecialchars($row['fecha']) . "</td>
                            <td><a href='" . htmlspecialchars($row['ruta']) . "' target='_blank' class='btn btn-sm btn-success'>Ver PDF</a></td>
                            </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<div class="alert alert-warning text-center">No hay PDFs para mostrar con esos filtros.</div>';
            }

            $stmt->close();
            ?>

        </div>
    </div>
</div>

</body>
</html>
