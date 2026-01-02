<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';

// Función para validar contraseña segura
function validarContrasenaSegura($contrasena) {
    // Longitud mínima: 8 caracteres, máxima: 16 caracteres
    if (strlen($contrasena) < 8 || strlen($contrasena) > 16) {
        return "La contraseña debe tener entre 8 y 16 caracteres.";
    }
    
    // Al menos una mayúscula
    if (!preg_match('/[A-Z]/', $contrasena)) {
        return "La contraseña debe contener al menos una letra mayúscula.";
    }
    
    // Al menos una minúscula
    if (!preg_match('/[a-z]/', $contrasena)) {
        return "La contraseña debe contener al menos una letra minúscula.";
    }
    
    // Al menos un número
    if (!preg_match('/[0-9]/', $contrasena)) {
        return "La contraseña debe contener al menos un número.";
    }
    
    // Al menos un símbolo especial
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $contrasena)) {
        return "La contraseña debe contener al menos un símbolo especial (!@#$%^&*()-_=+{};:,<.>).";
    }
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['correo'], $_POST['contraseña'], $_POST['area_id'])) {
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];
    $area_id = $_POST['area_id'];
    
    if (empty($usuario) || empty($correo) || empty($contraseña) || empty($area_id)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Validar contraseña segura
        $validacion = validarContrasenaSegura($contraseña);
        if ($validacion !== true) {
            $error = $validacion;
        } else {
            $usuario = mysqli_real_escape_string($enlace, $usuario);
            $correo = mysqli_real_escape_string($enlace, $correo);
            $contraseña = mysqli_real_escape_string($enlace, $contraseña);
            $area_id = (int)$area_id;
            
            $sqlCheckUsuarioCorreo = "SELECT usuario, correo FROM usuarios WHERE usuario = ? OR correo = ?";
            $stmtCheck = $enlace->prepare($sqlCheckUsuarioCorreo);
            $stmtCheck->bind_param("ss", $usuario, $correo);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows > 0) {
                $fila = $resultCheck->fetch_assoc();
                if ($fila['usuario'] === $usuario && $fila['correo'] === $correo) {
                    $error = "El usuario y el correo ya existen, intenta con otros.";
                } elseif ($fila['usuario'] === $usuario) {
                    $error = "El nombre de usuario ya existe. Intenta con uno nuevo.";
                } elseif ($fila['correo'] === $correo) {
                    $error = "El correo electrónico ya está registrado.";
                }
            } else {
                $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (usuario, correo, contrasena, area_id) VALUES (?, ?, ?, ?)";
                $stmt = $enlace->prepare($sql);
                $stmt->bind_param("sssi", $usuario, $correo, $contraseña_hash, $area_id);
                
                if ($stmt->execute()) {
                    $_SESSION['mensaje'] = "Usuario creado correctamente.";
                    $stmt->close();
                    header("location: inicio.php");
                    exit();
                } else {
                    $error = "Error al crear el usuario: " . $stmt->error;
                }
                $stmt->close();
            }
            $stmtCheck->close();
        }
    }
}

$sqlAreas = "SELECT * FROM areas";
$resultadoAreas = $enlace->query($sqlAreas);
if (!$resultadoAreas) {
    die("Error en la consulta de áreas: " . $enlace->error);
}
if ($resultadoAreas->num_rows == 0) {
    die("No se encontraron áreas en la base de datos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
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
        <section>
            <div class="titulo">
                <h2>Crear nuevo usuario</h2>
            </div>
            <form method="POST" action="" class="form-crear-usuario" id="formCrearUsuario">
                <div class="form-group">
                    <label for="usuario" class="form-label">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" class="form-input" maxlength="50" required
                        value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="correo" class="form-label">Correo:</label>
                    <input type="email" id="correo" name="correo" class="form-input" required
                        value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="contraseña" class="form-label">Contraseña:</label>
                    <div class="input-container">
                        <input type="password" id="contraseña" name="contraseña" class="form-input" maxlength="16" required
                            value="<?php echo isset($_POST['contraseña']) ? htmlspecialchars($_POST['contraseña']) : ''; ?>"
                            oninput="validarContrasena(this.value)"
                            placeholder="Ingrese contraseña (8-16 caracteres)">
                        <i id="togglePassword" class='bx bx-hide'></i>
                    </div>
                    
                    <!-- Indicadores de validación -->
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement invalid" id="reqLength">
                            <span class="requirement-icon">•</span>
                            <span>8-16 caracteres</span>
                        </div>
                        <div class="requirement invalid" id="reqUppercase">
                            <span class="requirement-icon">•</span>
                            <span>Al menos una mayúscula</span>
                        </div>
                        <div class="requirement invalid" id="reqLowercase">
                            <span class="requirement-icon">•</span>
                            <span>Al menos una minúscula</span>
                        </div>
                        <div class="requirement invalid" id="reqNumber">
                            <span class="requirement-icon">•</span>
                            <span>Al menos un número</span>
                        </div>
                        <div class="requirement invalid" id="reqSpecial">
                            <span class="requirement-icon">•</span>
                            <span>Al menos un símbolo especial (!@#$%^&*()-_=+{};:,<.>)</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="area_id" class="form-label">Área:</label>
                    <select id="area_id" name="area_id" class="form-select" required>
                        <option value="">Seleccione un Área</option>
                        <?php while ($fila = $resultadoAreas->fetch_assoc()): ?>
                            <option value="<?php echo $fila['id']; ?>"
                                <?php echo (isset($_POST['area_id']) && $_POST['area_id'] == $fila['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($fila['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Crear Usuario" class="form-submit" id="submitButton" disabled>
                </div>
            </form>
            <?php if (isset($error)): ?>
                <p id="error-message" style="color: red;text-align: center;"><?php echo $error; ?></p>
            <?php endif; ?>
        </section>
    </main>
    <script src="../../public/js/validaradmin.js" defer></script>
</body>
</html>