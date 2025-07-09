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

// Obtener datos POST (nombre y id, ambos)
$carrera = $_POST['carrera'] ?? '';
$carrera_nombre = $_POST['carrera_nombre'] ?? '';
$materia_id = $_POST['materia'] ?? '';
$materia_nombre = $_POST['materia_nombre'] ?? '';
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

// Validar que $materia_id existe en la tabla materias
$stmt_check = $conn->prepare("SELECT id, nombre FROM materias WHERE id = ?");
$stmt_check->bind_param("i", $materia_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($row_materia = $res_check->fetch_assoc()) {
    // Usar el nombre de la base por si hay diferencia
    $materia_nombre_db = $row_materia['nombre'];
} else {
    die("Error: El ID de materia '$materia_id' no existe en la tabla materias.");
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

// OBTENER NOMBRE DE LA CARRERA DESDE SU ID si no viene del form
if (empty($carrera_nombre)) {
    $stmt_carrera = $conn->prepare("SELECT nombre FROM carreras WHERE id = ?");
    $stmt_carrera->bind_param("i", $carrera);
    $stmt_carrera->execute();
    $result_carrera = $stmt_carrera->get_result();
    if ($row_carrera = $result_carrera->fetch_assoc()) {
        $carrera_nombre = $row_carrera['nombre'];
    } else {
        $carrera_nombre = $carrera;
    }
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

// Insertar en tabla pdfs (con nombre legible)
$fecha_actual = date('Y-m-d H:i:s');
$estado_inicial = 'pendiente';

$stmt_pdf = $conn->prepare("
    INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, estado, grupo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt_pdf->bind_param(
    "sssissss",
    $nombre_archivo,
    $ruta_pdf,
    $fecha_actual,
    $docente_id,
    $carrera_nombre,   // NOMBRE de la carrera
    $materia_nombre_db,   // NOMBRE desde la base, siempre correcto
    $estado_inicial,
    $grupo
);
$stmt_pdf->execute();
$pdf_id = $conn->insert_id; // ID del PDF recién creado

// INSERTAR EN TABLA fotesh (CON pdf_id, y usando SIEMPRE el ID de materia)
$stmt_insert = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, Horario,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt_insert) {
    die("Error en prepare para fotesh: " . $conn->error);
}

foreach ($practicas as $p) {
    $nombre = $p['nombre'] ?? '';
    $objetivo = $p['objetivo'] ?? '';
    $laboratorio = $p['laboratorio'] ?? '';
    $horario = ($p['horaInicio'] ?? '') . ' - ' . ($p['horaFin'] ?? '');
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaRealizada = $p['fechaRealizada'] ?? '';
    $rubrica = $p['rubrica'] ?? '';

    $stmt_insert->bind_param(
        "sssssssiii",
        $nombre,         // Nombre_Practica
        $objetivo,       // Objetivo
        $laboratorio,    // Laboratorio
        $horario,        // Horario
        $fechaPropuesta, // Fecha_Propuesta
        $fechaRealizada, // Fecha_Real
        $rubrica,        // Tipo_de_Laboratorio
        $materia_id,     // ID para fotesh
        $docente_id,     // Maestro_id
        $pdf_id          // pdf_id
    );

    if (!$stmt_insert->execute()) {
        file_put_contents('debug_error.txt', "❌ Error al insertar práctica: " . $stmt_insert->error . "\n", FILE_APPEND);
    } else {
        file_put_contents('debug_error.txt', "✅ Práctica insertada correctamente: $nombre\n", FILE_APPEND);
    }
}

$conn->close();
?>
