<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM practicas WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $practica = $result->fetch_assoc();
} else {
    header("Location: index.php?error=ID no válido");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Práctica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <h2 class="mb-4">Editar Práctica</h2>
    
    <form action="actualizar_practica.php" method="POST" class="p-4 border rounded shadow-sm">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($practica['id']); ?>">

        <div class="mb-3">
            <label class="form-label">Nombre de la práctica</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($practica['nombre']); ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>

</body>
</html>