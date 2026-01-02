<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';

function validarContrasenaSegura($contrasena) {
    if (strlen($contrasena) < 8 || strlen($contrasena) > 16) {
        return "La contraseña debe tener entre 8 y 16 caracteres.";
    }
    if (!preg_match('/[A-Z]/', $contrasena)) {
        return "La contraseña debe contener al menos una letra mayúscula.";
    }
    if (!preg_match('/[a-z]/', $contrasena)) {
        return "La contraseña debe contener al menos una letra minúscula.";
    }
    if (!preg_match('/[0-9]/', $contrasena)) {
        return "La contraseña debe contener al menos un número.";
    }
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $contrasena)) {
        return "La contraseña debe contener al menos un símbolo especial (!@#$%^&*()-_=+{};:,<.>).";
    }
    
    return true;
}

// MODIFICACIÓN: Excluir al usuario de emergencia de la lista
$sql = "SELECT u.id, u.usuario, u.correo, u.area_id, a.nombre AS area, 
               u.estado, u.fecha_ingreso, u.ultima_conexion, u.bloqueado, 
               u.intentos_fallidos, u.fecha_bloqueo
        FROM usuarios u
        JOIN areas a ON u.area_id = a.id
        WHERE u.usuario != 'jube792'  -- Excluir usuario de emergencia
        ORDER BY u.id ASC"; 
