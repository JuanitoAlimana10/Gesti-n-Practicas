<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $rol = trim($_POST['rol']);

    try {
        // Preparar la consulta
        $sql = "INSERT INTO tipodeusuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        // Vincular parámetros
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; font-weight: bold; text-align: center;'>Usuario registrado exitosamente.</div>";
        } else {
            throw new Exception("Error al registrar usuario: " . $stmt->error);
        }
        
        // Cerrar la consulta
        $stmt->close();
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; font-weight: bold; text-align: center;'>" . $e->getMessage() . "</div>";
    }

    // Cerrar la conexión
    $conn->close();
}
?><!DOCTYPE html><html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(24, 137, 49);
        }
    </style>    
</head>
<body>
    <h2>Agregar Nuevo Usuario</h2>
    <form method="POST" action="">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Correo Electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <select name="rol" required>
            <option value="administrador">Administrador</option>
            <option value="usuario">Jefe de Carrera</option>
            <option value="usuario">Docente</option>
        </select>
        <button type="submit">Registrar Usuario</button>
    </form>
</body>
</html>