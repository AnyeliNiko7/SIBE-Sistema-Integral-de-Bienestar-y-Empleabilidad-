<?php
session_start();

// Verificar autenticación y permisos
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Consejería') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';
$query = "SELECT t.id_tutor, t.nombre, t.dni, t.correo, t.telefono, 
                 c.nombre AS carrera 
          FROM tutores t
          LEFT JOIN carrera c ON t.id_carrera = c.id_carrera
          WHERE t.estado = 'papelera'
          ORDER BY t.id_tutor ASC";
$resultado = $enlace->query($query);
if (isset($_GET['restaurar'])) {
    $id_tutor = intval($_GET['restaurar']);
    $stmt = $enlace->prepare("UPDATE tutores SET estado = 'activo' WHERE id_tutor = ?");
    $stmt->bind_param("i", $id_tutor);
    $stmt->execute();
    $stmt->close();
    header("location: papelera.php");
    exit();
}
if (isset($_GET['vaciar'])) {
    $stmt = $enlace->prepare("DELETE FROM tutores WHERE estado = 'papelera'");
    $stmt->execute();
    $stmt->close();
    header("location: papelera.php");
    exit();
}
$enlace->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Papelera</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/consejeria.css?v=<?php echo filemtime('../../public/css/consejeria.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideConsejeria.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <h2>Papelera de Tutores</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Carrera</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($fila = $resultado->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($fila['nombre']) ?></td>
                        <td><?= htmlspecialchars($fila['dni']) ?></td>
                        <td><?= htmlspecialchars($fila['correo']) ?></td>
                        <td><?= htmlspecialchars($fila['telefono']) ?></td>
                        <td><?= htmlspecialchars($fila['carrera']) ?></td>
                        <td>
                            <a href="papelera.php?restaurar=<?= $fila['id_tutor'] ?>" class="btn btn-primary">
                                <i class='bx bx-undo'></i> Restaurar
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="icon-buttons">
            <a href="papelera.php?vaciar=true" onclick="return confirm('¿Estás seguro de vaciar la papelera?');" class="vaciar">
                <i class='bx bx-trash-alt'></i> Vaciar Papelera
            </a>
            <a href="inicio.php" class="volver">
                <i class='bx bxs-left-arrow-circle'></i> Volver
            </a>
        </div>
    </main>
</body>

</html>