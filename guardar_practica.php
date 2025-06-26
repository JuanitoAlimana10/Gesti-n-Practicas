<?php
session_start();
if ($_SESSION['rol'] !== 'Docente') {
    echo "Acceso denegado.";
    exit();
}

?>
<form action="guardar_practica.php" method="POST">
    <input type="text" name="nombre" placeholder="Nombre de la práctica" required>
    <button type="submit">Guardar</button>
</form>