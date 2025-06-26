<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}
include 'conexion.php';

$result = $conn->query("SELECT * FROM documentos");
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Documentos PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Bienvenido, <?php echo htmlspecialchars($nombre); ?> (<?php echo ucfirst($rol); ?>)</h2>
        
        <?php if ($rol === 'maestro') { ?>
            <div class="text-end mb-3">
                <a href="subir_pdf.php" class="btn btn-primary">Subir nuevo PDF</a>
            </div>
        <?php } ?>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Título</th>
                    <th>Visualizar</th>
                    <th>Descargar</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                    <td><a href="pdfs/<?php echo $row['archivo']; ?>" target="_blank" class="btn btn-success btn-sm">Ver</a></td>
                    <td><a href="pdfs/<?php echo $row['archivo']; ?>" download class="btn btn-secondary btn-sm">Descargar</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-dark">Volver al Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>

