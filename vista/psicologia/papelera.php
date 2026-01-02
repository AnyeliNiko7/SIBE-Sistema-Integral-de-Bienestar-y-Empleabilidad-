<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';
$query = "SELECT rp.id, rp.dni, rp.apellidos_nombres, rp.edad, c.nombre AS carrera, 
                 s.nombre AS semestre, t.nombre AS turno, 
                 rp.motivo_consulta, rp.ultima_actualizacion
          FROM registros_psicologia rp
          JOIN carrera c ON rp.id_carrera = c.id_carrera
          JOIN semestres s ON rp.id_semestre = s.id_semestre
          JOIN turnos t ON rp.id_turno = t.id_turno
          WHERE rp.estado = 'papelera'
          ORDER BY rp.fecha DESC";
$resultado = $enlace->query($query);
if (isset($_GET['restaurar'])) {
    $id = intval($_GET['restaurar']);
    $stmt = $enlace->prepare("UPDATE registros_psicologia SET estado = 'activo' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("location: papelera.php");
    exit();
}
if (isset($_GET['vaciar'])) {
    $stmt = $enlace->prepare("DELETE FROM registros_psicologia WHERE estado = 'papelera'");
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
    <link rel="stylesheet" href="../../public/css/psicologia.css?v=<?php echo filemtime('../../public/css/psicologia.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asidePsicologia.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <h2>Papelera de reciclaje</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>DNI</th>
                    <th>Apellidos y Nombres</th>
                    <th>Edad</th>
                    <th>Carrera</th>
                    <th>Semestre</th>
                    <th>Turno</th>
                    <th>Motivo de Consulta</th>
                    <th>Última Actualización</th>
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
                        <td><?= $fila['dni'] ?></td>
                        <td><?= $fila['apellidos_nombres'] ?></td>
                        <td><?= $fila['edad'] ?></td>
                        <td><?= $fila['carrera'] ?></td>
                        <td><?= $fila['semestre'] ?></td>
                        <td><?= $fila['turno'] ?></td>
                        <td><?= $fila['motivo_consulta'] ?></td>
                        <td><?= $fila['ultima_actualizacion'] ?></td>
                        <td>
                            <a href="papelera.php?restaurar=<?= $fila['id'] ?>">Restaurar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="icon-buttons">
            <a href="papelera.php?vaciar=true" onclick="return confirm('¿Vaciar papelera?');" class="vaciar">
                <i class='bx bx-trash-alt'></i>Vaciar Papelera
            </a>
            <a href="inicio.php" class="volver">
                <i class='bx bxs-left-arrow-circle'></i>Volver
            </a>
        </div>
    </main>
</body>

</html>