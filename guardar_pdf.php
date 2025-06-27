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
$carrera = $_POST['carrera'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$practicas_json = $_POST['practicas'] ?? '[]';
$docente_nombre = $_POST['docente'] ?? '';

$practicas = json_decode($practicas_json, true);

// Validar datos esenciales
if (empty($docente_nombre)) {
    die("Error: Nombre del docente no proporcionado.");
}
if (!isset($_FILES['archivo'])) {
    die("Error: Archivo PDF no recibido.");
}

// Obtener el ID del docente desde tipodeusuarios
$stmt_docente = $conn->prepare("SELECT id FROM tipodeusuarios WHERE nombre = ? AND rol = 'maestro'");
$stmt_docente->bind_param("s", $docente_nombre);
$stmt_docente->execute();
$result_docente = $stmt_docente->get_result();

if ($row_docente = $result_docente->fetch_assoc()) {
    $docente_id = $row_docente['id'];
} else {
    die("Error: Docente no encontrado.");
}

// Crear carpeta por docente si no existe
$carpeta_destino = 'FOTESH/' . $docente_nombre . '/';
if (!is_dir($carpeta_destino)) {
    mkdir($carpeta_destino, 0777, true);
}

// Guardar archivo PDF
$archivo = $_FILES['archivo'];
$nombre_archivo = basename($archivo['name']);
$ruta_destino = $carpeta_destino . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    die("Error al guardar el archivo PDF.");
}

// Insertar cada práctica en la tabla `fotesh`
$stmt_insert = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, Horario,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$materia_id = 1; // Puedes ajustar esto si se obtiene dinámicamente

foreach ($practicas as $p) {
    $nombre = $p['nombre'] ?? '';
    $objetivo = $p['objetivo'] ?? '';
    $laboratorio = $p['laboratorio'] ?? '';
    $horario = $p['horario'] ?? '';
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaRealizada = $p['fechaRealizada'] ?? '';
    $rubrica = $p['rubrica'] ?? '';

    $stmt_insert->bind_param("sssssssii", $nombre, $objetivo, $laboratorio, $horario, $fechaPropuesta, $fechaRealizada, $rubrica, $materia_id, $docente_id);
    $stmt_insert->execute();
}

echo "Prácticas y PDF guardados correctamente en '$ruta_destino'.";
$conn->close();
?>
