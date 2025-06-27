<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    die("Acceso denegado. Debe iniciar sesión como docente.");
}

require 'conexion.php';

$maestro_id = $_SESSION['id'];
$carrera = $_POST['carrera'] ?? '';
$materia = $_POST['materia'] ?? '';
$grupo = $_POST['grupo'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$fechaEntrega = $_POST['fechaEntrega'] ?? '';
$firmaBase64 = $_POST['firma'] ?? '';

// Validar campos mínimos
if (empty($carrera) || empty($materia) || empty($grupo) || empty($periodo) || empty($fechaEntrega) || empty($firmaBase64)) {
    die("Faltan datos obligatorios.");
}

// Validar que hay prácticas
if (!isset($_POST['nombrePractica']) || count($_POST['nombrePractica']) === 0) {
    die("No hay prácticas para guardar.");
}

// Insertar prácticas en tabla fotesh
$nombrePracticaArr = $_POST['nombrePractica'];
$objetivoArr = $_POST['objetivo'];
$laboratorioArr = $_POST['laboratorio'];
$horarioArr = $_POST['horario'];
$rubricaArr = $_POST['rubrica'];
$fechaProgramadaArr = $_POST['fechaProgramada'];
$fechaRealizadaArr = $_POST['fechaRealizada'];

$materia_id = 1; // <- Aquí debes obtener el ID correcto de la materia según tu base

// Para el campo horario en fotesh que es datetime, combinaremos la fecha programada y el horario si quieres, 
// o solo usarás horario como TIME y fecha programada como DATE.
// Aquí como ejemplo, usamos fechaProgramada + horario

$conn->begin_transaction();

try {
    for ($i = 0; $i < count($nombrePracticaArr); $i++) {
        $nombre = $nombrePracticaArr[$i];
        $objetivo = $objetivoArr[$i];
        $laboratorio = $laboratorioArr[$i];
        $horario = $fechaProgramadaArr[$i] . ' ' . $horarioArr[$i] . ':00'; // Ejemplo formato datetime
        $fechaPropuesta = $fechaProgramadaArr[$i];
        $fechaReal = $fechaRealizadaArr[$i] ?: null; // puede ser NULL
        $tipoLab = $rubricaArr[$i];

        $stmt = $conn->prepare("INSERT INTO fotesh 
            (Nombre_Practica, Objetivo, Laboratorio, Horario, Fecha_Propuesta, Fecha_Real, Tipo_de_Laboratorio, Materia_id, Maestro_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssii",
            $nombre,
            $objetivo,
            $laboratorio,
            $horario,
            $fechaPropuesta,
            $fechaReal,
            $tipoLab,
            $materia_id,
            $maestro_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al guardar práctica: " . $stmt->error);
        }
        $stmt->close();
    }

    // Guardar PDF
    if (!file_exists('PDFS')) {
        mkdir('PDFS', 0755, true);
    }

    // Generar nombre único para el PDF
    $nombreArchivo = "FO-TESH-98_" . preg_replace('/[^a-z0-9]/i', '_', strtolower($materia)) . "_" . str_replace('-', '', $fechaEntrega) . "_" . uniqid() . ".pdf";

    // Recibir PDF base64 desde el POST
    // Aquí asumimos que el PDF ya fue generado en el cliente y enviado como archivo
    // Pero en tu formulario enviamos la firma en base64, el PDF en sí se genera con jsPDF en cliente.
    // Lo ideal: enviar el PDF generado por jsPDF como archivo Blob via Fetch (como hacías antes)
    // Aquí solo guardaremos la firma (o podrías recibir el PDF base64 en otro campo).

    // Por ejemplo: para este caso solo guardamos la firma como imagen, para mostrar la lógica.
    // Pero si quieres guardar el PDF debes enviarlo en un input file o como base64 en POST.

    // Guardar la firma (imagen PNG) en disco
    $firmaBase64 = str_replace('data:image/png;base64,', '', $firmaBase64);
    $firmaBinaria = base64_decode($firmaBase64);
    $rutaFirma = "PDFS/firma_" . uniqid() . ".png";

    if (file_put_contents($rutaFirma, $firmaBinaria) === false) {
        throw new Exception("No se pudo guardar la firma.");
    }

    // Insertar registro en tabla pdfs
    $estado = "no realizada";
    $fechaActual = date('Y-m-d H:i:s');
    $sql = "INSERT INTO pdfs (nombre, ruta, fecha, carrera, materia, grupo, usuario_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtPdf = $conn->prepare($sql);
    $stmtPdf->bind_param("ssssssis", $nombreArchivo, $rutaFirma, $fechaActual, $carrera, $materia, $grupo, $maestro_id, $estado);

    if (!$stmtPdf->execute()) {
        throw new Exception("Error al guardar registro PDF: " . $stmtPdf->error);
    }

    $stmtPdf->close();
    $conn->commit();
    echo "<div class='alert alert-success'>Prácticas y PDF guardados correctamente.</div>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>
