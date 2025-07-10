<?php 
require 'conexion.php';
require 'validacion_roles.php';
verificarPermiso('maestro');


$docenteId = $_SESSION['id'];

$sql = "SELECT 
          a.id AS asignacion_id,
          c.nombre AS carrera,
          c.id AS carrera_id,
          m.nombre AS materia,
          m.id AS materia_id,
          g.nombre AS grupo,
          g.id AS grupo_id
        FROM asignaciones a
        JOIN carreras c ON a.carrera_id = c.id
        JOIN materias m ON a.materia_id = m.id
        JOIN grupos g ON a.grupo_id = g.id
        WHERE a.maestro_id = ?
        ORDER BY a.id DESC"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $docenteId);
$stmt->execute();
$result = $stmt->get_result();
$asignaciones_docente = $result->fetch_all(MYSQLI_ASSOC);

$primera = $asignaciones_docente[0] ?? null;

$datos_precargados = [
    'carrera' => $primera['carrera'] ?? '',
    'materia' => $primera['materia'] ?? '',
    'grupo' => $primera['grupo'] ?? '',
    'docente' => $_SESSION['nombre']
];
$carrera_nombre = $primera['carrera'] ?? '';
$materia_nombre = $primera['materia'] ?? '';
?>
<script>
const asignaciones = <?= json_encode($asignaciones_docente) ?>;
</script>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>FO-TESH-98 - Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
  <style>
    .signature-container {
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 10px;
    }
    #firma {
      width: 100%;
      height: 150px;
      background-color: #f8f9fa;
    }
    .campo-precargado {
      background-color: #f8f9fa;
      cursor: not-allowed;
    }
    .bloque-practica {
      border: 1px solid #ddd;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      background-color: #fdfdfd;
    }
  </style>
</head>
<body class="bg-light p-4">
  <div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-center">Formulario FO-TESH-98</h2>
    <form id="formulario">
      <input type="hidden" name="docente_id" value="<?= $_SESSION['id'] ?>">
<input type="hidden" name="docente" value="<?= htmlspecialchars($datos_precargados['docente']) ?>">

<input type="hidden" name="materia" id="materia_id" value="<?= htmlspecialchars($primera['materia_id'] ?? '') ?>">
<input type="hidden" id="materia_nombre" value="<?= htmlspecialchars($primera['materia']) ?>">

<input type="hidden" name="carrera" id="carrera_id" value="<?= htmlspecialchars($primera['carrera_id'] ?? '') ?>">
<input type="hidden" id="carrera_nombre" value="<?= htmlspecialchars($primera['carrera']) ?>">

<input type="hidden" id="grupo_nombre" value="<?= htmlspecialchars($primera['grupo']) ?>">

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Carrera</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($primera['carrera']) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Asignatura</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($primera['materia']) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Docente</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($datos_precargados['docente']) ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Grupo</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($primera['grupo']) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label for="periodo" class="form-label">Periodo Escolar</label>
          <select id="periodo" class="form-select" required>
            <option value="marzo-julio">Marzo-Julio</option>
            <option value="septiembre-enero">Septiembre-Enero</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label for="fechaEntrega" class="form-label">Fecha de Entrega</label>
          <input type="date" class="form-control" id="fechaEntrega" required>
        </div>
      </div>

      <div id="contenedor-practicas"></div>

      <div class="mb-3">
        <button type="button" class="btn btn-outline-success" id="agregarPractica">Agregar otra práctica</button>
      </div>

      <hr>
      <h4 class="mb-3">Firma del Docente</h4>
      <div class="mb-4">
        <div class="signature-container">
          <canvas id="firma"></canvas>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="borrarFirma">Borrar Firma</button>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">Generar PDF</button>
      </div>
    </form>
  </div>


<script>
const { jsPDF } = window.jspdf;
let signaturePad;
let contadorPracticas = 0;
function generarOpcionesHora24() {
  let opciones = '<option value="" disabled selected>--</option>';
  for (let h = 7; h <= 19; h++) {
    const hora = h.toString().padStart(2, '0') + ":00";
    opciones += `<option value="${hora}">${hora}</option>`;
  }
  return opciones;
}



