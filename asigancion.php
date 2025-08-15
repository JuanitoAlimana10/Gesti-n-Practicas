<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'conexion.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {
    echo "Acceso denegado.";
    exit;
}

$maestros = $conn->query("SELECT id, nombre FROM tipodeusuarios WHERE rol = 'maestro' AND estado = 'activo' ORDER BY nombre");
$materias = $conn->query("SELECT id, nombre FROM materias ORDER BY nombre");
$carreras = $conn->query("SELECT id, nombre FROM carreras ORDER BY nombre");

$asignaciones = $conn->query("
    SELECT a.id, a.maestro_id, a.materia_id, a.carrera_id, a.grupo_id,
           tu.nombre AS maestro_nombre, m.nombre AS materia_nombre, 
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
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
        }
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .table th {
            background-color: #f8f9fc;
            color: var(--secondary-color);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
        }
        .empty-state {
            background-color: #f8f9fc;
            border: 2px dashed #d1d3e2;
            border-radius: 0.5rem;
        }
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
                                <?php 
                                $maestros->data_seek(0);
                                while ($m = $maestros->fetch_assoc()): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Materia</label>
                            <select name="materia_id" class="form-select" required>
                                <option value="" selected disabled>Seleccione una materia</option>
                                <?php 
                                $materias->data_seek(0);
                                while ($mat = $materias->fetch_assoc()): ?>
                                    <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Carrera</label>
                            <select name="carrera_id" id="selector-carrera" class="form-select" required>
                                <option value="" selected disabled>Seleccione una carrera</option>
                                <?php 
                                $carreras->data_seek(0);
                                while ($c = $carreras->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grupo</label>
                            <select name="grupo_id" id="selector-grupo" class="form-select" required>
                                <option value="">Seleccione grupo</option>
                            </select>
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
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($asig = $asignaciones->fetch_assoc()): ?>
                                        <tr data-id="<?= $asig['id'] ?>"
                                            data-maestro="<?= $asig['maestro_id'] ?>"
                                            data-materia="<?= $asig['materia_id'] ?>"
                                            data-carrera="<?= $asig['carrera_id'] ?>"
                                            data-grupo="<?= $asig['grupo_id'] ?>">
                                            <td><?= htmlspecialchars($asig['maestro_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['materia_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['carrera_nombre']) ?></td>
                                            <td><?= htmlspecialchars($asig['grupo_nombre']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary btn-edit" title="Editar"><i class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger btn-delete" title="Eliminar"><i class="bi bi-trash"></i></button>
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

<!-- Modal para editar asignación -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="form-editar-asignacion" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarLabel"><i class="bi bi-pencil"></i> Editar Asignación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editar-id" />
        <div class="mb-3">
            <label class="form-label">Maestro</label>
            <select name="maestro_id" id="editar-maestro" class="form-select" required>
                <option value="">Seleccione un maestro</option>
                <?php
                $maestros2 = $conn->query("SELECT id, nombre FROM tipodeusuarios WHERE rol = 'maestro' AND estado = 'activo' ORDER BY nombre");
                while ($m = $maestros2->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Materia</label>
            <select name="materia_id" id="editar-materia" class="form-select" required>
                <option value="">Seleccione una materia</option>
                <?php
                $materias2 = $conn->query("SELECT id, nombre FROM materias ORDER BY nombre");
                while ($mat = $materias2->fetch_assoc()): ?>
                    <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Carrera</label>
            <select name="carrera_id" id="editar-carrera" class="form-select" required>
                <option value="">Seleccione una carrera</option>
                <?php
                $carreras2 = $conn->query("SELECT id, nombre FROM carreras ORDER BY nombre");
                while ($c = $carreras2->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Grupo</label>
            <select name="grupo_id" id="editar-grupo" class="form-select" required>
                <option value="">Seleccione grupo</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Cambios</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
// GRUPOS dependientes en el formulario de nueva asignación
document.getElementById("selector-carrera").addEventListener("change", function () {
    const carreraId = this.value;
    const selectorGrupo = document.getElementById("selector-grupo");
    selectorGrupo.innerHTML = "<option value=''>Cargando grupos...</option>";

    fetch("obtener_grupos.php?carrera_id=" + carreraId)
        .then(response => response.json())
        .then(grupos => {
            selectorGrupo.innerHTML = "<option value=''>Seleccione grupo</option>";
            grupos.forEach(grupo => {
                const option = document.createElement("option");
                option.value = grupo.id;
                option.textContent = grupo.nombre;
                selectorGrupo.appendChild(option);
            });
        });
});

document.getElementById("form-asignacion").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("procesar_asignacion.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("\u2705 " + data.message);
            location.reload();
        } else {
            alert("\u274C " + data.message);
        }
    })
    .catch(error => {
        alert("\u274C Error: " + error.message);
    });
});

// ------------ ACCIONES DE EDITAR Y ELIMINAR ---------------

// EDITAR
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        document.getElementById('editar-id').value = row.getAttribute('data-id');
        document.getElementById('editar-maestro').value = row.getAttribute('data-maestro');
        document.getElementById('editar-materia').value = row.getAttribute('data-materia');
        document.getElementById('editar-carrera').value = row.getAttribute('data-carrera');
        // Carga los grupos al seleccionar carrera (AJAX):
        const carreraId = row.getAttribute('data-carrera');
        const grupoId = row.getAttribute('data-grupo');
        const editarGrupo = document.getElementById('editar-grupo');
        editarGrupo.innerHTML = "<option value=''>Cargando grupos...</option>";
        fetch("obtener_grupos.php?carrera_id=" + carreraId)
            .then(response => response.json())
            .then(grupos => {
                editarGrupo.innerHTML = "<option value=''>Seleccione grupo</option>";
                grupos.forEach(grupo => {
                    const option = document.createElement("option");
                    option.value = grupo.id;
                    option.textContent = grupo.nombre;
                    editarGrupo.appendChild(option);
                });
                editarGrupo.value = grupoId;
            });
        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    });
});

document.getElementById("form-editar-asignacion").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("editar_asignacion.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("\u2705 " + data.message);
            location.reload();
        } else {
            alert("\u274C " + data.message);
        }
    })
    .catch(error => {
        alert("\u274C Error: " + error.message);
    });
});

// ELIMINAR
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const id = row.getAttribute('data-id');
        if (confirm("¿Seguro que deseas eliminar esta asignación?")) {
            fetch("eliminar_asignacion.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "id=" + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("\u2705 " + data.message);
                    location.reload();
                } else {
                    alert("\u274C " + data.message);
                }
            })
            .catch(error => {
                alert("\u274C Error: " + error.message);
            });
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
