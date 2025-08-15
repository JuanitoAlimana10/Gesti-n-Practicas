<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "gestion_practicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario (POST)
$carrera_id     = $_POST['carrera'] ?? null;
$carrera_nombre = $_POST['carrera_nombre'] ?? '';
$materia_id     = $_POST['materia'] ?? null;
$materia_nombre = $_POST['materia_nombre'] ?? '';
$grupo_id       = $_POST['grupo'] ?? null;
$fechaEntrega   = $_POST['fechaEntrega'] ?? '';
$titulo         = $_POST['titulo'] ?? '';
$firma_base64   = $_POST['firma'] ?? '';
$docente_id     = $_POST['docente_id'] ?? null;

if (!$docente_id || !$materia_id || !$carrera_id || !$grupo_id) {
    die("❌ Error: Datos insuficientes para guardar la práctica.");
}

// Obtener el periodo activo directamente de la BD
$sqlPeriodo = "SELECT id FROM periodos WHERE activo = 1 LIMIT 1";
$resPeriodo = $conn->query($sqlPeriodo);
if ($resPeriodo && $resPeriodo->num_rows > 0) {
    $periodo_id = $resPeriodo->fetch_assoc()['id'];
} else {
    die("❌ No hay un periodo activo configurado.");
}

// Obtener nombre del docente
$stmt = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$docente_result = $stmt->get_result();
$docente_nombre = $docente_result->fetch_assoc()['nombre'] ?? 'Desconocido';

// Crear carpetas para PDFs y firmas
$carpeta_pdf = 'FOTESH/' . $docente_nombre . '/';
if (!is_dir($carpeta_pdf)) mkdir($carpeta_pdf, 0777, true);

$carpeta_firmas = 'firmas/' . $docente_nombre . '/';
if (!is_dir($carpeta_firmas)) mkdir($carpeta_firmas, 0777, true);

// Guardar archivo PDF
if (!isset($_FILES['archivo'])) {
    die("No se recibió archivo PDF.");
}
$archivo = $_FILES['archivo'];
$nombre_archivo_original = pathinfo($archivo['name'], PATHINFO_FILENAME);
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

$nombre_archivo = $nombre_archivo_original . '.' . $extension;
$ruta_pdf = $carpeta_pdf . $nombre_archivo;

// Evitar sobrescribir archivos
$contador = 1;
while (file_exists($ruta_pdf)) {
    $nombre_archivo = $nombre_archivo_original . '_' . $contador . '.' . $extension;
    $ruta_pdf = $carpeta_pdf . $nombre_archivo;
    $contador++;
}

if (!move_uploaded_file($archivo['tmp_name'], $ruta_pdf)) {
    die("Error al guardar el archivo PDF.");
}

// Guardar firma (si existe)
$firma_ruta = '';
if (!empty($firma_base64)) {
    $firma_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $firma_base64);
    $firma_bin = base64_decode($firma_base64);

    $firma_nombre = 'firma_' . time() . '.png';
    $firma_ruta = $carpeta_firmas . $firma_nombre;
    file_put_contents($firma_ruta, $firma_bin);
}

// Guardar registro en tabla pdfs
$fecha_actual = date('Y-m-d H:i:s');
$estado_pdf = 'pendiente';

$stmt_pdf = $conn->prepare("
    INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, estado, grupo, periodo_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt_pdf) {
    die("Error en prepare pdfs: " . $conn->error);
}
$stmt_pdf->bind_param(
    "sssissssi",
    $nombre_archivo,
    $ruta_pdf,
    $fecha_actual,
    $docente_id,
    $carrera_nombre,
    $materia_nombre,
    $estado_pdf,
    $grupo_id,
    $periodo_id
);
$stmt_pdf->execute();
$pdf_id = $conn->insert_id;

// Preparar insertar prácticas
$stmt_fotesh = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, hora_inicio, hora_fin,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id, estado,
        Carrera_id, Grupo_id, periodo_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt_fotesh) {
    die("❌ Error en prepare de fotesh: " . $conn->error);
}

$practicas_json = $_POST['practicas'] ?? '[]';
$practicas = json_decode($practicas_json, true);
if (!is_array($practicas)) {
    die("Error: Datos de prácticas no válidos.");
}

// Eliminar duplicados
$practicas = array_map('unserialize', array_unique(array_map('serialize', $practicas)));

foreach ($practicas as $p) {
    if (empty($p['nombre']) || empty($p['objetivo']) || empty($p['laboratorio'])) continue;

    $nombre         = $p['nombre'];
    $objetivo       = $p['objetivo'];
    $laboratorio    = $p['laboratorio'];
    $hora_inicio    = !empty($p['horaInicio']) ? $p['horaInicio'] : null;
    $hora_fin       = !empty($p['horaFin']) ? $p['horaFin'] : null;
    $fechaPropuesta = !empty($p['fechaProgramada']) ? $p['fechaProgramada'] : null;
    $fechaReal      = !empty($p['fechaRealizada']) ? $p['fechaRealizada'] : null;
    $rubrica        = $p['rubrica'] ?? '';
    $estado         = 'Pendiente';

    // Asegurar que valores nulos se pasen como NULL
    $hora_inicio = $hora_inicio ?: null;
    $hora_fin = $hora_fin ?: null;
    $fechaPropuesta = $fechaPropuesta ?: null;
    $fechaReal = $fechaReal ?: null;

    $stmt_fotesh->bind_param(
        "ssssssssiiisiii",
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
        $estado,
        $carrera_id,
        $grupo_id,
        $periodo_id
    );

    $stmt_fotesh->execute();
}

echo "✅ PDF y prácticas guardados correctamente.";

$conn->close();
?>
