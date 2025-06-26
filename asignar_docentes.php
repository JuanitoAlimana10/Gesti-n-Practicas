<?php
include 'conexion.php';
session_start();

// Solo permitir acceso al administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $maestro_id = $_POST['maestro_id'];
    $materia_id = $_POST['materia_id'];
    $carrera_id = $_POST['carrera_id'];
    $grupo_id = $_POST['grupo_id'];

    // Verificar si ya existe la asignación
    $check = $conn->prepare("SELECT id FROM asignaciones WHERE maestro_id = ? AND materia_id = ? AND carrera_id = ? AND grupo_id = ?");
    $check->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "<div class='alert alert-warning'>Esta asignación ya existe.</div>";
    } else {
        $insert = $conn->prepare("INSERT INTO asignaciones (maestro_id, materia_id, carrera_id, grupo_id) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiii", $maestro_id, $materia_id, $carrera_id, $grupo_id);
        if ($insert->execute()) {
            $mensaje = "<div class='alert alert-success'>Asignación guardada correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al guardar la asignación.</div>";
        }
    }
}

// Consultas para llenar los selects
$maestros = $conn->query("SELECT id, nombre FROM tipodeusuarios WHERE rol = 'maestro'");
$materias = $conn->query("SELECT id, nombre FROM materias");
$carreras = $conn->query("SELECT id, nombre FROM carreras");
$grupos = $conn->query("SELECT id, nombre FROM grupos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Asignar Docente a Materia / Carrera / Grupo</h3>
    <?= $mensaje ?>

    <form method="POST" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Docente</label>
            <select name="maestro_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($m = $maestros->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Materia</label>
            <select name="materia_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($mat = $materias->fetch_assoc()): ?>
                    <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Carrera</label>
            <select name="carrera_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($c = $carreras->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Grupo</label>
            <select name="grupo_id" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($g = $grupos->fetch_assoc()): ?>
                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Asignar</button>
        </div>
    </form>
</div>
</body>
</html>
