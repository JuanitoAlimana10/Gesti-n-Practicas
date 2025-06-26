<?php
include 'conexion.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $resultado = $conn->query("SELECT * FROM practicas WHERE id=$id");
    $practica = $resultado->fetch_assoc();
}
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $laboratorio = $_POST['laboratorio'];
    $horario = $_POST['horario'];
    
    $conn->query("UPDATE practicas SET nombre='$nombre', laboratorio='$laboratorio', horario='$horario' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?><!DOCTYPE html><html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Práctica</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3">Editar Práctica</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $practica['nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Laboratorio</label>
                <input type="text" name="laboratorio" class="form-control" value="<?php echo $practica['laboratorio']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Horario</label>
                <input type="text" name="horario" class="form-control" value="<?php echo $practica['horario']; ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>

