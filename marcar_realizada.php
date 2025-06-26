<?php
include 'conexion.php';
session_start();

// Solo permitir acceso a usuarios con sesión iniciada
if (!isset($_SESSION['id'])) {
    echo "error";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $pdf_id = intval($_POST['id']);
    $usuario_id = $_SESSION['id'];

    // Verifica que el PDF pertenezca al usuario logueado
    $stmt = $conn->prepare("SELECT id FROM pdfs WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pdf_id, $usuario_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Actualiza el estado a 'realizada'
        $update = $conn->prepare("UPDATE pdfs SET estado = 'realizada' WHERE id = ?");
        $update->bind_param("i", $pdf_id);
        if ($update->execute()) {
            echo "ok";
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>
