<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Consejería') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';
$sql = "SELECT t.id_tutor, t.nombre AS tutor_nombre, t.dni, t.correo, t.telefono, 
               s.nombre AS semestre_nombre, c.nombre AS carrera_nombre, t.id_carrera, t.id_semestre, 
               tr.nombre AS turno_nombre, t.id_turno 
        FROM tutores t
        LEFT JOIN carrera c ON t.id_carrera = c.id_carrera
        LEFT JOIN semestres s ON t.id_semestre = s.id_semestre
        LEFT JOIN turnos tr ON t.id_turno = tr.id_turno
        WHERE t.estado = 'activo'";
$resultado = $enlace->query($sql);
if (!$resultado) {
    die("Error en la consulta: " . $enlace->error);
}
if (isset($_GET['eliminar'])) {
    $id_tutor = $_GET['eliminar'];
    $sql = "UPDATE tutores SET estado = 'papelera' WHERE id_tutor = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("i", $id_tutor);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tutor'])) {
    $id_tutor = $_POST['id_tutor'];
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $id_semestre = $_POST['id_semestre'];
    $id_carrera = $_POST['id_carrera'];
    $id_turno = $_POST['id_turno'];  // Agregar esta línea

    $sql = "UPDATE tutores SET nombre = ?, dni = ?, correo = ?, telefono = ?, id_semestre = ?, id_carrera = ?, id_turno = ? WHERE id_tutor = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("ssssiiis", $nombre, $dni, $correo, $telefono, $id_semestre, $id_carrera, $id_turno, $id_tutor);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}
$sqlCarreras = "SELECT * FROM carrera";
$carreras = $enlace->query($sqlCarreras);

$sqlSemestres = "SELECT * FROM semestres";
$semestres = $enlace->query($sqlSemestres);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Tutores</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/consejeria.css?v=<?php echo filemtime('../../public/css/consejeria.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.15/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>

<body>
    <?php include '../../includes/asideConsejeria.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <div class="titulo">
                <h2>Administrar los tutores</h2>
            </div>
            <div class="buscar_eliminar">
                <div class="search-container">
                    <input type="text" id="buscar" placeholder="Buscar por DNI o Nombre..." onkeyup="filtrarTabla()">
                    <span id="clearSearch" onclick="limpiarBusqueda()">&times;</span>
                </div>
                <div class="export-buttons">
                    <button onclick="descargarPDF()" class="btn btn-archivop"><i class='bx bxs-file-pdf'></i>Descargar PDF</button>
                    <button onclick="descargarExcel()" class="btn btn-archivoe"><i class='bx bxs-file'></i>Descargar Excel</button>
                </div>
                <div class="papelera-container">
                    <a href="papelera.php" class="btn btn-secondary"><i class='bx bx-trash'></i> Papelera</a>
                </div>
            </div>
            <table id="tablaTutores">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                        <th>Turno</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['tutor_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($fila['dni']); ?></td>
                            <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($fila['carrera_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($fila['semestre_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($fila['turno_nombre']); ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($fila)); ?>)">Editar</button>
                                <a class="btn btn-delete" href="inicio.php?eliminar=<?php echo $fila['id_tutor']; ?>" onclick="return confirm('¿Mover a papelera?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="modalEditar" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="cerrarModal()">&times;</span>
                    <h3>Editar Tutor</h3>
                    <form method="POST" action="inicio.php">
                        <input type="hidden" id="id_tutor" name="id_tutor">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required>
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" required>
                        <label for="correo">Correo:</label>
                        <input type="email" id="correo" name="correo" required>
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" required>
                        <label for="id_carrera">Carrera:</label>
                        <select id="id_carrera" name="id_carrera" required>
                            <?php while ($carrera = $carreras->fetch_assoc()): ?>
                                <option value="<?php echo $carrera['id_carrera']; ?>">
                                    <?php echo htmlspecialchars($carrera['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label for="id_semestre">Semestre:</label>
                        <select id="id_semestre" name="id_semestre" required>
                            <?php while ($semestre = $semestres->fetch_assoc()): ?>
                                <option value="<?php echo $semestre['id_semestre']; ?>">
                                    <?php echo htmlspecialchars($semestre['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label for="id_turno">Turno:</label>
                        <select id="id_turno" name="id_turno" required>
                            <?php
                            $sqlTurnos = "SELECT * FROM turnos";
                            $turnos = $enlace->query($sqlTurnos);
                            while ($turno = $turnos->fetch_assoc()):
                            ?>
                                <option value="<?php echo $turno['id_turno']; ?>">
                                    <?php echo htmlspecialchars($turno['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select><br>
                        <button name="actualizar" type="submit">Actualizar</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function abrirModal(tutor) {
            document.getElementById('modalEditar').style.display = 'block';
            document.getElementById('id_tutor').value = tutor.id_tutor;
            document.getElementById('nombre').value = tutor.tutor_nombre;
            document.getElementById('dni').value = tutor.dni;
            document.getElementById('correo').value = tutor.correo;
            document.getElementById('telefono').value = tutor.telefono;
            const selectCarrera = document.getElementById('id_carrera');
            Array.from(selectCarrera.options).forEach(option => {
                option.selected = option.value == tutor.id_carrera;
            });
            const selectSemestre = document.getElementById('id_semestre');
            Array.from(selectSemestre.options).forEach(option => {
                option.selected = option.value == tutor.id_semestre;
            });
            const selectTurno = document.getElementById('id_turno');
            Array.from(selectTurno.options).forEach(option => {
                option.selected = option.value == tutor.id_turno;
            });
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target === document.getElementById('modalEditar')) {
                cerrarModal();
            }
        };

        function filtrarTabla() {
            const input = document.getElementById('buscar');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('tablaTutores');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdDNI = tr[i].getElementsByTagName('td')[1];
                const tdNombre = tr[i].getElementsByTagName('td')[0];

                if (tdDNI || tdNombre) {
                    const txtValueDNI = tdDNI.textContent || tdDNI.innerText;
                    const txtValueNombre = tdNombre.textContent || tdNombre.innerText;

                    tr[i].style.display = (txtValueDNI.toUpperCase().includes(filter) || txtValueNombre.toUpperCase().includes(filter)) ? '' : 'none';
                }
            }
        }

        function limpiarBusqueda() {
            document.getElementById('buscar').value = '';
            filtrarTabla();
        }

        function descargarPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const usuario = "<?php echo $_SESSION['usuario']; ?>";
            const pageWidth = doc.internal.pageSize.width || doc.internal.pageSize.getWidth();
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(16);
            doc.text("Lista de Tutores", pageWidth / 2, 20, {
                align: "center"
            });
            doc.setFontSize(10);
            doc.text(`Generado por: ${usuario}`, pageWidth / 2, 30, {
                align: "center"
            });
            const rows = [];
            const rowsData = document.querySelectorAll('#tablaTutores tbody tr');
            rowsData.forEach(row => {
                const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.textContent);
                rows.push(rowData);
            });
            doc.autoTable({
                head: [
                    ['Nombre', 'DNI', 'Correo', 'Teléfono', 'Carrera', 'Semestre']
                ],
                body: rows,
                startY: 40,
                styles: {
                    halign: 'center'
                }
            });

            doc.save('tutores.pdf');
        }

        function descargarExcel() {
            const usuario = "<?php echo $_SESSION['usuario']; ?>";
            const table = document.getElementById('tablaTutores');
            const rows = [];
            rows.push(["Generado por: " + usuario]);
            rows.push(['Nombre', 'DNI', 'Correo', 'Teléfono', 'Carrera', 'Semestre', 'Turno']);
            const filasTabla = table.querySelectorAll('tbody tr');
            filasTabla.forEach(row => {
                const celdas = Array.from(row.querySelectorAll('td')).slice(0, 6);
                const turno = row.querySelectorAll('td')[6].textContent.trim();
                const datos = celdas.map(celda => celda.textContent.trim()).concat(turno);
                rows.push(datos);
            });
            const ws = XLSX.utils.aoa_to_sheet(rows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Tutores');
            XLSX.writeFile(wb, 'tutores.xlsx');
        }
    </script>
</body>

</html>