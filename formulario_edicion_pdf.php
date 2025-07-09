<?php
include 'conexion.php';
session_start();

$id = $_GET['id'] ?? null;
$maestro_id = $_GET['maestro_id'] ?? null;

if (!$id || !$maestro_id) {
    die("Parámetros faltantes.");
}

// Obtener todas las prácticas relacionadas al mismo PDF
$stmt = $conn->prepare("SELECT * FROM fotesh WHERE pdf_id = (SELECT pdf_id FROM fotesh WHERE id = ?) AND Maestro_id = ?");
$stmt->bind_param("ii", $id, $maestro_id);
$stmt->execute();
$resultado = $stmt->get_result();
$practicas = [];
while ($row = $resultado->fetch_assoc()) {
    $practicas[] = $row;
}

if (empty($practicas)) {
    die("No se encontraron prácticas para este PDF.");
}

$stmt_encabezado = $conn->prepare("SELECT carrera, materia, grupo, fecha FROM pdfs WHERE id = ?");
$stmt_encabezado->bind_param("i", $practicas[0]['pdf_id']);
$stmt_encabezado->execute();
$encabezado = $stmt_encabezado->get_result()->fetch_assoc();


// Obtener nombre del docente
$stmt_docente = $conn->prepare("SELECT nombre FROM tipodeusuarios WHERE id = ?");
$stmt_docente->bind_param("i", $maestro_id);
$stmt_docente->execute();
$docente = $stmt_docente->get_result()->fetch_assoc()['nombre'];
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
  <p><strong>Carrera:</strong> <?= $encabezado['carrera'] ?: '-' ?></p>
  <p><strong>Asignatura:</strong> <?= $encabezado['materia'] ?: '-' ?></p>
  <p><strong>Grupo:</strong> <?= $encabezado['grupo'] ?: '-' ?></p>
  <p><strong>Fecha</strong> <?= $encabezado['fecha'] ?: '-' ?></p>

  <form id="formulario">
    <input type="hidden" id="maestro_id" value="<?= $maestro_id ?>">

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
              <label class="form-label">Horario</label>
              <input type="text" class="form-control" name="horario[]" value="<?= htmlspecialchars($practica['Horario']) ?>">
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
  return `${h.padStart(2, '0')}:${m}`;
}

function cargarImagenFondo(url) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "blob";
    xhr.onload = function () {
      if (this.status === 200) {
        const reader = new FileReader();
        reader.onload = function (event) {
          resolve(event.target.result);
        };
        reader.readAsDataURL(this.response);
      } else {
        reject(new Error("No se pudo cargar la imagen"));
      }
    };
    xhr.onerror = () => reject(new Error("Error de red"));
    xhr.send();
  });
}

async function generarPDF() {
  const datos = {
    carrera: "<?= $encabezado['carrera'] ?>",
    asignatura: "<?= $encabezado['materia'] ?>",
    docente: "<?= $docente ?>",
    grupo: "<?= $encabezado['grupo'] ?>",
    periodo: "<?= $practica['periodo'] ?? '' ?>",
    fechaEntrega: "<?= $encabezado['fecha_entrega'] ?>"
  };

  const p = {
    nombre: document.getElementById("nombre").value,
    objetivo: document.getElementById("objetivo").value,
    laboratorio: document.getElementById("laboratorio").value,
    horario: document.getElementById("horario").value,
    horaInicio: document.getElementById("horario").value.split(" - ")[0],
    horaFin: document.getElementById("horario").value.split(" - ")[1],
    fechaProgramada: document.getElementById("fechaPropuesta").value,
    fechaRealizada: document.getElementById("fechaReal").value,
    rubrica: document.getElementById("rubrica").value
  };

  const practicas = [p];
  const pdf = await generarPDFConFormato(datos, practicas);

  const formData = new FormData();
  formData.append("archivo", pdf.output("blob"), `FO-TESH-98_actualizado_${datos.grupo}.pdf`);
  formData.append("docente", datos.docente);
  formData.append("carrera", datos.carrera);
  formData.append("materia", datos.asignatura);
  formData.append("grupo", datos.grupo);
  formData.append("periodo", datos.periodo);
  formData.append("practicas", JSON.stringify(practicas));
  formData.append("docente_id", <?= $maestro_id ?>);


  try {
    const respuesta = await fetch("guardar_pdf_actualizado.php", {
      method: "POST",
      body: formData
    });
    const resultado = await respuesta.text();
    alert(resultado);
    window.location.href = `ver_reportes_docente.php?carrera_id=1&docente_id=<?= $maestro_id ?>`;
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

  let y = 220;
  for (let i = 0; i < practicas.length; i++) {
    const p = practicas[i];
    pdf.setFontSize(9);
    pdf.text(String(i + 1), 50, y);
    pdf.text(p.nombre, 75, y + 30, { maxWidth: 100 });
    pdf.text(p.objetivo, 190, y + 30, { maxWidth: 100 });
    pdf.text(p.laboratorio, 310, y + 30, { maxWidth: 100 });
    pdf.text(`${formatearHora24(p.horaInicio)} - ${formatearHora24(p.horaFin)}`, 343, y + 25);
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
