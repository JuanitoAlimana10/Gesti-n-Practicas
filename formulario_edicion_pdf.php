<?php
include 'autorizacion_compartida.php';
require 'conexion.php';

// Validar parámetros
$id = $_GET['id'] ?? null;
$maestro_id = $_GET['maestro_id'] ?? null;
$carrera_id = $_GET['carrera_id'] ?? null;
$periodo_id = $_GET['periodo_id'] ?? null;

if (!$id || !$maestro_id || !$carrera_id || !$periodo_id) {
    die("Faltan parámetros necesarios.");
}

// Validar acceso
if ($_SESSION['rol'] === 'maestro' && $_SESSION['id'] != $maestro_id) {
    die("No tienes permiso para editar estas prácticas.");
}

// Obtener nombre de carrera
$stmt_carrera = $conn->prepare("SELECT nombre FROM carreras WHERE id = ?");
$stmt_carrera->bind_param("i", $carrera_id);
$stmt_carrera->execute();
$carrera_nombre = $stmt_carrera->get_result()->fetch_assoc()['nombre'] ?? "Desconocida";

// Obtener todas las prácticas del PDF
$stmt = $conn->prepare("SELECT * FROM fotesh WHERE pdf_id = (SELECT pdf_id FROM fotesh WHERE id = ?) AND Maestro_id = ?");
$stmt->bind_param("ii", $id, $maestro_id);
$stmt->execute();
$resultado = $stmt->get_result();
$practicas = [];
while ($row = $resultado->fetch_assoc()) {
    $practicas[] = $row;
}
if (empty($practicas)) die("No se encontraron prácticas para este PDF.");

// Encabezado del PDF
$stmt_encabezado = $conn->prepare("SELECT materia, grupo, fecha FROM pdfs WHERE id = ?");
$stmt_encabezado->bind_param("i", $practicas[0]['pdf_id']);
$stmt_encabezado->execute();
$encabezado = $stmt_encabezado->get_result()->fetch_assoc();
if (!$encabezado) die("No se encontraron datos de encabezado para el PDF.");

// Nombre del docente
$stmt_docente = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_docente->bind_param("i", $maestro_id);
$stmt_docente->execute();
$docente = $stmt_docente->get_result()->fetch_assoc()['nombre'] ?? "Desconocido";

$es_admin = $_SESSION['rol'] === 'administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Prácticas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5 p-4 bg-white shadow rounded">
  <h2 class="mb-4 text-primary">Editar Prácticas de: <?= htmlspecialchars($docente) ?></h2>
  <p><strong>Carrera:</strong> <?= htmlspecialchars($carrera_nombre) ?></p>
  <input type="hidden" id="carrera_nombre" value="<?= htmlspecialchars($carrera_nombre) ?>">
  <p><strong>Asignatura:</strong> <?= $encabezado['materia'] ?: '-' ?></p>
  <input type="hidden" id="materia_nombre" value="<?= htmlspecialchars($encabezado['materia']) ?>">
  <p><strong>Grupo:</strong> <?= $encabezado['grupo'] ?: '-' ?></p>
  <p><strong>Fecha:</strong> <?= $encabezado['fecha'] ?: '-' ?></p>

  <form id="formulario">
    <input type="hidden" id="maestro_id" value="<?= $maestro_id ?>">
    <input type="hidden" id="periodo_id" value="<?= $periodo_id ?>">
    <input type="hidden" id="carrera_nombre" value="<?= htmlspecialchars($carrera_nombre) ?>">
    <input type="hidden" id="materia_nombre" value="<?= htmlspecialchars($encabezado['materia']) ?>">
    <input type="hidden" id="grupo_nombre" value="<?= htmlspecialchars($encabezado['grupo']) ?>">

    <div id="practicas-container">
      <?php foreach ($practicas as $index => $practica): ?>
      <div class="border p-3 mb-4">
        <h5>Práctica <?= $index + 1 ?></h5>
        <input type="hidden" name="id[]" value="<?= $practica['id'] ?>">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre[]" value="<?= htmlspecialchars($practica['Nombre_Practica']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Objetivo</label>
            <input type="text" class="form-control" name="objetivo[]" value="<?= htmlspecialchars($practica['Objetivo']) ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Laboratorio</label>
            <input type="text" class="form-control" name="laboratorio[]" value="<?= htmlspecialchars($practica['Laboratorio']) ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Hora Inicio</label>
            <select class="form-select" name="horaInicio[]">
              <?php for ($h = 7; $h <= 19; $h++):
                $hora = str_pad($h,2,'0',STR_PAD_LEFT).":00";
                $selected = ($practica['hora_inicio'] === $hora) ? 'selected' : '';
              ?>
                <option value="<?= $hora ?>" <?= $selected ?>><?= $hora ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Hora Fin</label>
            <select class="form-select" name="horaFin[]">
              <?php for ($h = 8; $h <= 20; $h++):
                $hora = str_pad($h,2,'0',STR_PAD_LEFT).":00";
                $selected = ($practica['hora_fin'] === $hora) ? 'selected' : '';
              ?>
                <option value="<?= $hora ?>" <?= $selected ?>><?= $hora ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Rúbrica</label>
            <input type="text" class="form-control" name="rubrica[]" value="<?= htmlspecialchars($practica['Tipo_de_Laboratorio']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha Propuesta</label>
            <input type="date" class="form-control" name="fechaPropuesta[]" value="<?= $practica['Fecha_Propuesta'] ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha Real</label>
            <input type="date" class="form-control" name="fechaReal[]" value="<?= $practica['Fecha_Real'] ?>">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="d-grid">
      <button type="button" class="btn btn-success btn-lg" onclick="generarPDF()">Guardar y Generar PDF</button>
    </div>
  </form>
</div>

<script>
const { jsPDF } = window.jspdf;

function formatearHora24(horaStr) {
  const [h, m] = horaStr.split(":");
  return `${h.padStart(2,'0')}:${m}`;
}

async function cargarImagenFondo(url) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "blob";
    xhr.onload = function() {
      if (this.status === 200) {
        const reader = new FileReader();
        reader.onload = function(event){ resolve(event.target.result); };
        reader.readAsDataURL(this.response);
      } else reject(new Error("No se pudo cargar la imagen"));
    };
    xhr.onerror = () => reject(new Error("Error de red"));
    xhr.send();
  });
}

