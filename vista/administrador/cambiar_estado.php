<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../inicio.php");
    exit();
}

include '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['estado'];
    
    $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("si", $estado, $id);
    
    if ($stmt->execute()) {
        echo "Estado actualizado correctamente";
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Método no permitido";
}
?>