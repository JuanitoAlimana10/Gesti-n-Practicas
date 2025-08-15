<?php
require 'conexion.php';
require 'validacion_roles.php';
verificarPermiso('maestro');

$docente_id = $_SESSION['id'];
$nombre_maestro = $_SESSION['nombre'] ?? '';

// Obtener periodo activo
$sqlPeriodo = "SELECT id, nombre FROM periodos WHERE activo = 1 LIMIT 1";
$resultPeriodo = $conn->query($sqlPeriodo);
$periodoActivo = ['id'=>0, 'nombre'=>'No hay periodo activo'];
if($resultPeriodo && $resultPeriodo->num_rows > 0){
    $periodoActivo = $resultPeriodo->fetch_assoc();
}

// Obtener todos los periodos
$sqlPeriodos = "SELECT id, nombre, activo FROM periodos ORDER BY id DESC";
$resultPeriodos = $conn->query($sqlPeriodos);
$periodos = [];
while($p = $resultPeriodos->fetch_assoc()){
    $periodos[$p['id']] = $p;
}

// Periodo seleccionado
$periodoSeleccionado = isset($_GET['periodo']) && is_numeric($_GET['periodo']) ? (int)$_GET['periodo'] : (int)$periodoActivo['id'];

// Obtener asignaciones del docente
$sql = "
SELECT a.id AS asignacion_id,
       c.nombre AS carrera,
       c.id AS carrera_id,
       m.nombre AS materia,
       m.id AS materia_id,
       g.nombre AS grupo,
       g.id AS grupo_id
FROM asignaciones a
JOIN materias m ON a.materia_id = m.id
JOIN carreras c ON a.carrera_id = c.id
JOIN grupos g ON a.grupo_id = g.id
WHERE a.maestro_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $docente_id);
$stmt->execute();
$resultado = $stmt->get_result();
$asignaciones = $resultado->fetch_all(MYSQLI_ASSOC);

// Obtener FOTESH ya registrados en el periodo activo
$sqlRegistrados = "SELECT materia_id, carrera_id 
                   FROM fotesh 
                   WHERE maestro_id = ? AND periodo_id = ?";
$stmtReg = $conn->prepare($sqlRegistrados);
$stmtReg->bind_param("ii", $docente_id, $periodoActivo['id']);
$stmtReg->execute();
$resReg = $stmtReg->get_result();
$materiasRegistradas = [];
while($r = $resReg->fetch_assoc()){
    $materiasRegistradas[] = [
        'materia_id' => (int)$r['materia_id'],
        'carrera_id'  => (int)$r['carrera_id']
    ];
}

// Verificar si periodo seleccionado es activo
$esPeriodoActivo = ($periodoSeleccionado === (int)$periodoActivo['id']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Docente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
button[disabled] {
    cursor: not-allowed;
    opacity: 0.7;
}
.materia-registrada {
    background-color: #d4edda !important; /* verde claro */
}
.periodo-inactivo {
    color: #856404;
    font-weight: bold;
}
</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Panel del Docente</span>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item"><span class="nav-link text-light fw-bold"><?= htmlspecialchars($nombre_maestro) ?></span></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="alert alert-info">
        <strong>Periodo activo:</strong> <?= htmlspecialchars($periodoActivo['nombre']) ?>
        <?php if (!$esPeriodoActivo): ?>
            <span class="periodo-inactivo">– Este periodo no está activo</span>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <form method="get" class="d-flex align-items-center gap-2">
            <label for="periodo">Filtrar por periodo:</label>
            <select name="periodo" id="periodo" class="form-select" onchange="this.form.submit()">
                <?php foreach($periodos as $id => $p): ?>
                    <option value="<?= $id ?>" <?= $id == $periodoSeleccionado ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?> <?= $p['activo'] ? '(Activo)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <h3 class="mb-4">Materias asignadas</h3>
    <?php if (count($asignaciones) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Materia</th>
                <th>Grupo</th>
                <th>Carrera</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($asignaciones as $fila): 
            $yaRegistrado = false;
            foreach($materiasRegistradas as $r){
                if( (int)$r['materia_id'] === (int)$fila['materia_id'] &&
                    (int)$r['carrera_id'] === (int)$fila['carrera_id']){
                    $yaRegistrado = true;
                    break;
                }
            }

            $puedeRegistrar = $esPeriodoActivo && !$yaRegistrado;
            $filaClase = ($yaRegistrado && $esPeriodoActivo) ? 'materia-registrada' : '';

            // Obtener último PDF original de esta materia
            $sqlUltimoPDF = "SELECT id, ruta, pdf_original_id 
                             FROM pdfs 
                             WHERE materia = ? AND grupo = ? AND carrera = ? 
                             AND usuario_id = ? AND periodo_id = ? 
                             ORDER BY id DESC LIMIT 1";
            $stmtPDF = $conn->prepare($sqlUltimoPDF);
            $stmtPDF->bind_param(
                "sssii",
                $fila['materia'], 
                $fila['grupo'], 
                $fila['carrera'], 
                $docente_id, 
                $periodoSeleccionado
            );
            $stmtPDF->execute();
            $resPDF = $stmtPDF->get_result();
            $ultimoPDF = $resPDF->fetch_assoc();

            // Buscar PDF actualizado si existe
            $pdfActualizado = null;
            if($ultimoPDF){
                $stmtPDFAct = $conn->prepare("SELECT ruta FROM pdfs WHERE pdf_original_id = ?");
                $stmtPDFAct->bind_param("i", $ultimoPDF['id']);
                $stmtPDFAct->execute();
                $resAct = $stmtPDFAct->get_result();
                $pdfActualizado = $resAct->fetch_assoc();
            }
        ?>
            <tr class="<?= $filaClase ?>">
                <td><?= htmlspecialchars($fila['materia']) ?></td>
                <td><?= htmlspecialchars($fila['grupo']) ?></td>
                <td><?= htmlspecialchars($fila['carrera']) ?></td>
                <td>
                    <?php if($puedeRegistrar): ?>
                        <a href="formulario_pdf.php?materia_id=<?= $fila['materia_id'] ?>&carrera_id=<?= $fila['carrera_id'] ?>&grupo_id=<?= $fila['grupo_id'] ?>&periodo_id=<?= $periodoSeleccionado ?>" class="btn btn-primary btn-sm">Registrar FOTESH</a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>No disponible</button>
                    <?php endif; ?>

                    <!-- Botón Ver estadísticas -->
                    <a href="reporte_docente_materia.php?materia_id=<?= $fila['materia_id'] ?>&docente_id=<?= $docente_id ?>&carrera_id=<?= $fila['carrera_id'] ?>&periodo_id=<?= $periodoSeleccionado ?>" class="btn btn-warning btn-sm">Ver estadísticas</a>

                    <?php if($ultimoPDF): ?>
                        <a href="<?= htmlspecialchars($ultimoPDF['ruta']) ?>" target="_blank" class="btn btn-info btn-sm">Ver PDF original</a>
                        <?php if($pdfActualizado): ?>
                            <a href="<?= htmlspecialchars($pdfActualizado['ruta']) ?>" target="_blank" class="btn btn-success btn-sm">Ver PDF actualizado</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No hay PDF</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning">No tienes materias asignadas.</div>
    <?php endif; ?>
</div>
</body>
</html>
