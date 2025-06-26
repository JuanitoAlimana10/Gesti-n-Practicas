<?php
include 'conexion.php'; // Cxbase de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $objetivo = $_POST['objetivo'];
    $laboratorio = $_POST['laboratorio'];
    $horario = $_POST['horario'];
    $fechas = $_POST['fechas'];
    $tipo = $_POST['tipo'];
    $materia = $_POST['materia'];
    $maestro = $_POST['maestro'];
    $grupo = $_POST['grupo']; 
    $sql = "INSERT INTO practicas (Nombre, Objetivo, Laboratorio, Horario, Fechas, Tipo_laboratorio, Materia, Maestro, Grupo) 
            VALUES ('$nombre', '$objetivo', '$laboratorio', '$horario', '$fechas', '$tipo', '$materia', '$maestro', '$grupo')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Práctica agregada exitosamente'); window.location='index.php';</script>";
    } else {
        echo "Error al agregar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Práctica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
    <div class="container mt-5">
        <h2 class="text-center">Agregar Nueva Práctica</h2>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Laboratorio:</label>
                <input type="text" name="laboratorio" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Horario:</label>
                <input type="text" name="horario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Fechas:</label>
                <input type="text" name="fechas" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo:</label>
                <input type="text" name="tipo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Materia:</label>
                <input type="text" name="materia" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Maestro:</label>
                <input type="text" name="maestro" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Grupo:</label> 
                <input type="text" name="grupo" class="form-control" required>
            </div>
            <label class="form-label">Carrera:</label> 
            <select class="form-control" id="carrera" name="carrera" required>
            <disabled value="ELIGA CARRERA.">ELIGA CARRERA</disabled>
                <option value="Ingeniería en Sistemas Computacionales">Ingeniería en Sistemas Computacionales</option>
                <option value="Ingeniería Civil">Ingeniería Civil</option>
                <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                <option value="Ingeniería Mecatrónica">Ingeniería Mecatrónica</option>
                <option value="Licenciatura en Administración">Licenciatura en Administración</option>
                <option value="Gastronomía">Gastronomía</option>
                <option value="Biologia">Biologia</option>
                <div class="mb-3">
                </select>
                <div class="mb-3">
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success w-100">Agregar Práctica</button>
                    </div>
                    <div class="mb-3">
                    <center><button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                    <div class="mb-3">
                    <center><a href="index.php" class="btn btn-secondary">Volver</a> 
                    </div>
                    <div class="mb-3">

    </div>
</body>
</html>