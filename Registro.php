<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Validar correo institucional
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@huixquilucan\.tecnm\.mx$/', $email)) {
        echo "<script>alert('Solo se permiten correos institucionales (@huixquilucan.tecnm.mx)'); window.history.back();</script>";
        exit;
    }

    // Verificar si el correo ya está registrado
    $verificar = $conn->prepare("SELECT id FROM tipodeusuarios WHERE email = ?");
    $verificar->bind_param("s", $email);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
        echo "<script>alert('Este correo ya está registrado.'); window.history.back();</script>";
        $verificar->close();
        $conn->close();
        exit;
    }
    $verificar->close();

    // Encriptar contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO tipodeusuarios (nombre, email, password, rol, estado) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $rol);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario registrado exitosamente.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error al registrar usuario: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Registro de Usuario</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Institucional</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-control" id="rol" name="rol" required>
                                    <option value="maestro">Maestro</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="jefe_carrera">Jefe de Carrera</option>
                                </select>
                            </div>
                            <div class="mb-3" id="carreraDiv" style="display:none;">
    <label for="carrera" class="form-label">Carrera que supervisará</label>
    <select class="form-control" id="carrera" name="carrera">
        <option value="">Seleccione una carrera</option>
        <option value="1">Ingeniería en Sistemas</option>
        <option value="2">Ingeniería Civil</option>
        <option value="3">Ingeniería Industrial</option>
        <option value="4">Licenciatura en Administración</option>
        <option value="5">Ingeniería en Mecatrónica</option>
        <option value="6">Licenciatura en Biología</option>
    </select>
</div>

<script>
document.getElementById('rol').addEventListener('change', function () {
    var carreraDiv = document.getElementById('carreraDiv');
    carreraDiv.style.display = this.value === 'jefe' ? 'block' : 'none';
});
</script>

                            <div class="text-center">
                                <button type="submit" class="btn btn-success w-100">Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>