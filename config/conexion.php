<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$nombre_base_datos = "sibe"; 
$puerto = 3309; // cambiar de puerto si es necesario

$enlace = new mysqli($servidor, $usuario, $contrasena, $nombre_base_datos, $puerto);

if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

$enlace->set_charset("utf8");
?>
