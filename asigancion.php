<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'conexion.php';
session_start();

// Verificar si es administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo "Acceso denegado.";
    exit;
}

// Obtener datos para los selects
$maestros = $conn->query("SELECT id, nombre FROM tipodeusuarios WHERE rol = 'maestro' AND estado = 'activo' ORDER BY nombre");
$materias = $conn->query("SELECT id, nombre FROM materias ORDER BY nombre");
$carreras = $conn->query("SELECT id, nombre FROM carreras ORDER BY nombre");
$grupos = $conn->query("SELECT id, nombre FROM grupos ORDER BY nombre");

// Consulta de asignaciones
$asignaciones = $conn->query("
    SELECT a.id, tu.nombre AS maestro_nombre, m.nombre AS materia_nombre, 
           c.nombre AS carrera_nombre, g.nombre AS grupo_nombre
    FROM asignaciones a
    JOIN tipodeusuarios tu ON a.maestro_id = tu.id
    JOIN materias m ON a.materia_id = m.id
    JOIN carreras c ON a.carrera_id = c.id
    JOIN grupos g ON a.grupo_id = g.id
    ORDER BY tu.nombre, m.nombre
");

if ($conn->error) {
    die("Error en la base de datos: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestor de Asignaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <style>
        :root { --primary-color: #4e73df; --secondary-color: #858796; }
        body { background-color: #f8f9fc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 0.5rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
        .card-header { background-color: var(--primary-color); color: white; border-radius: 0.5rem 0.5rem 0 0 !important; }
        .table th { background-color: #f8f9fc; color: var(--secondary-color); text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }
        .action-btn { min-width: 100px; }
        .empty-state { background-color: #f8f9fc; border: 2px dashed #d1d3e2; border-radius: 0.5rem; }
        .bi.spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-journal-bookmark-fill text-primary"></i> Gestor de Asignaciones</h2>
            <p class="text-muted mb-0">Administración de asignaciones académicas</p>
        </div>
        <a href="panel_admin.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Panel Principal
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Asignación</h5>
                </div>
                <div class="card-body">
                    <form id="form-asignacion">
                        <div class="mb-3">
                            <label class="form-label">Maestro</label>
                            <select name="maestro_id" class="form-select" required>
                                <option value="" selected disabled>Seleccione un maestro</option>
                                <?php while ($m = $maestros->fetch_assoc()): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Materia</label>
                            <select name="materia_id" class="form-select" required>
                                <option value="" selected disabled>Seleccione una materia</option>
                                <?php while ($mat = $materias->fetch_assoc()): ?>
                                    <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Carrera</label>
                                <select name="carrera_id" class="form-select" required>
                                    <option value="" selected disabled>Seleccione una carrera</option>
                                    <?php while ($car = $carreras->fetch_assoc()): ?>
                                        <option value="<?= $car['id'] ?>"><?= htmlspecialchars($car['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Grupo</label>
                                <select name="grupo_id" class="form-select" required>
                                    <option value="" selected disabled>Seleccione un grupo</option>
                                    <?php while ($gr = $grupos->fetch_assoc()): ?>
                                        <option value="<?= $gr['id'] ?>"><?= htmlspecialchars($gr['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            <i class="bi bi-save"></i> Guardar Asignación
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Asignaciones Registradas</h5>
                    <span class="badge bg-white text-primary"><?= $asignaciones->num_rows ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if ($asignaciones->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Maestro</th>
                                        <th>Materia</th>
                                        <th>Carrera</th>
                                        <th>Grupo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($asig = $asignaciones->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asig['maestro_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['materia_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['carrera_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['grupo_nombre']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info action-btn"
                                                        onclick="mostrarDetalles(
                                                            '<?= htmlspecialchars($asig['maestro_nombre']) ?>',
                                                            '<?= htmlspecialchars($asig['materia_nombre']) ?>',
                                                            '<?= htmlspecialchars($asig['carrera_nombre']) ?>',
                                                            '<?= htmlspecialchars($asig['grupo_nombre']) ?>'
                                                        )">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn"
                                                        onclick="eliminarAsignacion(<?= $asig['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 empty-state">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No hay asignaciones registradas</h5>
                            <p class="text-muted">Agregue una nueva asignación usando el formulario</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Asignación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Maestro:</label>
                    <p id="detalle-maestro" class="fw-bold"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Materia:</label>
                    <p id="detalle-materia" class="fw-bold"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Carrera:</label>
                    <p id="detalle-carrera" class="fw-bold"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Grupo:</label>
                    <p id="detalle-grupo" class="fw-bold"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Envío del formulario con AJAX
    document.getElementById('form-asignacion').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Procesando...';
        submitBtn.disabled = true;

        fetch('procesar_asignacion.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la red');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                window.location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function eliminarAsignacion(id) {
    if (confirm('¿Estás seguro de eliminar esta asignación?')) {
        fetch('eliminar_asignacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('🗑️ ' + data.message);
                window.location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }
}

function mostrarDetalles(maestro, materia, carrera, grupo) {
    document.getElementById('detalle-maestro').textContent = maestro;
    document.getElementById('detalle-materia').textContent = materia;
    document.getElementById('detalle-carrera').textContent = carrera;
    document.getElementById('detalle-grupo').textContent = grupo;
    
    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
    modal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>