<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../inicio.php");
    exit();
}

include '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "UPDATE usuarios SET bloqueado = 'no', intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Usuario desbloqueado correctamente";
    } else {
        echo "Error al desbloquear el usuario: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Método no permitido";
}
?>