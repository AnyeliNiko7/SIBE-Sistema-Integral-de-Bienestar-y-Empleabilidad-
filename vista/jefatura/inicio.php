<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Jefatura') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';
$sql = "SELECT ov.id, ov.apellidos_nombres, ov.celular, ov.colegio, c.nombre AS carrera_nombre 
        FROM orientacion_vocacional ov
        LEFT JOIN carrera c ON ov.id_carrera = c.id_carrera
        WHERE ov.estado = 'activo'";
$resultado = $enlace->query($sql);
if (!$resultado) {
    die("Error en la consulta: " . $enlace->error);
}
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "UPDATE orientacion_vocacional SET estado = 'papelera' WHERE id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $apellidos_nombres = trim($_POST['apellidos_nombres']);
    $celular = trim($_POST['celular']);
    $colegio = trim($_POST['colegio']);
    $id_carrera = $_POST['id_carrera'];
    if (strlen($apellidos_nombres) > 50) {
        echo "<script>alert('El nombre no debe exceder los 50 caracteres.'); window.history.back();</script>";
        exit();
    }
    if (strlen($colegio) > 50) {
        echo "<script>alert('El nombre del colegio no debe exceder los 50 caracteres.'); window.history.back();</script>";
        exit();
    }
    if (!ctype_digit($celular) || strlen($celular) != 9) {
        echo "<script>alert('El número de celular debe tener exactamente 9 dígitos y solo contener números.'); window.history.back();</script>";
        exit();
    }
    $sql = "UPDATE orientacion_vocacional SET apellidos_nombres = ?, celular = ?, colegio = ?, id_carrera = ? WHERE id = ?";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param("sssii", $apellidos_nombres, $celular, $colegio, $id_carrera, $id);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}
$sqlCarreras = "SELECT * FROM carrera";
$carreras = $enlace->query($sqlCarreras);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Orientación Vocacional</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/jefatura.css?v=<?php echo filemtime('../../public/css/jefatura.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.15/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <?php include '../../includes/asideJefatura.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <div class="titulo">
                <h2>Administrar Orientación Vocacional</h2>
            </div>
            <div class="buscar_eliminar">
                <div class="search-container">
                    <input type="text" id="buscar" placeholder="Buscar por Apellidos o Colegio..." onkeyup="filtrarTabla()">
                    <span id="clearSearch" onclick="limpiarBusqueda()">&times;</span>
                </div>
                <div class="export-buttons">
                    <button onclick="descargarPDF()" class="btn btn-archivop"><i class='bx bxs-file-pdf'></i> Descargar PDF</button>
                    <button onclick="descargarExcel()" class="btn btn-archivoe"><i class='bx bxs-file'></i> Descargar Excel</button>
                </div>
                <div class="papelera-container">
                    <a href="papelera.php" class="btn btn-secondary">
                        <i class='bx bx-trash'></i> Papelera
                    </a>
                </div>
            </div>
            <table id="tablaOrientacion" border="1">
                <thead>
                    <tr>
                        <th>Apellidos y Nombres</th>
                        <th>Celular</th>
                        <th>Colegio</th>
                        <th>Carrera</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['apellidos_nombres']); ?></td>
                            <td><?php echo htmlspecialchars($fila['celular'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($fila['colegio']); ?></td>
                            <td><?php echo htmlspecialchars($fila['carrera_nombre'] ?? ''); ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="abrirModal(<?php echo htmlspecialchars(json_encode($fila)); ?>)">Editar</button>
                                <a class="btn btn-delete" href="inicio.php?eliminar=<?php echo $fila['id']; ?>" onclick="return confirm('¿Desear mover a papelera?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="modalEditar" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="cerrarModal()">&times;</span>
                    <h2>Editar Registro</h2>
                    <form method="POST" action="inicio.php" onsubmit="return validarFormulario()">
                        <input type="hidden" id="id" name="id">
                        <label for="apellidos_nombres">Apellidos y Nombres:</label>
                        <input type="text" id="apellidos_nombres" name="apellidos_nombres" maxlength="50" required>
                        <label for="celular">Celular:</label>
                        <input type="text" id="celular" name="celular" maxlength="9" onkeypress="return isNumberKey(event)" required>
                        <label for="colegio">Colegio:</label>
                        <input type="text" id="colegio" name="colegio" maxlength="50" required>
                        <label for="id_carrera">Carrera:</label>
                        <select id="id_carrera" name="id_carrera" required>
                            <?php while ($carrera = $carreras->fetch_assoc()): ?>
                                <option value="<?php echo $carrera['id_carrera']; ?>">
                                    <?php echo htmlspecialchars($carrera['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="button-group">
                            <button name="actualizar" type="submit">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function abrirModal(registro) {
            document.getElementById('modalEditar').style.display = 'block';
            document.getElementById('id').value = registro.id;
            document.getElementById('apellidos_nombres').value = registro.apellidos_nombres;
            document.getElementById('celular').value = registro.celular;
            document.getElementById('colegio').value = registro.colegio;

            const selectCarrera = document.getElementById('id_carrera');
            Array.from(selectCarrera.options).forEach(option => {
                if (option.value == registro.id_carrera) {
                    option.selected = true;
                } else {
                    option.selected = false;
                }
            });
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target === document.getElementById('modalEditar')) {
                cerrarModal();
            }
        }

        function filtrarTabla() {
            const input = document.getElementById('buscar');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('tablaOrientacion');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdApellidos = tr[i].getElementsByTagName('td')[0];
                const tdColegio = tr[i].getElementsByTagName('td')[2];

                if (tdApellidos || tdColegio) {
                    const txtValueApellidos = tdApellidos.textContent || tdApellidos.innerText;
                    const txtValueColegio = tdColegio.textContent || tdColegio.innerText;

                    if (txtValueApellidos.toUpperCase().indexOf(filter) > -1 || txtValueColegio.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
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
            doc.text("Lista de Orientación Vocacional", 10, 10);

            const rows = [];
            const rowsData = document.querySelectorAll('#tablaOrientacion tbody tr');
            rowsData.forEach(row => {
                const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.textContent);
                rows.push(rowData);
            });

            doc.autoTable({
                head: [
                    ['Apellidos y Nombres', 'Celular', 'Colegio', 'Carrera']
                ],
                body: rows
            });

            doc.save('orientacion_vocacional.pdf');
        }

        function descargarExcel() {
            const table = document.getElementById('tablaOrientacion');
            const ws = XLSX.utils.table_to_sheet(table);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'OrientacionVocacional');

            XLSX.writeFile(wb, 'orientacion_vocacional.xlsx');
        }
    </script>
</body>

</html>