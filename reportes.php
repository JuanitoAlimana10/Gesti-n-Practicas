<?php
include 'conexion.php';
session_start();

// Solo permitir acceso a usuarios con sesión iniciada
if (!isset($_SESSION['id'])) {
    echo "Acceso denegado.";
    exit;
}

$usuario_id = $_SESSION['id']; // ID del usuario actual

// Consultas con chequeo de resultado para evitar warnings
$result = $conn->query("SELECT COUNT(*) as total FROM pdfs WHERE usuario_id = $usuario_id");
$total_practicas = ($result) ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM pdfs WHERE estado='realizada' AND usuario_id = $usuario_id");
$practicas_completadas = ($result) ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM pdfs WHERE estado='no realizada' AND usuario_id = $usuario_id");
$practicas_pendientes = ($result) ? $result->fetch_assoc()['total'] : 0;

$practicas = $conn->query("SELECT id, nombre, fecha, estado FROM pdfs WHERE usuario_id = $usuario_id ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mis Reportes de Prácticas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Mis Reportes de Prácticas</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total de Prácticas</h5>
                    <p class="card-text"><?= $total_practicas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Prácticas Completadas</h5>
                    <p class="card-text"><?= $practicas_completadas ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Prácticas Pendientes</h5>
                    <p class="card-text"><?= $practicas_pendientes ?></p>
                </div>
            </div>
        </div>
    </div>

    <canvas id="graficoPracticas" height="100"></canvas>
    <script>
        var ctx = document.getElementById('graficoPracticas').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total', 'Completadas', 'Pendientes'],
                datasets: [{
                    label: 'Cantidad de Prácticas',
                    data: [<?= $total_practicas ?>, <?= $practicas_completadas ?>, <?= $practicas_pendientes ?>],
                    backgroundColor: ['blue', 'green', 'orange']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

    <h4 class="mt-5">Listado de Prácticas</h4>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $practicas->fetch_assoc()): ?>
            <tr id="practica-<?= $row['id'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= $row['fecha'] ?></td>
                <td class="estado"><?= ucfirst($row['estado']) ?></td>
                <td>
                    <?php if ($row['estado'] === 'no realizada'): ?>
                    <button class="btn btn-sm btn-success marcar-realizada" data-id="<?= $row['id'] ?>">Marcar como realizada</button>
                    <?php else: ?>
                    <span class="text-success">Realizada</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.marcar-realizada').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const row = document.getElementById('practica-' + id);
        const estadoCell = row.querySelector('.estado');

        fetch('marcar_realizada.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id)
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'ok') {
                estadoCell.textContent = 'Realizada';
                row.querySelector('td:last-child').innerHTML = '<span class="text-success">✔ Realizada</span>';
            } else {
                alert('Error al marcar como realizada');
            }
        })
        .catch(() => alert('Error en la solicitud'));
    });
});
</script>
</body>
</html>
