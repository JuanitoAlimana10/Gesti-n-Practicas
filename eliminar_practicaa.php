<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['id'])) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['ruta'])) {
    $id = intval($_POST['id']);
    $ruta = $_POST['ruta'];
    $usuario_id = $_SESSION['id'];

    // Validar que la práctica pertenece al usuario actual
    $stmt = $conn->prepare("SELECT * FROM pdfs WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        // Eliminar el archivo si existe
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // Eliminar el registro de la base de datos
        $stmt = $conn->prepare("DELETE FROM pdfs WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: listado_practicas.php?mensaje=eliminado");
            exit;
        } else {
            echo "Error al eliminar la práctica.";
        }
    } else {
        echo "No tienes permiso para eliminar esta práctica.";
    }
} else {
    echo "Solicitud inválida.";
}
