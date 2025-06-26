<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "gestion_practicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del formulario
$carrera = $_POST['carrera'];
$titulo = $_POST['titulo'];
$practicas_json = $_POST['practicas'];
$practicas = json_decode($practicas_json, true);

// Obtener el archivo PDF
$archivo = $_FILES['archivo'];
$nombre_archivo = basename($archivo['name']);
$ruta_destino = 'pdf/' . $nombre_archivo;

// Guardar archivo en el servidor
move_uploaded_file($archivo['tmp_name'], $ruta_destino);

// Obtener el ID del docente desde tipodeusuarios
$docente_nombre = $_POST['docente'] ?? ''; // por si lo agregas después
$stmt_docente = $conn->prepare("SELECT id FROM tipodeusuarios WHERE nombre = ? AND rol = 'maestro'");
$stmt_docente->bind_param("s", $docente_nombre);
$stmt_docente->execute();
$result_docente = $stmt_docente->get_result();

if ($row_docente = $result_docente->fetch_assoc()) {
    $docente_id = $row_docente['id'];
} else {
    die("Error: Docente no encontrado.");
}

// Insertar cada práctica
$stmt_insert = $conn->prepare("INSERT INTO fotesh (Nombre_Practica, Objetivo, Laboratorio, Horario, Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio, Materia_id, Maestro_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($practicas as $p) {
    $nombre = $p['nombre'];
    $objetivo = $p['objetivo'];
    $laboratorio = $p['laboratorio'];
    $horario = $p['horario'];
    $fechaPropuesta = $p['fechaProgramada'];
    $fechaRealizada = $p['fechaRealizada'];
    $rubrica = $p['rubrica'];
    $materia_id = 1; // Ajusta esto si tienes mapeo de materias

    $stmt_insert->bind_param("sssssssii", $nombre, $objetivo, $laboratorio, $horario, $fechaPropuesta, $fechaRealizada, $rubrica, $materia_id, $docente_id);
    $stmt_insert->execute();
}

echo "Prácticas y PDF guardados correctamente.";
$conn->close();
?>
