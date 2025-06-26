<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_POST['usuario_id'];
    $accion = $_POST['accion'];

    if ($accion === 'aceptar') {
        $nuevo_rol = $_POST['nuevo_rol'] ?? '';
        if (!in_array($nuevo_rol, ['maestro', 'administrador', 'jefe_carrera'])) {
            header("Location: gestionar_usuarios.php?estado=pendientes&mensaje=Rol no válido");
            exit();
        }

        $stmt = $conn->prepare("UPDATE tipodeusuarios SET estado = 'activo', rol = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_rol, $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: gestionar_usuarios.php?estado=pendientes&mensaje=Usuario aceptado correctamente");
        exit();

    } elseif ($accion === 'rechazar') {
        $stmt = $conn->prepare("UPDATE tipodeusuarios SET estado = 'rechazado' WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: gestionar_usuarios.php?estado=pendientes&mensaje=Usuario rechazado");
        exit();

    } elseif ($accion === 'eliminar') {
        // Eliminar usuario de la base de datos
        $stmt = $conn->prepare("DELETE FROM tipodeusuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: gestionar_usuarios.php?estado=pendientes&mensaje=Usuario eliminado correctamente");
        exit();
    }
}
?>
