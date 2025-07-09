<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "gestion_practicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$carrera = $_POST['carrera'] ?? '';
$materia_id = $_POST['materia'] ?? '';
$grupo_id = $_POST['grupo'] ?? '';
$fechaEntrega = $_POST['fechaEntrega'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$practicas_json = $_POST['practicas'] ?? '[]';
$firma_base64 = $_POST['firma'] ?? '';
$docente_id = $_POST['docente_id'] ?? null;

if (!$docente_id) die("Error: ID del docente no proporcionado.");

// Obtener nombre del docente
$stmt = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$docente_result = $stmt->get_result();
$docente_nombre = $docente_result->fetch_assoc()['nombre'] ?? 'Desconocido';

// Obtener nombres de carrera, materia y grupo
function obtenerNombre($conn, $tabla, $id) {
    $stmt = $conn->prepare("SELECT nombre FROM $tabla WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc()['nombre'] ?? 'Desconocido';
}

$carrera_nombre = obtenerNombre($conn, 'carreras', $carrera);
$materia_nombre = obtenerNombre($conn, 'materias', $materia_id);
$grupo_nombre = obtenerNombre($conn, 'grupos', $grupo_id);

// Crear carpeta PDF
$carpeta_pdf = 'FOTESH/' . $docente_nombre . '/';
if (!is_dir($carpeta_pdf)) mkdir($carpeta_pdf, 0777, true);

// Guardar archivo PDF
$archivo = $_FILES['archivo'];
$nombre_archivo = basename($archivo['name']);
$ruta_pdf = $carpeta_pdf . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_pdf)) {
    die("Error al guardar el archivo PDF.");
}

// Guardar firma si existe
$firma_ruta = '';
if (!empty($firma_base64)) {
    $firma_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $firma_base64);
    $firma_bin = base64_decode($firma_base64);

    $carpeta_firmas = 'firmas/' . $docente_nombre . '/';
    if (!is_dir($carpeta_firmas)) mkdir($carpeta_firmas, 0777, true);

    $firma_nombre = 'firma_' . time() . '.png';
    $firma_ruta = $carpeta_firmas . $firma_nombre;
    file_put_contents($firma_ruta, $firma_bin);
}

// Guardar en tabla pdfs
$fecha_actual = date('Y-m-d H:i:s');
$estado_pdf = 'pendiente';

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
    $carrera_nombre,
    $materia_nombre,
    $estado_pdf,
    $grupo_nombre
);
$stmt_pdf->execute();
$pdf_id = $conn->insert_id;

// Guardar prácticas en tabla fotesh
$practicas = json_decode($practicas_json, true);
$stmt_fotesh = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, Horario,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id, estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($practicas as $p) {
    $nombre = $p['nombre'] ?? '';
    $objetivo = $p['objetivo'] ?? '';
    $laboratorio = $p['laboratorio'] ?? '';
    $horario = ($p['horaInicio'] ?? '') . ' - ' . ($p['horaFin'] ?? '');
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaReal = $p['fechaRealizada'] ?? '';
    $rubrica = $p['rubrica'] ?? '';
    $estado = 'pendiente'; // puedes cambiarlo si lo deseas dinámico

    $stmt_fotesh->bind_param(
        "sssssssiiis",
        $nombre,
        $objetivo,
        $laboratorio,
        $horario,
        $fechaPropuesta,
        $fechaReal,
        $rubrica,
        $materia_id,
        $docente_id,
        $pdf_id,
        $estado
    );

    $stmt_fotesh->execute();
}

echo "✅ PDF y prácticas guardados correctamente.";
$conn->close();
?>
