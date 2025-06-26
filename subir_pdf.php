<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    echo "Acceso denegado.";
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreArchivo = $_FILES["archivo"]["name"];
    $archivoTmp = $_FILES["archivo"]["tmp_name"];
    $directorio = "PDFS/";
    $grupo = $_POST["grupo"];
    $rutaRelativa = $directorio . basename($nombreArchivo);

    $carrera = $_POST["carrera"];
    $materia = $_POST["materia"];
    $usuario_id = $_SESSION['id'];
    $fecha = date("Y-m-d H:i:s");

    // Verificamos si se subió correctamente
    if (move_uploaded_file($archivoTmp, $rutaRelativa)) {
        $sql = "INSERT INTO pdfs (nombre, ruta, fecha, carrera, materia, grupo, usuario_id, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'no realizada')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $nombreArchivo, $rutaRelativa, $fecha, $carrera, $materia, $grupo, $usuario_id);


        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Archivo subido correctamente.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error al guardar en la base de datos.</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error al mover el archivo a la carpeta PDFS.</div>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Subir documento PDF</h2>
    <form method="post" enctype="multipart/form-data" class="border p-4 rounded bg-white shadow-sm">
        <div class="mb-3">
            <label for="archivo" class="form-label">Selecciona un archivo PDF:</label>
            <input type="file" name="archivo" id="archivo" accept="application/pdf" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="carrera" class="form-label">Carrera:</label>
            <select name="carrera" id="carrera" class="form-select" required>
                <option value="">Seleccione una carrera</option>
                <option value="Ingeniería en Sistemas">Ingeniería en Sistemas</option>
                <option value="Ingeniería Civil">Ingeniería Civil</option>
                <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                <option value="Licenciatura en Administración">Licenciatura en Administración</option>
                <option value="Ingeniería en Mecatrónica">Ingeniería en Mecatrónica</option>
                <option value="Licenciatura en Biología">Licenciatura en Biología</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="materia" class="form-label">Materia:</label>
            <select name="materia" id="materia" class="form-select" required>
                <option value="">Seleccione una materia</option>
                <option value="Programación Orientada a Objetos">Programación Orientada a Objetos</option>
                <option value="Contabilidad Financiera">Contabilidad Financiera</option>
                <option value="Probabilidad y Estadística">Probabilidad y Estadística</option>
                <option value="Simulación">Simulación</option>
                <option value="Métodos Numéricos">Métodos Numéricos</option>
                <option value="Fundamentos de Bases de Datos">Fundamentos de Bases de Datos</option>
                <option value="Tópicos Avanzados de Programación">Tópicos Avanzados de Programación</option>
                <option value="Redes de Computadoras">Redes de Computadoras</option>
                <option value="Lenguajes y Autómatas I">Lenguajes y Autómatas I</option>
                <option value="Lenguajes de Interfaz">Lenguajes de Interfaz</option>
                <option value="Administración de Bases de Datos">Administración de Bases de Datos</option>
                <option value="Ingeniería en Software">Ingeniería en Software</option>
                <option value="Taller de Sistemas Operativos">Taller de Sistemas Operativos</option>
                <option value="Administración de Redes">Administración de Redes</option>
                <option value="Programación Lógica y Funcional">Programación Lógica y Funcional</option>
                <option value="Programación Web">Programación Web</option>
                <option value="Data Warehouse">Data Warehouse</option>
                <option value="Big Data y NoSQL">Big Data y NoSQL</option>
                <option value="Modelos de Optimización de Recursos">Modelos de Optimización de Recursos</option>
                <option value="Dinámica">Dinámica</option>
                <option value="Maquinaria Pesada y Movimiento de Tierra">Maquinaria Pesada y Movimiento de Tierra</option>
                <option value="Administración de la Construcción">Administración de la Construcción</option>
                <option value="Hidráulica de Canales">Hidráulica de Canales</option>
                <option value="Diseño y Construcción de Pavimentos">Diseño y Construcción de Pavimentos</option>
                <option value="Análisis Estructural">Análisis Estructural</option>
                <option value="Dibujo Industrial">Dibujo Industrial</option>
                <option value="Algoritmos y Lenguajes de Programación">Algoritmos y Lenguajes de Programación</option>
                <option value="Tecnologías de la Información Aplicadas a las Finanzas">Tecnologías de la Información Aplicadas a las Finanzas</option>
                <option value="Programación Básica">Programación Básica</option>
                <option value="Álgebra Lineal">Álgebra Lineal</option>
                <option value="Diseño de Elementos Mecánicos">Diseño de Elementos Mecánicos</option>
                <option value="Manufactura Avanzada">Manufactura Avanzada</option>
                <option value="Control">Control</option>
                <option value="Meteorología y Climatología">Meteorología y Climatología</option>
                <option value="Bioestadística">Bioestadística</option>
                <option value="Taller de Investigación I">Taller de Investigación I</option>
                <option value="Sistema de Información Geográfica y Percepción Remota">Sistema de Información Geográfica y Percepción Remota</option>
            </select>
        </div>
        <div class="mb-3">
    <label for="grupo" class="form-label">Grupo:</label>
    <input type="text" name="grupo" id="grupo" class="form-control" placeholder="Ej. 3501" required>
</div>


        <button type="submit" class="btn btn-primary">Subir PDF</button>
        <a href="panel_docente.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>