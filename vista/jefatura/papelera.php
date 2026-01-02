<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Jefatura') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';

$query = "SELECT o.id, o.apellidos_nombres, o.celular, o.colegio, 
                 c.nombre AS carrera 
          FROM orientacion_vocacional o
          LEFT JOIN carrera c ON o.id_carrera = c.id_carrera
          WHERE o.estado = 'papelera'
          ORDER BY o.id ASC";
$resultado = $enlace->query($query);
if (isset($_GET['restaurar'])) {
    $id = intval($_GET['restaurar']);
    $stmt = $enlace->prepare("UPDATE orientacion_vocacional SET estado = 'activo' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("location: papelera.php");
    exit();
}
if (isset($_GET['vaciar'])) {
    $stmt = $enlace->prepare("DELETE FROM orientacion_vocacional WHERE estado = 'papelera'");
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
    <link rel="stylesheet" href="../../public/css/jefatura.css?v=<?php echo filemtime('../../public/css/jefatura.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideJefatura.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <h2>Papelera de Orientación Vocacional</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Apellidos y Nombres</th>
                    <th>Celular</th>
                    <th>Colegio</th>
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
                        <td><?= htmlspecialchars($fila['apellidos_nombres']) ?></td>
                        <td><?= htmlspecialchars($fila['celular'] ?? '') ?></td>
                        <td><?= htmlspecialchars($fila['colegio']) ?></td>
                        <td><?= htmlspecialchars($fila['carrera'] ?? '') ?></td>
                        <td>
                            <a href="papelera.php?restaurar=<?= $fila['id'] ?>" class="btn btn-primary">
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