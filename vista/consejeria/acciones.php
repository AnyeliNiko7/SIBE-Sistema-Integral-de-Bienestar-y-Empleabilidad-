<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Consejería') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';

$error = "";
$sqlCarreras = "SELECT id_carrera, nombre FROM carrera";
$carreras = $enlace->query($sqlCarreras);
if (!$carreras) {
    die("Error al consultar las carreras: " . $enlace->error);
}
$sqlSemestres = "SELECT id_semestre, nombre FROM semestres";
$semestres = $enlace->query($sqlSemestres);

if (!$semestres) {
    die("Error al consultar los semestres: " . $enlace->error);
}
$sqlTurnos = "SELECT id_turno, nombre FROM turnos";
$turnos = $enlace->query($sqlTurnos);
if (!$turnos) {
    die("Error al consultar los turnos: " . $enlace->error);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $id_carrera = $_POST['id_carrera'];
    $id_semestre = $_POST['id_semestre'];
    $id_turno = $_POST['id_turno'];
    if (strlen($nombre) > 50) {
        $error = "El nombre no puede exceder los 50 caracteres.";
    } elseif (strlen($dni) != 8 || !ctype_digit($dni)) {
        $error = "El DNI debe ser un número de 8 dígitos.";
    } elseif (strlen($telefono) != 9 || !ctype_digit($telefono)) {
        $error = "El número de teléfono debe ser un número de 9 dígitos.";
    } else {
        $stmt = $enlace->prepare("SELECT id_tutor FROM tutores WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "El DNI ya existe, intente con otro.";
        } else {
            $sql = "INSERT INTO tutores (nombre, dni, correo, telefono, id_carrera, id_semestre, id_turno)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $enlace->prepare($sql);
            $stmt->bind_param("ssssiii", $nombre, $dni, $correo, $telefono, $id_carrera, $id_semestre, $id_turno);
            $stmt->execute();
            $stmt->close();

            header("Location: inicio.php"); // Redirigir a la lista después de insertar
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Registro - Tutores</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/consejeria.css?v=<?php echo filemtime('../../public/css/consejeria.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <link rel="icon" href="../../public/img/icono2.png">
</head>

<body>
    <?php include '../../includes/asideConsejeria.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <div class="titulo">
                <h2>Crear Nuevo Registro - Tutor</h2>
            </div>
            <form method="POST" action="" class="formulario">
                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" maxlength="50" required>
                    </div>
                    <div class="columna-formulario">
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" placeholder="Ej. 12345678" maxlength="8" required>
                    </div>
                </div>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" placeholder="Ej. correo@dominio.com" required>
                    </div>
                    <div class="columna-formulario">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" placeholder="Ej. 987654321" maxlength="9" required>
                    </div>
                </div>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="id_semestre">Semestre:</label>
                        <select id="id_semestre" name="id_semestre" required>
                            <option value="">Seleccione un semestre</option>
                            <?php while ($semestre = $semestres->fetch_assoc()): ?>
                                <option value="<?php echo $semestre['id_semestre']; ?>">
                                    <?php echo htmlspecialchars($semestre['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="fila-formulario">
                    <div class="columna-formulario">
                        <label for="id_turno">Turno:</label>
                        <select id="id_turno" name="id_turno" required>
                            <option value="">Seleccione un turno</option>
                            <?php while ($turno = $turnos->fetch_assoc()): ?>
                                <option value="<?php echo $turno['id_turno']; ?>">
                                    <?php echo htmlspecialchars($turno['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
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
                <div class="fila-formulario">
                    <input type="submit" value="Crear" class="boton-crear">
                </div>
            </form>
        </section>
    </main>
</body>

</html>