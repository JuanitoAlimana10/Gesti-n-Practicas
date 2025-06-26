<?php
session_start();
include("conexion.php");

$usuario_id = $_SESSION['usuario_id'];
$carrera_id = $_POST['carrera_id'];
$materia_id = $_POST['materia_id'];

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
    $nombre = $_FILES['archivo']['name'];
    $ruta_temporal = $_FILES['archivo']['tmp_name'];
    $ruta_destino = 'pdfs/' . $nombre;

    if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
        $sql = "INSERT INTO pdfs (id,nombre, ruta, fecha, usuario_id, carrera, materia, estado) 
                VALUES ('$id','$nombre', '$ruta','$fecha',' $usuario_id', '$carrera_id', '$materia_id', 'no realizada')";
        if (mysqli_query($conn, $sql)) {
            echo "Archivo subido correctamente.";
            header("Location: subir_pdf.php?exito=1");
        } else {
            echo "Error al guardar en la base de datos.";
        }
    } else {
        echo "Error al mover el archivo.";
    }
} else {
    echo "Archivo no válido.";  
}
?>