function crearBloquePractica() {
  contadorPracticas++;
  const html = `
    <div class="bloque-practica position-relative" data-index="${contadorPracticas}">
      <button type="button" class="btn-close position-absolute top-0 end-0 m-2 eliminar-practica" aria-label="Eliminar"></button>
      <h5>Práctica ${contadorPracticas}</h5>
      <div class="col-md-6 mb-3">
  <label class="form-label">Nombre de la Práctica</label>
  <input type="text" class="form-control" name="nombrePractica" maxlength="120" required>
</div>
<div class="col-md-6 mb-3">
  <label class="form-label">Objetivo</label>
  <input type="text" class="form-control" name="objetivo" maxlength="120" required>
</div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Laboratorio</label>
          <select class="form-select" name="laboratorio" required>
            <option value="" disabled selected>Seleccione laboratorio</option>
            <option value="LIN ">LIN - Industrial</option>
            <option value="LM">LM - Multifuncional</option>
            <option value="LMA">LMA - Materiales</option>
            <option value="LR">LR - Repostería</option>
            <option value="LMS">LMS - Mecánica de Suelos y pavimentos</option>
            <option value="LA">LA - Automatización</option>
            <option value="LC">LC - Cómputo</option>
            <option value="LT">LT - Topografía</option>
            <option value="LAC">LAC – Artes culinarias</option>
            <option value="LQA">LQA - Química de alimentos</option>
            <option value="LME">LME - Metrología</option>
            <option value="LH">LH - Hidráulica</option>
            <option value="LS ">LS - Servicio</option>
            <option value="LFQ">LFQ - Física y Química</option>
            <option value="LEE">LEE – Electricidad y electrónica</option>
            <option value="MCD">MCD – Microprocesadores y comunicaciones digitales</option>
            <option value="LE">LE-Especialidades</option>
          </select>
          </div>
          <div class="col-md-4 mb-3">
          <label class="form-label">Hora de inicio</label>
          <select class="form-select hora-select" name="horaInicio" required>
  <option value="">--</option>
  ${generarOpcionesHora24()}
</select>

            </div>
            <div class="col-md-4 mb-3">
            <label class="form-label">Hora de finalización</label>
            <select class="form-select hora-select" name="horaFin" required>
  <option value="">--</option>
  ${generarOpcionesHora24()}
</select>
            </div>
            <div class="col-md-4 mb-3">
            <label class="form-label">Rúbrica</label>
            <input type="text" class="form-control" name="rubrica" value="" readonly>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Fecha Programada</label>
          <input type="date" class="form-control" name="fechaProgramada" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Fecha Realizada</label>
          <input type="date" class="form-control" name="fechaRealizada" required>
        </div>
      </div>
    </div>`;
  document.getElementById('contenedor-practicas').insertAdjacentHTML('beforeend', html);
}
document.getElementById('contenedor-practicas').addEventListener('click', function (e) {
  if (e.target.classList.contains('eliminar-practica')) {
    const bloque = e.target.closest('.bloque-practica');
    if (bloque) {
      bloque.remove();
    }
  }
});


function inicializarFirma() {
  const canvas = document.getElementById("firma");
  signaturePad = new SignaturePad(canvas, {
    backgroundColor: '#f8f9fa',
    penColor: '#000000'
  });

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
  }

  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();

  document.getElementById('borrarFirma').addEventListener('click', function() {
    signaturePad.clear();
  });
}

function normalizarTexto(texto) {
  return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
}

function formatearHora24(horaStr) {
  // Asegura que la hora venga en "HH:MM"
  const [h, m] = horaStr.split(':');
  return `${h.padStart(2, '0')}:${m}`;
}


async function generarPDF(datos, practicas) {
  const pdf = new jsPDF('p', 'pt', 'letter');

  try {
    const imagenFondo = await cargarImagenFondo('FOTESH.jpg');
    pdf.addImage(imagenFondo, 'JPEG', 0, 0, 565, 792);
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

  // Si ya hay 4 prácticas en la página actual, agregar nueva página
  if (i > 0 && i % practicasPorPagina === 0) {
    pdf.addPage();
    try {
      pdf.addImage(await cargarImagenFondo('FOTESH.jpg'), 'JPEG', 0, 0, 565, 792);
    } catch {}

    y = yInicial;

    // encabezado
    pdf.setFontSize(10);
    pdf.text(datos.carrera, 110, 135);
    pdf.text(datos.asignatura, 110, 148);
    pdf.text(datos.docente, 110, 161);
    pdf.text(datos.grupo, 460, 135);
    pdf.text(datos.periodo, 460, 148);
    pdf.text(datos.fechaEntrega, 460, 161);
  }

  // 👇 Aquí va el contenido de cada práctica
  pdf.setFontSize(9);
  pdf.text(String(i + 1), 50, y); // Número de práctica
  pdf.text(p.nombre, 75, y + 10, { maxWidth: 100 });
  pdf.text(p.objetivo, 190, y + 10, { maxWidth: 100 });
  pdf.text(p.laboratorio, 310, y + 30, { maxWidth: 100 });

  // Mostrar hora de inicio y fin
  const horario = `${formatearHora24(p.horaInicio)} - ${formatearHora24(p.horaFin)}`;
  pdf.text(horario, 343, y + 25, { maxWidth: 30 });
  pdf.text(p.fechaProgramada, 380, y + 30);
  pdf.text(p.fechaRealizada, 430, y + 30);
  pdf.text(p.rubrica, 480, y + 30, { maxWidth: 40 });
  y += 80; // Avanza a la siguiente fila
}



  const firmaDataURL = signaturePad.toDataURL();
  pdf.text(datos.docente, 160, 693);
  pdf.addImage(firmaDataURL, 'PNG', 370, 645, 120, 40);
  return pdf;
}

