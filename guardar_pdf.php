<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "gestion_practicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Para depuración
file_put_contents('debug.txt', print_r($_POST, true));

// Obtener datos POST
$carrera = $_POST['carrera'] ?? '';
$materia_nombre = $_POST['materia'] ?? '';
$grupo = $_POST['grupo'] ?? '';
$fechaEntrega = $_POST['fechaEntrega'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$practicas_json = $_POST['practicas'] ?? '[]';
$firma_base64 = $_POST['firma'] ?? '';

$practicas = json_decode($practicas_json, true);

// Validar ID del docente
$docente_id = $_POST['docente_id'] ?? null;
if (!$docente_id) {
    die("Error: ID del docente no proporcionado.");
}

// Obtener nombre del docente
$stmt_nombre = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_nombre->bind_param("i", $docente_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();
if ($row_nombre = $result_nombre->fetch_assoc()) {
    $docente_nombre = $row_nombre['nombre'];
} else {
    die("Error: No se encontró el nombre del docente.");
}

// Obtener ID de la materia desde su nombre
$stmt_materia = $conn->prepare("SELECT id FROM materias WHERE nombre = ?");
$stmt_materia->bind_param("s", $materia_nombre);
$stmt_materia->execute();
$result_materia = $stmt_materia->get_result();
if ($row_materia = $result_materia->fetch_assoc()) {
    $materia_id = $row_materia['id'];
} else {
    die("❌ Error: No se encontró la materia '$materia_nombre' en la base de datos.");
}

// Crear carpeta para el PDF
$carpeta_pdf = 'FOTESH/' . $docente_nombre . '/';
if (!is_dir($carpeta_pdf)) {
    mkdir($carpeta_pdf, 0777, true);
}

// Guardar archivo PDF
$archivo = $_FILES['archivo'];
$nombre_archivo = basename($archivo['name']);
$ruta_pdf = $carpeta_pdf . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_pdf)) {
    die("Error al guardar el archivo PDF.");
}

// Guardar firma como imagen
$firma_ruta = '';
if (!empty($firma_base64)) {
    $firma_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $firma_base64);
    $firma_bin = base64_decode($firma_base64);

    $carpeta_firmas = 'firmas/' . $docente_nombre . '/';
    if (!is_dir($carpeta_firmas)) {
        mkdir($carpeta_firmas, 0777, true);
    }

    $firma_nombre = 'firma_' . time() . '.png';
    $firma_ruta = $carpeta_firmas . $firma_nombre;

    file_put_contents($firma_ruta, $firma_bin);
}

// Insertar en tabla pdfs
$fecha_actual = date('Y-m-d H:i:s');
$estado_inicial = 'pendiente';

$stmt_pdf = $conn->prepare("
    INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, grupo, estado, fecha_entrega, firma)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt_pdf->bind_param("sssissssss", $nombre_archivo, $ruta_pdf, $fecha_actual, $docente_id, $carrera, $materia_nombre, $grupo, $estado_inicial, $fechaEntrega, $firma_ruta);
$stmt_pdf->execute();
$pdf_id = $conn->insert_id;

// Insertar prácticas individuales
$stmt_insert = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, Horario,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id, estado,
        carrera, materia, grupo, periodo, fecha_entrega,
        firma
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt_insert) {
    die("Error en prepare para fotesh: " . $conn->error);
}

// Si la materia_id no la estás sacando de otro lado, le asignamos un valor fijo o temporal
$materia_id = 1;

foreach ($practicas as $p) {
    $nombre = $p['nombre'] ?? '';
    $objetivo = $p['objetivo'] ?? '';
    $laboratorio = $p['laboratorio'] ?? '';
    $horario = ($p['horaInicio'] ?? '') . ' - ' . ($p['horaFin'] ?? '');
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaRealizada = $p['fechaRealizada'] ?? '';
    $rubrica = $p['rubrica'] ?? '';

    $estado_practica = !empty($fechaRealizada) ? 'realizada' : 
                      (!empty($fechaPropuesta) && date('Y-m-d') < $fechaPropuesta ? 'pendiente' : 'no realizada');

    $stmt_insert->bind_param("sssssssiiisssssss", 
        $nombre, $objetivo, $laboratorio, $horario,
        $fechaPropuesta, $fechaRealizada, $rubrica,
        $materia_id, $docente_id, $pdf_id, $estado_practica,
        $carrera, $materia, $grupo, $periodo, $fechaEntrega,
        $firma_ruta
    );

    if (!$stmt_insert->execute()) {
        file_put_contents('debug_error.txt', "❌ Error al insertar práctica: " . $stmt_insert->error . "\n", FILE_APPEND);
    } else {
        file_put_contents('debug_error.txt', "✅ Práctica insertada correctamente: $nombre\n", FILE_APPEND);
    }
}

$conn->close();
?>
