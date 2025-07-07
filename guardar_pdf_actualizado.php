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
$docente_id = $_POST['docente_id'] ?? null;
if (!$docente_id) {
    die("Error: ID del docente no proporcionado.");
}
$carrera = $_POST['carrera'] ?? '';
$materia = $_POST['materia'] ?? '';
$grupo = $_POST['grupo'] ?? '';
$fechaEntrega = $_POST['fechaEntrega'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$practicas_json = $_POST['practicas'] ?? '[]';
$periodo = $_POST['periodo'] ?? '';
$practicas = json_decode($practicas_json, true);


// Obtener nombre del docente (si lo necesitas para carpeta)
$stmt_nombre = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_nombre->bind_param("i", $docente_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

if ($row_nombre = $result_nombre->fetch_assoc()) {
    $docente_nombre = $row_nombre['nombre'];
} else {
    die("Error: Docente no encontrado.");
}

if (!isset($_FILES['archivo'])) {
    die("Error: Archivo PDF no recibido.");
}

// Obtener el nombre del docente (opcional, para carpeta)
$stmt_nombre = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_nombre->bind_param("i", $docente_id);
$stmt_nombre->execute();
$result = $stmt_nombre->get_result();

if ($row = $result->fetch_assoc()) {
    $docente_nombre = $row['nombre'];
} else {
    die("Error: No se encontró el nombre del docente.");
}

// Crear carpeta por docente
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

// Insertar PDF
$fecha_actual = date('Y-m-d H:i:s');
$estado_inicial = 'pendiente';

$stmt_pdf = $conn->prepare("INSERT INTO pdfs (nombre, ruta, fecha, usuario_id, carrera, materia, grupo, estado, fecha_entrega) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt_pdf->bind_param("sssisssss", $nombre_archivo, $ruta_destino, $fecha_actual, $docente_id, $carrera, $materia, $grupo, $estado_inicial, $fechaEntrega);
$stmt_pdf->execute();
$pdf_id = $conn->insert_id;

// Insertar prácticas
$stmt_insert = $conn->prepare("
    INSERT INTO fotesh (
        Nombre_Practica, Objetivo, Laboratorio, Horario,
        Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio,
        Materia_id, Maestro_id, pdf_id, estado,
        carrera, materia, grupo, periodo, fecha_entrega
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$materia_id = 1;

foreach ($practicas as $p) {
    $nombre = $p['nombre'] ?? '';
    $objetivo = $p['objetivo'] ?? '';
    $laboratorio = $p['laboratorio'] ?? '';
    $horario = ($p['horaInicio'] ?? '') . ' - ' . ($p['horaFin'] ?? '');
    $fechaPropuesta = $p['fechaProgramada'] ?? '';
    $fechaRealizada = $p['fechaRealizada'] ?? '';
    $rubrica = $p['rubrica'] ?? '';

    if (!empty($fechaRealizada)) {
        $estado_practica = 'realizada';
    } elseif (!empty($fechaPropuesta) && date('Y-m-d') < $fechaPropuesta) {
        $estado_practica = 'pendiente';
    } else {
        $estado_practica = 'no realizada';
    }

    $stmt_insert->bind_param(
        "sssssssiiissssss",
        $nombre, $objetivo, $laboratorio, $horario,
        $fechaPropuesta, $fechaRealizada, $rubrica,
        $materia_id, $docente_id, $pdf_id, $estado_practica,
        $carrera, $materia, $grupo, $periodo, $fechaEntrega
    );

    $stmt_insert->execute();
}

echo "✅ Prácticas y PDF guardados correctamente en '$ruta_destino'.";
$conn->close();
?>
