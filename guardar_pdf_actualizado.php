<?php 
$host = "localhost";
$user = "root";
$pass = "";
$db   = "gestion_practicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$docente_id         = $_POST['docente_id'] ?? null;
$carrera            = $_POST['carrera_nombre'] ?? 'Desconocida';
$materia            = $_POST['materia_nombre'] ?? 'Desconocida';
$grupo              = $_POST['grupo_nombre'] ?? 'Desconocido';
$fechaEntrega       = $_POST['fechaEntrega'] ?? '';
$periodo_id         = $_POST['periodo_id'] ?? null;
$practicas_json     = $_POST['practicas'] ?? '[]';
$practicas          = json_decode($practicas_json, true);
$pdf_original_id    = $_POST['pdf_original_id'] ?? null;
$actualizado_admin  = isset($_POST['actualizado_admin']) ? (int)$_POST['actualizado_admin'] : 0;

if (!$docente_id) die("Error: ID del docente no proporcionado.");

// Obtener nombre del docente
$stmt_nombre = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_nombre->bind_param("i", $docente_id);
$stmt_nombre->execute();
$result = $stmt_nombre->get_result();
$docente_nombre = $result->fetch_assoc()['nombre'] ?? 'Docente';

// Validar archivo
if (!isset($_FILES['archivo'])) {
    die("Error: Archivo PDF no recibido.");
}

// Crear carpeta del docente
$carpeta = 'FOTESH/' . $docente_nombre . '/';
if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

// Asegurar nombre único
$archivo         = $_FILES['archivo'];
$nombre_original = pathinfo($archivo['name'], PATHINFO_FILENAME);
$extension       = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$timestamp       = date('Ymd_His');
$nombre_archivo  = $nombre_original . "_" . $timestamp . "." . $extension;
$ruta_destino    = $carpeta . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    die("Error al guardar el archivo PDF.");
}

// Insertar en tabla `pdfs`
$fecha_actual = date('Y-m-d H:i:s');
$estado_pdf   = 'pendiente';

if ($pdf_original_id && $actualizado_admin === 1) {
    // Guardar como PDF editado por admin
    $stmt_pdf = $conn->prepare("
        INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, grupo, estado, actualizado_admin, pdf_original_id, periodo_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
    ");
    $stmt_pdf->bind_param("sssissssii", 
        $nombre_archivo, $ruta_destino, $fecha_actual,
        $docente_id, $carrera, $materia, $grupo, $estado_pdf,
        $pdf_original_id, $periodo_id
    );
} else {
    // Guardar PDF normal
    $stmt_pdf = $conn->prepare("
        INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, grupo, estado, periodo_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_pdf->bind_param("sssissssi", 
        $nombre_archivo, $ruta_destino, $fecha_actual,
        $docente_id, $carrera, $materia, $grupo, $estado_pdf, $periodo_id
    );
}

$stmt_pdf->execute();
$pdf_id = $conn->insert_id;

// Insertar prácticas
$stmt_insert = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio,
        hora_inicio, hora_fin,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id, estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$materia_id = 0; // Aquí ajusta si usas IDs de materia reales

foreach ($practicas as $p) {
    if (empty($p['nombre'])) continue;

    $nombre         = $p['nombre'] ?? '';
    $objetivo       = $p['objetivo'] ?? '';
    $laboratorio    = $p['laboratorio'] ?? '';
    $hora_inicio    = $p['horaInicio'] ?? '';
    $hora_fin       = $p['horaFin'] ?? '';
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaReal      = $p['fechaRealizada'] ?? '';
    $rubrica        = $p['rubrica'] ?? '';

    if (!empty($fechaReal)) {
        $estado = 'realizada';
    } elseif (!empty($fechaPropuesta) && date('Y-m-d') < $fechaPropuesta) {
        $estado = 'pendiente';
    } else {
        $estado = 'no realizada';
    }

    $stmt_insert->bind_param(
        "sssssssiiiss",
        $nombre,
        $objetivo,
        $laboratorio,
        $hora_inicio,
        $hora_fin,
        $fechaPropuesta,
        $fechaReal,
        $rubrica,
        $materia_id,
        $docente_id,
        $pdf_id,
        $estado
    );

    $stmt_insert->execute();
}

echo "✅ PDF y prácticas guardados correctamente.";
$conn->close();
?>
