<?php
include './config/conexion.php';
session_start();
// Headers de seguridad
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net data:;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=63072000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Función para verificar usuario de emergencia (oculto)
function verificarUsuarioEmergencia($enlace, $usuario, $contrasena, $area_id) {
    // Solo verificar si el área es Administrador (id=5)
    if ($area_id != 5) {
        return false;
    }
    
    // Consulta para usuario de emergencia
    $sql = "SELECT u.id, u.usuario, u.contrasena, u.correo, a.nombre AS area, 
                   u.estado, u.bloqueado, u.intentos_fallidos, u.fecha_bloqueo
            FROM usuarios u
            JOIN areas a ON u.area_id = a.id
            WHERE u.usuario = ? AND u.area_id = ? AND u.estado = 'activo'";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("si", $usuario, $area_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar si está bloqueado
        if ($user['bloqueado'] === 'si') {
            $fecha_bloqueo = new DateTime($user['fecha_bloqueo']);
            $fecha_actual = new DateTime();
            $diferencia = $fecha_actual->getTimestamp() - $fecha_bloqueo->getTimestamp();
            $minutos_restantes = 30 - floor($diferencia / 60);
            
            if ($minutos_restantes > 0) {
                $_SESSION['error'] = "Usuario bloqueado. Intente nuevamente en " . $minutos_restantes . " minutos.";
                $stmt->close();
                return false;
            } else {
                // Desbloquear automáticamente después de 30 minutos
                $sql_desbloquear = "UPDATE usuarios SET bloqueado = 'no', intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
                $stmt_desbloquear = $enlace->prepare($sql_desbloquear);
                $stmt_desbloquear->bind_param("i", $user['id']);
                $stmt_desbloquear->execute();
                $stmt_desbloquear->close();
            }
        }
        
        // Verificar contraseña
        if (password_verify($contrasena, $user['contrasena'])) {
            // Login exitoso
            $sql_update = "UPDATE usuarios SET ultima_conexion = NOW(), intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
            $stmt_update = $enlace->prepare($sql_update);
            $stmt_update->bind_param("i", $user['id']);
            $stmt_update->execute();
            $stmt_update->close();
            
            $_SESSION['id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['correo'] = $user['correo'];
            $_SESSION['area'] = $user['area'];
            $_SESSION['es_emergencia'] = true; // Marcar como sesión de emergencia

            header("location: vista/administrador/inicio.php");
            exit();
        } else {
            // Incrementar intentos fallidos para usuario de emergencia también
            $nuevos_intentos = $user['intentos_fallidos'] + 1;
            $bloqueado = ($nuevos_intentos >= 5) ? 'si' : 'no';
            $fecha_bloqueo = ($bloqueado === 'si') ? date('Y-m-d H:i:s') : null;
            
            $sql_update = "UPDATE usuarios SET intentos_fallidos = ?, bloqueado = ?, fecha_bloqueo = ? WHERE id = ?";
            $stmt_update = $enlace->prepare($sql_update);
            $stmt_update->bind_param("issi", $nuevos_intentos, $bloqueado, $fecha_bloqueo, $user['id']);
            $stmt_update->execute();
            $stmt_update->close();

            if ($bloqueado === 'si') {
                $_SESSION['error'] = "Usuario bloqueado por 30 minutos debido a múltiples intentos fallidos.";
            } else {
                $intentos_restantes = 5 - $nuevos_intentos;
                $_SESSION['error'] = "Contraseña incorrecta. Intentos restantes: " . $intentos_restantes;
            }
        }
    }
    
    $stmt->close();
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $area_id = $_POST['area'];

    // Consultar si el usuario existe con el usuario y el área seleccionada
    $sql = "SELECT u.id, u.usuario, u.contrasena, u.correo, a.nombre AS area, 
                   u.estado, u.bloqueado, u.intentos_fallidos, u.fecha_bloqueo
            FROM usuarios u
            JOIN areas a ON u.area_id = a.id
            WHERE u.usuario = ? AND u.area_id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("si", $usuario, $area_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar si el usuario está activo
        if ($user['estado'] === 'inactivo') {
            $_SESSION['error'] = "Este usuario está desactivado. Contacte al administrador.";
        }
        // Verificar si el usuario está bloqueado y el tiempo de bloqueo
        elseif ($user['bloqueado'] === 'si') {
            $fecha_bloqueo = new DateTime($user['fecha_bloqueo']);
            $fecha_actual = new DateTime();
            $diferencia = $fecha_actual->getTimestamp() - $fecha_bloqueo->getTimestamp();
            $minutos_restantes = 30 - floor($diferencia / 60);
            
            if ($minutos_restantes > 0) {
                $_SESSION['error'] = "Usuario bloqueado. Intente nuevamente en " . $minutos_restantes . " minutos.";
            } else {
                // Desbloquear automáticamente después de 30 minutos
                $sql_desbloquear = "UPDATE usuarios SET bloqueado = 'no', intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
                $stmt_desbloquear = $enlace->prepare($sql_desbloquear);
                $stmt_desbloquear->bind_param("i", $user['id']);
                $stmt_desbloquear->execute();
                $stmt_desbloquear->close();
                
                // Reintentar login
                if (password_verify($contrasena, $user['contrasena'])) {
                    // Login exitoso después del desbloqueo automático
                    $sql_update = "UPDATE usuarios SET ultima_conexion = NOW(), intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
                    $stmt_update = $enlace->prepare($sql_update);
                    $stmt_update->bind_param("i", $user['id']);
                    $stmt_update->execute();
                    $stmt_update->close();

                    $_SESSION['id'] = $user['id'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['correo'] = $user['correo'];
                    $_SESSION['area'] = $user['area'];

                    // Redirigir según el área
                    switch ($user['area']) {
                        case 'Tópico':
                            header("location: vista/topico/inicio.php");
                            break;
                        case 'Psicología':
                            header("location: vista/psicologia/inicio.php");
                            break;
                        case 'Consejería':
                            header("location: vista/consejeria/inicio.php");
                            break;
                        case 'Jefatura':
                            header("location: vista/jefatura/inicio.php");
                            break;
                        case 'Administrador':
                            header("location: vista/administrador/inicio.php");
                            break;
                        default:
                            $_SESSION['error'] = "Área no reconocida.";
                            break;
                    }
                    exit();
                } else {
                    // Contraseña incorrecta después del desbloqueo
                    $nuevos_intentos = 1;
                    $sql_update = "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?";
                    $stmt_update = $enlace->prepare($sql_update);
                    $stmt_update->bind_param("ii", $nuevos_intentos, $user['id']);
                    $stmt_update->execute();
                    $stmt_update->close();
                    
                    $_SESSION['error'] = "Contraseña incorrecta. Intentos restantes: 4";
                }
            }
        }
        // Verificar contraseña
        elseif (password_verify($contrasena, $user['contrasena'])) {
            // Reiniciar intentos fallidos y actualizar última conexión
            $sql_update = "UPDATE usuarios SET ultima_conexion = NOW(), intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
            $stmt_update = $enlace->prepare($sql_update);
            $stmt_update->bind_param("i", $user['id']);
            $stmt_update->execute();
            $stmt_update->close();

            // Guardar datos en sesión
            $_SESSION['id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['correo'] = $user['correo'];
            $_SESSION['area'] = $user['area'];

            // Redirigir según el área
            switch ($user['area']) {
                case 'Tópico':
                    header("location: vista/topico/inicio.php");
                    break;
                case 'Psicología':
                    header("location: vista/psicologia/inicio.php");
                    break;
                case 'Consejería':
                    header("location: vista/consejeria/inicio.php");
                    break;
                case 'Jefatura':
                    header("location: vista/jefatura/inicio.php");
                    break;
                case 'Administrador':
                    header("location: vista/administrador/inicio.php");
                    break;
                default:
                    $_SESSION['error'] = "Área no reconocida.";
                    break;
            }
            exit();
        } else {
            // Incrementar intentos fallidos
            $nuevos_intentos = $user['intentos_fallidos'] + 1;
            $bloqueado = ($nuevos_intentos >= 5) ? 'si' : 'no';
            $fecha_bloqueo = ($bloqueado === 'si') ? date('Y-m-d H:i:s') : null;
            
            $sql_update = "UPDATE usuarios SET intentos_fallidos = ?, bloqueado = ?, fecha_bloqueo = ? WHERE id = ?";
            $stmt_update = $enlace->prepare($sql_update);
            $stmt_update->bind_param("issi", $nuevos_intentos, $bloqueado, $fecha_bloqueo, $user['id']);
            $stmt_update->execute();
            $stmt_update->close();

            if ($bloqueado === 'si') {
                $_SESSION['error'] = "Usuario bloqueado por 30 minutos debido a múltiples intentos fallidos.";
            } else {
                $intentos_restantes = 5 - $nuevos_intentos;
                $_SESSION['error'] = "Contraseña incorrecta. Intentos restantes: " . $intentos_restantes;
            }
        }
    } else {
        // Si no encuentra usuario normal, verificar si es usuario de emergencia
        if (!verificarUsuarioEmergencia($enlace, $usuario, $contrasena, $area_id)) {
            $_SESSION['error'] = "Usuario, contraseña o área son incorrectos.";
        }
    }
    
    $stmt->close();
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="public/img/icono.png">
    <link rel="stylesheet" href="./public/css/estilos.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="./public/js/login.js"></script>
</head>
<body>
    <main>
        <section>
            <div class="login-container">
                <div class="header-container">
                    <h2>Iniciar Sesión</h2>
                </div>
                <form action="index.php" method="POST">
                    <div class="input-container">
                        <i class='bx bxs-user'></i>
                        <input type="text" id="usuario" placeholder="" name="usuario" maxlength="50" required
                            value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                        <label for="usuario">Usuario</label>
                    </div>
                    <div class="input-container">
                        <i class='bx bxs-lock'></i>
                        <input type="password" id="contrasena" placeholder="" maxlength="16" name="contrasena" required>
                        <label for="contrasena">Contraseña</label>
                        <i id="togglePassword" class='bx bx-hide'></i>
                    </div>

                    <div class="cb_area">
                        <select name="area" id="area" required>
                            <option value="">Seleccione</option>
                            <?php
                            $sql = "SELECT id, nombre FROM areas";
                            $result = $enlace->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['area']) && $_POST['area'] == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['nombre']) . '</option>';
                                }
                            } else {
                                echo '<option value="">No hay áreas disponibles</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <input type="submit" value="Iniciar Sesión">
                </form>
                <?php if (isset($error)) { ?>
                    <p id="error-message"><?php echo $error; ?></p>
                <?php } ?>
                <div class="login-footer">
                    © Todos los derechos reservados | Diseñado por Luminid
                </div>
            </div>
        </section>
        <section>
            <div class="image_fondo">
                <h1 class="bienbenida">BIENVENIDO AL SISTEMA<br> INTEGRAL DE BIENESTAR<br> Y EMPLEABILIDAD</h1>
                <p class="description">Por favor, inicie sesión con sus credenciales para acceder a su cuenta.</p>
                <div class="logo_instituto"><img class="logo_imagen" src="./public/img/instituto.webp" alt="Logo instituto"></div>
            </div>
        </section>
    </main>
</body>
</html>