function cargarImagenFondo(url) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.responseType = 'blob';
    xhr.onload = function () {
      if (this.status === 200) {
        const reader = new FileReader();
        reader.onload = function (event) {
          resolve(event.target.result);
        };
        reader.readAsDataURL(this.response);
      } else {
        reject(new Error('No se pudo cargar la imagen'));
      }
    };
    xhr.onerror = () => reject(new Error('Error de red'));
    xhr.send();
  });
}



document.getElementById('formulario').addEventListener('submit', async function(e) {
  e.preventDefault();
  if (signaturePad.isEmpty()) {
    alert('Por favor, proporcione su firma.');
    return;
  }

  const datos = {
  carrera: document.getElementById('carrera_nombre').value,
  asignatura: document.getElementById('materia_nombre').value,
  docente: document.querySelector('input[name="docente"]').value,
  grupo: document.getElementById('grupo_nombre').value,
  periodo: document.getElementById('periodo').value,
  fechaEntrega: document.getElementById('fechaEntrega').value
};





  const practicas = Array.from(document.querySelectorAll('.bloque-practica')).map(p => {
  return {
    nombre: p.querySelector('input[name="nombrePractica"]').value,
    objetivo: p.querySelector('input[name="objetivo"]').value,
    laboratorio: p.querySelector('select[name="laboratorio"]').value,
    horaInicio: p.querySelector('select[name="horaInicio"]').value,
    horaFin: p.querySelector('select[name="horaFin"]').value,
    rubrica: p.querySelector('input[name="rubrica"]').value || ' ',
    fechaProgramada: p.querySelector('input[name="fechaProgramada"]').value,
    fechaRealizada: p.querySelector('input[name="fechaRealizada"]').value
  };
});




  const pdf = await generarPDF(datos, practicas);
  const nombreArchivo = `FO-TESH-98_${normalizarTexto(datos.asignatura)}_${datos.fechaEntrega.replace(/-/g, '')}.pdf`;
  const pdfBlob = pdf.output('blob');
  const firmaDataUrl = signaturePad.toDataURL();
  const formData = new FormData();
  formData.append('docente_id', document.querySelector('input[name="docente_id"]').value);
  console.log("DOCENTE A ENVIAR:", datos.docente);
  formData.append('docente_id', document.querySelector('input[name="docente_id"]').value);
formData.append('archivo', pdfBlob, nombreArchivo);
formData.append('titulo', nombreArchivo);
formData.append('carrera', document.getElementById('carrera_id').value); // ID para backend
formData.append('carrera_nombre', document.getElementById('carrera_nombre').value); // solo para mostrar/guardar legible
formData.append('grupo', document.getElementById('grupo_nombre').value);
formData.append('docente', datos.docente);
formData.append('materia', document.getElementById('materia_id').value); // <-- EL ID!
formData.append('materia_nombre', document.getElementById('materia_nombre').value); // (opcional, para pdfs)
formData.append('periodo', document.getElementById('periodo').value);
formData.append('fechaEntrega', document.getElementById('fechaEntrega').value);
formData.append('practicas', JSON.stringify(practicas));
formData.append("firma", firmaDataUrl);

  //  el contenido de 'practicas'
console.log('JSON de prácticas:', JSON.stringify(practicas));

// está enviando en el FormData
for (let pair of formData.entries()) {
  console.log(pair[0] + ':', pair[1]);
}



  fetch('guardar_pdf.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.text())
    .then(text => document.write(text))
    .catch(err => alert('Error al guardar PDF: ' + err.message));
});

document.getElementById('agregarPractica').addEventListener('click', crearBloquePractica);

document.addEventListener('DOMContentLoaded', () => {
  inicializarFirma();
  const hoy = new Date().toISOString().split('T')[0];
  document.getElementById('fechaEntrega').value = hoy;
  crearBloquePractica();
});

</script>
</body>
</html>