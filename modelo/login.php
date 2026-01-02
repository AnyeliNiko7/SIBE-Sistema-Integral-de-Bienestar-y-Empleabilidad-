<?php
function verificar_login($usuario, $contrasena, $area_id, $enlace) {
    $sql = "SELECT u.id, u.usuario, u.contrasena, u.correo, a.nombre AS area
            FROM usuarios u
            JOIN areas a ON u.area_id = a.id
            WHERE u.usuario = ? AND u.area_id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("si", $usuario, $area_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($contrasena, $user['contrasena'])) {
            return $user; // Éxito
        } else {
            return false; // Contraseña incorrecta
        }
    } else {
        return false; // Usuario no encontrado
    }
}
?>
