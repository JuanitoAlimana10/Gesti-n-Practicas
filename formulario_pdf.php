<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {
    die("Acceso denegado. Debe iniciar sesión como docente.");
}

$datos_precargados = [
    'carrera' => $_GET['carrera'] ?? '',
    'materia' => $_GET['materia'] ?? '',
    'grupo' => $_GET['grupo'] ?? '',
    'docente' => $_SESSION['nombre']
];
?>
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
      <input type="hidden" name="carrera" value="<?= htmlspecialchars($datos_precargados['carrera']) ?>">
      <input type="hidden" name="materia" value="<?= htmlspecialchars($datos_precargados['materia']) ?>">
      <input type="hidden" name="grupo" value="<?= htmlspecialchars($datos_precargados['grupo']) ?>">
      <input type="hidden" name="docente" value="<?= htmlspecialchars($datos_precargados['docente']) ?>">

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Carrera</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($datos_precargados['carrera']) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Asignatura</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($datos_precargados['materia']) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Docente</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($datos_precargados['docente']) ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Grupo</label>
          <input type="text" class="form-control campo-precargado" value="<?= htmlspecialchars($datos_precargados['grupo']) ?>" readonly>
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

function crearBloquePractica() {
  contadorPracticas++;
  const html = `
    <div class="bloque-practica position-relative" data-index="${contadorPracticas}">
      <button type="button" class="btn-close position-absolute top-0 end-0 m-2 eliminar-practica" aria-label="Eliminar"></button>
      <h5>Práctica ${contadorPracticas}</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Nombre de la Práctica</label>
          <input type="text" class="form-control" name="nombrePractica" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Objetivo</label>
          <input type="text" class="form-control" name="objetivo" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Laboratorio</label>
          <select class="form-select" name="laboratorio" required>
            <option value="" disabled selected>Seleccione laboratorio</option>
            <option value="LIN - Industrial">LIN - Industrial</option>
            <option value="LM - Multifuncional">LM - Multifuncional</option>
            <option value="LMA - Materiales">LMA - Materiales</option>
            <option value="LR - Repostería">LR - Repostería</option>
            <option value="LMS - Mecánica de Suelos y pavimentos">LMS - Mecánica de Suelos y pavimentos</option>
            <option value="LA - Automatización">LA - Automatización</option>
            <option value="LC - Cómputo">LC - Cómputo</option>
            <option value="LT - Topografía">LT - Topografía</option>
            <option value="LAC – Artes culinarias">LAC – Artes culinarias</option>
            <option value="LQA - Química de alimentos">LQA - Química de alimentos</option>
            <option value="LME - Metrología">LME - Metrología</option>
            <option value="LH - Hidráulica">LH - Hidráulica</option>
            <option value="LS - Servicio">LS - Servicio</option>
            <option value="LFQ - Física y Química">LFQ - Física y Química</option>
            <option value="LEE – Electricidad y electrónica">LEE – Electricidad y electrónica</option>
            <option value="MCD – Microprocesadores y comunicaciones digitales">MCD – Microprocesadores y comunicaciones digitales</option>
            <option value="LE-Especialidades">LE-Especialidades</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Horario</label>
          <input type="text" class="form-control" name="horario" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Rúbrica</label>
          <input type="text" class="form-control" name="rubrica" required>
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

    // Redibujar encabezado si es necesario
    pdf.setFontSize(10);
    pdf.text(datos.carrera, 110, 135);
    pdf.text(datos.asignatura, 110, 148);
    pdf.text(datos.docente, 110, 161);
    pdf.text(datos.grupo, 460, 135);
    pdf.text(datos.periodo, 460, 148);
    pdf.text(datos.fechaEntrega, 460, 161);
  }

  pdf.text(String(i + 1), 49, y);
  pdf.text(p.nombre, 70, y + 30, { maxWidth: 120 });
  pdf.text(p.objetivo, 200, y + 30, { maxWidth: 120 });
  pdf.text(p.laboratorio, 305, y + 30, { maxWidth: 40 });
  pdf.text(p.horario, 343, y + 5, { maxWidth: 25 });
  pdf.text(p.fechaProgramada, 380, y + 20);
  pdf.text(p.fechaRealizada, 430, y + 20);
  pdf.text(p.rubrica, 480, y + 20, { maxWidth: 40 });

  y += 80;
}


  const firmaDataURL = signaturePad.toDataURL();
  pdf.text(datos.docente, 200, y + 100);
  pdf.addImage(firmaDataURL, 'PNG', 370, y + 135, 150, 40);
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
    carrera: "<?= htmlspecialchars($datos_precargados['carrera']) ?>",
    asignatura: "<?= htmlspecialchars($datos_precargados['materia']) ?>",
    docente: "<?= htmlspecialchars($datos_precargados['docente']) ?>",
    grupo: "<?= htmlspecialchars($datos_precargados['grupo']) ?>",
    periodo: document.getElementById('periodo').value,
    fechaEntrega: document.getElementById('fechaEntrega').value
  };

  const practicas = Array.from(document.querySelectorAll('.bloque-practica')).map(p => {
    return {
      nombre: p.querySelector('input[name="nombrePractica"]').value,
      objetivo: p.querySelector('input[name="objetivo"]').value,
      laboratorio: p.querySelector('select[name="laboratorio"]').value,
      horario: p.querySelector('input[name="horario"]').value,
      rubrica: p.querySelector('input[name="rubrica"]').value,
      fechaProgramada: p.querySelector('input[name="fechaProgramada"]').value,
      fechaRealizada: p.querySelector('input[name="fechaRealizada"]').value
    };
  });

  const pdf = await generarPDF(datos, practicas);
  const nombreArchivo = `FO-TESH-98_${normalizarTexto(datos.asignatura)}_${datos.fechaEntrega.replace(/-/g, '')}.pdf`;
  const pdfBlob = pdf.output('blob');
  const formData = new FormData();
  formData.append('archivo', pdfBlob, nombreArchivo);
formData.append('titulo', nombreArchivo);
formData.append('carrera', datos.carrera);
formData.append('grupo', datos.grupo); // Añadir grupo
formData.append('docente', datos.docente); // Añadir docente
formData.append('materia', datos.asignatura); // Añadir asignatura
formData.append('periodo', datos.periodo); // Añadir periodo
formData.append('fechaEntrega', datos.fechaEntrega); // Añadir fechaEntrega
formData.append('practicas', JSON.stringify(practicas));

  // 👉 Depurar el contenido de 'practicas'
console.log('JSON de prácticas:', JSON.stringify(practicas));

// 👉 Ver qué se está enviando en el FormData
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