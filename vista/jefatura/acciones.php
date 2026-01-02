<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Jefatura') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $apellidos_nombres = trim($_POST['apellidos_nombres']);
    $celular = trim($_POST['celular']);
    $id_carrera = $_POST['id_carrera'];
    $colegio = trim($_POST['colegio']);

    // Validaciones
    if (strlen($apellidos_nombres) > 255) {
        $error = "Los apellidos y nombres no pueden exceder los 255 caracteres.";
    } elseif (strlen($celular) != 9 || !ctype_digit($celular)) {
        $error = "El número de celular debe ser un número de 9 dígitos.";
    } else {
        // Insertar en la base de datos
        $sql = "INSERT INTO orientacion_vocacional (apellidos_nombres, celular, id_carrera, colegio)
                VALUES (?, ?, ?, ?)";

        $stmt = $enlace->prepare($sql);
        $stmt->bind_param("ssis", $apellidos_nombres, $celular, $id_carrera, $colegio);
        $stmt->execute();
        $stmt->close();

        header("Location: inicio.php");
        exit();
    }
}
$sqlCarreras = "SELECT id_carrera, nombre FROM carrera";
$carreras = $enlace->query($sqlCarreras);

if (!$carreras) {
    die("Error al consultar las carreras: " . $enlace->error);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Registro - Orientación Vocacional</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/jefatura.css?v=<?php echo filemtime('../../public/css/jefatura.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideJefatura.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <div class="titulo">
                <h2>Crear Nuevo Registro - Orientación Vocacional</h2>
            </div>
            <form method="POST" action="" class="formulario">
                <?php if (!empty($error)): ?>
                    <p style="color: red"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="apellidos_nombres">Apellidos y Nombres:</label>
                        <input type="text" id="apellidos_nombres" name="apellidos_nombres" placeholder="Ej. Juan Pérez" maxlength="255" required>
                    </div>
                    <div class="columna-formulario">
                        <label for="celular">Celular:</label>
                        <input type="text" id="celular" name="celular" placeholder="Ej. 987654321" maxlength="9" required>
                    </div>
                </div>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="id_carrera">Carrera:</label>
                        <select id="id_carrera" name="id_carrera" required>
                            <option value="">Seleccione una carrera</option>
                            <?php while ($carrera = $carreras->fetch_assoc()): ?>
                                <option value="<?php echo $carrera['id_carrera']; ?>">
                                    <?php echo htmlspecialchars($carrera['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="columna-formulario">
                        <label for="colegio">Colegio:</label>
                        <input type="text" id="colegio" name="colegio" placeholder="Ej. Colegio Nacional" maxlength="255" required>
                    </div>
                </div>
                <div class="fila-formulario">
                    <input type="submit" value="Crear" class="boton-crear">
                </div>
            </form>
        </section>
    </main>
</body>

</html>