async function generarPDF() {
  const datos = {
    carrera: document.getElementById('carrera_nombre').value,
    asignatura: document.getElementById('materia_nombre').value,
    docente: "<?= $docente ?>",
    grupo: "<?= $encabezado['grupo'] ?>",
    periodo: "",
    fechaEntrega: "<?= $encabezado['fecha'] ?>"
  };

  const practicas = [];
  const containers = document.querySelectorAll("#practicas-container > div");
  containers.forEach((div) => {
    practicas.push({
      nombre: div.querySelector("input[name='nombre[]']").value,
      objetivo: div.querySelector("input[name='objetivo[]']").value,
      laboratorio: div.querySelector("input[name='laboratorio[]']").value,
      horaInicio: div.querySelector("select[name='horaInicio[]']").value,
      horaFin: div.querySelector("select[name='horaFin[]']").value,
      fechaProgramada: div.querySelector("input[name='fechaPropuesta[]']").value,
      fechaRealizada: div.querySelector("input[name='fechaReal[]']").value,
      rubrica: div.querySelector("input[name='rubrica[]']").value
    });
  });

  const pdf = await generarPDFConFormato(datos, practicas);

  const formData = new FormData();
  formData.append("archivo", pdf.output("blob"), `FO-TESH-98_actualizado_${datos.grupo}.pdf`);
  formData.append("docente_id", <?= $maestro_id ?>);
  formData.append("carrera_id", <?= $carrera_id ?>);
  formData.append("periodo_id", <?= $periodo_id ?>);
  formData.append("practicas", JSON.stringify(practicas));
  formData.append("pdf_id", <?= $practicas[0]['pdf_id'] ?>); // PDF original
  formData.append("pdf_original_id", <?= $practicas[0]['pdf_id'] ?>); // vincular
  formData.append("actualizado_admin", 1); // marcar como editado por admin
  formData.append("carrera_nombre", document.getElementById('carrera_nombre').value);
  formData.append("materia_nombre", document.getElementById('materia_nombre').value);
  formData.append("grupo_nombre", document.getElementById('grupo_nombre').value);

  try {
    const respuesta = await fetch("guardar_pdf_actualizado.php", {
      method: "POST",
      body: formData
    });
    const resultado = await respuesta.text();
    alert(resultado);
    window.location.href = `ver_reportes_docente.php?carrera_id=<?= $carrera_id ?>&docente_id=<?= $maestro_id ?>&periodo_id=<?= $periodo_id ?>`;
  } catch (error) {
    alert("Error al guardar el PDF: " + error);
  }
}

async function generarPDFConFormato(datos, practicas) {
  const pdf = new jsPDF("p", "pt", "letter");

  try {
    const fondo = await cargarImagenFondo("FOTESH.jpg");
    pdf.addImage(fondo, "JPEG", 0, 0, 565, 792);
  } catch {}

  pdf.setFontSize(10);
  pdf.text(datos.carrera, 110, 135);
  pdf.text(datos.asignatura, 110, 148);
  pdf.text(datos.docente, 110, 161);
  pdf.text(datos.grupo, 460, 135);
  pdf.text(datos.periodo, 460, 148);
  pdf.text(datos.fechaEntrega, 460, 161);

  let yInicial = 220;
  let practicasPorPagina = 4;
  let y = yInicial;

  for (let i = 0; i < practicas.length; i++) {
    const p = practicas[i];

    if (i > 0 && i % practicasPorPagina === 0) {
      pdf.addPage();
      try { pdf.addImage(await cargarImagenFondo('FOTESH.jpg'), 'JPEG', 0, 0, 565, 792); } catch {}
      y = yInicial;
      pdf.setFontSize(10);
      pdf.text(datos.carrera, 110, 135);
      pdf.text(datos.asignatura, 110, 148);
      pdf.text(datos.docente, 110, 161);
      pdf.text(datos.grupo, 460, 135);
      pdf.text(datos.periodo, 460, 148);
      pdf.text(datos.fechaEntrega, 460, 161);
    }

    pdf.setFontSize(9);
    pdf.text(String(i + 1), 50, y);
    pdf.text(p.nombre, 75, y + 10, { maxWidth: 100 });
    pdf.text(p.objetivo, 190, y + 10, { maxWidth: 100 });
    pdf.text(p.laboratorio, 310, y + 30, { maxWidth: 100 });
    const horario = `${formatearHora24(p.horaInicio)} - ${formatearHora24(p.horaFin)}`;
    pdf.text(horario, 343, y + 25, { maxWidth: 30 });
    pdf.text(p.fechaProgramada, 380, y + 30);
    pdf.text(p.fechaRealizada, 430, y + 30);
    pdf.text(p.rubrica, 480, y + 30, { maxWidth: 40 });
    y += 80;
  }

  return pdf;
}
</script>
</body>
</html>