$resultado = $enlace->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $enlace->error);
}
$sql_areas = "SELECT * FROM areas";
$resultado_areas = $enlace->query($sql_areas);

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario: " . $stmt->error;
    }
    $stmt->close();
    header("location: inicio.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['contrasena']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $contrasena = $_POST['contrasena'];
        
        $validacion = validarContrasenaSegura($contrasena);
        if ($validacion !== true) {
            $_SESSION['error'] = $validacion;
            header("location: inicio.php");
            exit();
        }
        
        if (!empty($contrasena)) {
            $contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
            $stmt = $enlace->prepare($sql);
            $stmt->bind_param("si", $contrasena, $id);
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Contraseña actualizada correctamente.";
                $stmt->close();
                header("location: inicio.php");
                exit();
            } else {
                $_SESSION['error'] = "Error al actualizar la contraseña: " . $stmt->error;
                header("location: inicio.php");
                exit();
            }
        }
    }
    if (isset($_POST['editar_usuario']) && isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $usuario = $_POST['edit_usuario'];
        $correo = $_POST['edit_correo'];
        $area_id = $_POST['edit_area_id'];
        
        $sql_check = "SELECT id FROM usuarios WHERE (usuario = ? OR correo = ?) AND id != ?";
        $stmt_check = $enlace->prepare($sql_check);
        $stmt_check->bind_param("ssi", $usuario, $correo, $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $_SESSION['error'] = "El nombre de usuario o correo electrónico ya están en uso.";
            $stmt_check->close();
            header("location: inicio.php");
            exit();
        }
        $stmt_check->close();
        
        $sql_update = "UPDATE usuarios SET usuario = ?, correo = ?, area_id = ? WHERE id = ?";
        $stmt_update = $enlace->prepare($sql_update);
        $stmt_update->bind_param("ssii", $usuario, $correo, $area_id, $id);
        
        if ($stmt_update->execute()) {
            $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
            $stmt_update->close();
            header("location: inicio.php");
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar el usuario: " . $stmt_update->error;
            $stmt_update->close();
            header("location: inicio.php");
            exit();
        }
    }
}
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// MODIFICACIÓN: Mostrar alerta si está en modo emergencia
if (isset($_SESSION['es_emergencia']) && $_SESSION['es_emergencia'] === true) {
    $emergencia_alert = '<div class="emergency-alert" style="background: #ffeb3b; color: #333; padding: 10px; text-align: center; border-left: 4px solid #ff9800; margin-bottom: 20px;">
                            <strong>⚠ MODO EMERGENCIA ACTIVO</strong> - Estás usando la cuenta de emergencia
                         </div>';
} else {
    $emergency_alert = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuarios</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo filemtime('../../public/css/admin.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>
<body>
    <?php include '../../includes/asideAdmin.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <div class="titulo">
            <h2>Registros de usuarios</h2>
        </div>
        
        <?php echo $emergency_alert; ?>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <table id="tablaRegistros" border="1">
            <thead>
                <tr>
                    <th>Nº</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Área</th>
                    <th>Estado</th>
                    <th>Fecha Ingreso</th>
                    <th>Última Conexión</th>
                    <th>Bloqueado</th>
                    <th>Intentos Fallidos</th>
                    <th>Tiempo Bloqueo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $contador = 1;
                while ($fila = $resultado->fetch_assoc()): 
                    $fecha_ingreso = $fila['fecha_ingreso'] ? date('d/m/Y H:i', strtotime($fila['fecha_ingreso'])) : 'N/A';
                    $ultima_conexion = $fila['ultima_conexion'] ? date('d/m/Y H:i', strtotime($fila['ultima_conexion'])) : 'Nunca';
                    
                    $tiempo_bloqueo = 'N/A';
                    if ($fila['bloqueado'] === 'si' && $fila['fecha_bloqueo']) {
                        $fecha_bloqueo = new DateTime($fila['fecha_bloqueo']);
                        $fecha_actual = new DateTime();
                        $diferencia = $fecha_actual->getTimestamp() - $fecha_bloqueo->getTimestamp();
                        $minutos_restantes = 30 - floor($diferencia / 60);
                        
                        if ($minutos_restantes > 0) {
                            $tiempo_bloqueo = $minutos_restantes . ' min';
                        } else {

                            $sql_update = "UPDATE usuarios SET bloqueado = 'no', intentos_fallidos = 0, fecha_bloqueo = NULL WHERE id = ?";
                            $stmt_update = $enlace->prepare($sql_update);
                            $stmt_update->bind_param("i", $fila['id']);
                            $stmt_update->execute();
                            $stmt_update->close();
                            $tiempo_bloqueo = 'Expirado';
                        }
                    }
                ?>
                    <tr>
                        <td><?php echo $contador++; ?></td>
                        <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['area']); ?></td>
                        <td class="estado-<?php echo $fila['estado']; ?>">
                            <?php echo ucfirst($fila['estado']); ?>
                        </td>
                        <td class="fecha-conexion"><?php echo $fecha_ingreso; ?></td>
                        <td class="fecha-conexion"><?php echo $ultima_conexion; ?></td>
                        <td class="bloqueado-<?php echo $fila['bloqueado']; ?>">
                            <?php echo ucfirst($fila['bloqueado']); ?>
                        </td>
                        <td class="intentos-fallidos">
                            <?php echo $fila['intentos_fallidos']; ?>
                        </td>
                        <td class="tiempo-bloqueo">
                            <?php echo $tiempo_bloqueo; ?></td>
                        <td>
                            <div class="actions-container">
                                <button class="btn-icon btn-edit-user" 
                                        title="Editar Usuario"
                                        onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($fila)); ?>)">
                                    <i class='bx bx-edit-alt'></i>
                                </button>
                                
                                <button class="btn-icon btn-edit" 
                                        title="Cambiar Contraseña"
                                        onclick="abrirModalContrasena(<?php echo htmlspecialchars(json_encode($fila)); ?>)">
                                    <i class='bx bx-key'></i>
                                </button>
                                
                                <?php if ($fila['area'] !== 'Administrador'): ?>
                                    <button class="btn-icon <?php echo $fila['estado'] == 'activo' ? 'btn-status-inactive' : 'btn-status-active'; ?>" 
                                            title="<?php echo $fila['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>"
                                            onclick="cambiarEstado(<?php echo $fila['id']; ?>, '<?php echo $fila['estado']; ?>', '<?php echo $fila['area']; ?>')">
                                        <i class='bx <?php echo $fila['estado'] == 'activo' ? 'bx-user-x' : 'bx-user-check'; ?>'></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-icon btn-disabled" title="No se puede desactivar Administrador">
                                        <i class='bx bx-user-check'></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($fila['bloqueado'] === 'si'): ?>
                                    <button class="btn-icon btn-unlock" 
                                            title="Desbloquear Usuario"
                                            onclick="desbloquearUsuario(<?php echo $fila['id']; ?>)">
                                        <i class='bx bx-lock-open'></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-icon btn-disabled" title="Usuario No Bloqueado">
                                        <i class='bx bx-lock-open-alt'></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($fila['area'] !== 'Administrador'): ?>
                                    <a class="btn-icon btn-delete" 
                                       title="Eliminar Usuario"
                                       href="inicio.php?eliminar=<?php echo $fila['id']; ?>"
                                       onclick="return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn-icon btn-disabled" title="No se puede eliminar Administrador">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="modalContrasena" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModalContrasena()">&times;</span>
                <h2>Cambiar Contraseña de Usuario</h2>
                <form id="formContrasena" method="POST" action="inicio.php">
                    <input type="hidden" id="id_contrasena" name="id">

                    <div class="form-group-modal">
                        <label for="contrasena">Nueva Contraseña:</label>
                        <div class="input-container">
                            <input type="password" id="contrasena" name="contrasena" maxlength="16" required 
                                   placeholder="Ingrese nueva contraseña (8-16 caracteres)"
                                   oninput="validarContrasena(this.value)">
                            <i id="togglePasswordModal" class='bx bx-hide'></i>
                        </div>

                        <div class="password-requirements" id="passwordRequirements">
                            <div class="requirement invalid" id="reqLength">
                                <span>8-16 caracteres</span>
                            </div>
                            <div class="requirement invalid" id="reqUppercase">
                                <span>Al menos una mayúscula</span>
                            </div>
                            <div class="requirement invalid" id="reqLowercase">
                                <span>Al menos una minúscula</span>
                            </div>
                            <div class="requirement invalid" id="reqNumber">
                                <span>Al menos un número</span>
                            </div>
                            <div class="requirement invalid" id="reqSpecial">
                                <span>Al menos un símbolo especial (!@#$%^&*()-_=+{};:,<.>)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-update" id="submitButtonContrasena" disabled>
                            <i class='bx bx-key'></i> Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modalEditarUsuario" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModalEditar()">&times;</span>
                <h2>Editar Usuario</h2>
                <form id="formEditarUsuario" method="POST" action="inicio.php">
                    <input type="hidden" id="edit_id" name="edit_id">
                    <input type="hidden" name="editar_usuario" value="1">

                    <div class="form-group-modal">
                        <label for="edit_usuario">Usuario:</label>
                        <input type="text" id="edit_usuario" name="edit_usuario" maxlength="50" required>
                    </div>

                    <div class="form-group-modal">
                        <label for="edit_correo">Correo:</label>
                        <input type="email" id="edit_correo" name="edit_correo" required>
                    </div>

                    <div class="form-group-modal">
                        <label for="edit_area_id">Área:</label>
                        <select id="edit_area_id" name="edit_area_id" required>
                            <option value="">Seleccione un Área</option>
                            <?php 
                            $resultado_areas->data_seek(0);
                            while ($area = $resultado_areas->fetch_assoc()): ?>
                                <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-update">
                            <i class='bx bx-save'></i> Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="../../public/js/validaradmin.js" defer></script>
</body>
</html>