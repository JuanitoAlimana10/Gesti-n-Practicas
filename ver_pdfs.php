<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['id'])) {
    echo "Debes iniciar sesión para ver tus prácticas.";
    exit;
}

$usuario_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Listado de Prácticas Subidas</h4>
        </div>
        <div class="card-body">

            <?php
            // Mostrar mensaje si viene de una eliminación exitosa
            if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'eliminado') {
                echo '<div class="alert alert-success text-center">La práctica fue eliminada correctamente.</div>';
            }

            $sql = "SELECT * FROM pdfs WHERE usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $usuario_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<table class="table table-striped table-hover">';
                    echo '<thead class="table-dark"><tr>
                            <th>Nombre</th>
                            <th>Materia</th>
                            <th>Carrera</th>
                            <th>Grupo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acción</th>
                          </tr></thead><tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['nombre']}</td>
                                <td>{$row['materia']}</td>
                                <td>{$row['carrera']}</td>
                                <td>{$row['grupo']}</td>
                                <td>{$row['fecha']}</td>
                                <td>{$row['estado']}</td>
                                <td>
                                    <a href='{$row['ruta']}' target='_blank' class='btn btn-sm btn-success mb-1'>Ver PDF</a>
                                    <form method='POST' action='eliminar_practicaa.php' class='d-inline' onsubmit='return confirm(\"¿Estás seguro de eliminar esta práctica?\");'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <input type='hidden' name='ruta' value='{$row['ruta']}'>
                                        <button type='submit' class='btn btn-sm btn-danger'>Eliminar</button>
                                    </form>
                                </td>
                              </tr>";
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert alert-warning text-center">No hay documentos subidos todavía.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Error al ejecutar la consulta.</div>';
            }

            $stmt->close();
            ?>

        </div>
    </div>
</div>

</body>
</html>
