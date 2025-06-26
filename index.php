<?php
include 'conexion.php';
$result = $conn->query("SELECT * FROM practicas");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        .btn-sm {
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Gestión de Prácticas</h2>
        <div class="text-end mb-3">
            <a href="agregar_practica.php" class="btn btn-success">➕ Agregar Práctica</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($practica = $result->fetch_assoc()) { ?>
                        <tr>
                            <td class="text-center"><?php echo $practica['id']; ?></td>
                            <td><?php echo $practica['nombre']; ?></td>
                            <td class="text-center">
                                <a href="editar_practica.php?id=<?php echo $practica['id']; ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                                <a href="eliminar_practica.php?id=<?php echo $practica['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?');">🗑️ Eliminar</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>