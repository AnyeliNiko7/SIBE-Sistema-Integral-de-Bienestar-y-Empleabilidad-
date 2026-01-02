<?php
session_start();

// Verificar que el usuario esté autenticado y pertenezca al área
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Tópico') {
    header("location: ../../index.php");
    exit();
}
// Incluir la conexión a la base de datos
require_once '../../config/conexion.php';

// Consultar los registros activos
$query = "SELECT rt.id, rt.dni, rt.nombre, rt.edad, c.id_carrera, c.nombre AS carrera, 
                 s.id_semestre, s.nombre AS semestre, 
                 t.id_turno, t.nombre AS turno, 
                 rt.sintomas, rt.ultima_actualizacion
          FROM registros_topico rt
          JOIN carrera c ON rt.id_carrera = c.id_carrera
          JOIN semestres s ON rt.id_semestre = s.id_semestre
          JOIN turnos t ON rt.id_turno = t.id_turno
          WHERE rt.estado = 'activo'
          ORDER BY rt.fecha DESC";
$resultado = $enlace->query($query);
// Mover registro a la papelera
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']); // Convertir a entero para evitar inyección
    $stmt = $enlace->prepare("UPDATE registros_topico SET estado = 'papelera' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}
// Manejar actualización de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = intval($_POST['id']);
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $edad = intval($_POST['edad']);
    $carrera = $_POST['carrera'];
    $semestre = $_POST['semestre'];
    $turno = $_POST['turno'];
    $sintomas = $_POST['sintomas'];

    // Validaciones del servidor
    if (strlen($dni) !== 8 || !ctype_digit($dni)) {
        die("Error: El DNI debe tener exactamente 8 dígitos.");
    }
    if (strlen($nombre) > 50) {
        die("Error: El nombre y apellidos no pueden superar los 50 caracteres.");
    }
    if ($edad < 1 || $edad > 100) {
        die("Error: La edad debe estar entre 0 y 100 años.");
    }

    $query = "UPDATE registros_topico 
              SET dni = ?, nombre = ?, edad = ?, id_carrera = ?, id_semestre = ?, id_turno = ?, sintomas = ? 
              WHERE id = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("ssissssi", $dni, $nombre, $edad, $carrera, $semestre, $turno, $sintomas, $id);
    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}

// Consultas para llenar los dropdowns de carrera, semestre y turno
$queryCarreras = "SELECT id_carrera, nombre FROM carrera";
$resultCarreras = $enlace->query($queryCarreras);
$querySemestres = "SELECT id_semestre, nombre FROM semestres";
$resultSemestres = $enlace->query($querySemestres);
$queryTurnos = "SELECT id_turno, nombre FROM turnos";
$resultTurnos = $enlace->query($queryTurnos);
// Cerrar conexión al terminar
$enlace->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de registros de tópico</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/topico.css?v=<?php echo filemtime('../../public/css/topico.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
    <?php include '../../includes/asideTopico.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <h2>Historial de Registros</h2>
            <div class="buscar_eliminar">
                <!-- Barra de búsqueda -->
                <div class="search-container">
                    <input
                        type="text"
                        id="buscarDNI"
                        placeholder="Buscar por DNI..."
                        onkeyup="filtrarPorDNI()">
                    <span id="clearSearch" onclick="limpiarBusqueda()">×</span>
                </div>
                <!-- Botón para acceder a la papelera -->
                <div class="papelera-container">
                    <a href="papelera.php" class="btn btn-secondary">
                        <i class='bx bx-trash'></i> Papelera
                    </a>
                </div>
            </div>
            <!-- Tabla de registros -->
            <table id="tablaRegistros">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                        <th>Turno</th>
                        <th>Síntomas</th>
                        <th>Última Actualización</th>
                        <th>Ver Detalles</th> <!-- Nueva columna -->
                        <th>Editar</th>
                        <th>Eliminar</th>
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
                            <td><?= $fila['nombre'] ?></td>
                            <td><?= $fila['carrera'] ?></td>
                            <td><?= $fila['semestre'] ?></td>
                            <td><?= $fila['turno'] ?></td>
                            <td><?= $fila['sintomas'] ?></td>
                            <td><?= $fila['ultima_actualizacion'] ?></td>
                            <td>
                                <button class="btn btn-details" onclick="verDetalles(<?= $fila['id'] ?>)">Detalles</button> <!-- Botón -->
                            </td>
                            <td>
                                <button class="btn btn-edit" onclick='abrirModal(<?= json_encode($fila, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Editar</button>
                            </td>
                            <td>
                                <a href="inicio.php?eliminar=<?= $fila['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Mover este registro a la papelera?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
        <!-- Modal para detalles -->
        <div id="modalDetalles" class="modal">
            <div class="modal-content">
                <h3>Detalles del Registro</h3>
                <div id="detalles-content">
                    <!-- Aquí se cargarán los detalles dinámicamente -->
                </div>
                <button type="button" onclick="cerrarModalDetalles()">Cerrar</button>
            </div>
        </div>
        <!-- Modal para edición -->
        <div id="modalEditar" class="modal">
            <div class="modal-content">
                <h3>Edita el registro</h3>
                <form method="POST" action="inicio.php">
                    <input type="hidden" id="id" name="id">
                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" name="dni" maxlength="8" pattern="\d{8}" title="Debe tener exactamente 8 dígitos" required>
                    <label for="nombre">Apellido y Nombre:</label>
                    <input type="text" id="nombre" name="nombre" maxlength="50" required>
                    <label for="edad">Edad:</label>
                    <input type="number" id="edad" name="edad" min="1" max="100" required>
                    <label for="carrera">Carrera:</label>
                    <select id="carrera" name="carrera" required>
                        <?php while ($row = $resultCarreras->fetch_assoc()): ?>
                            <option value="<?= $row['id_carrera'] ?>"><?= $row['nombre'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="semestre">Semestre:</label>
                    <select id="semestre" name="semestre" required>
                        <?php while ($row = $resultSemestres->fetch_assoc()): ?>
                            <option value="<?= $row['id_semestre'] ?>"><?= $row['nombre'] ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label for="turno">Turno:</label>
                    <select id="turno" name="turno" required>
                        <?php while ($row = $resultTurnos->fetch_assoc()): ?>
                            <option value="<?= $row['id_turno'] ?>"><?= $row['nombre'] ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label for="sintomas">Síntomas:</label>
                    <textarea id="sintomas" name="sintomas" required></textarea>
                    <div class="button-group">
                        <button name="actualizar">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        // Función para abrir el modal con los datos del registro a editar
        function abrirModal(datos) {
            // Asignar los valores del registro al formulario en el modal
            document.getElementById('id').value = datos.id;
            document.getElementById('dni').value = datos.dni;
            document.getElementById('nombre').value = datos.nombre;
            document.getElementById('edad').value = datos.edad;
            document.getElementById('sintomas').value = datos.sintomas;

            // Asignar los valores correspondientes de carrera, semestre y turno usando sus IDs
            document.getElementById('carrera').value = datos.id_carrera; // Modificado para usar el id_carrera
            document.getElementById('semestre').value = datos.id_semestre; // Modificado para usar el id_semestre
            document.getElementById('turno').value = datos.id_turno; // Modificado para usar el id_turno

            // Mostrar el modal
            document.getElementById('modalEditar').style.display = 'block';
        }
        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                cerrarModal();
            }
        }

        // Función para filtrar por DNI
        function filtrarPorDNI() {
            const input = document.getElementById("buscarDNI");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("tablaRegistros");
            const tr = table.getElementsByTagName("tr");

            // Mostrar el botón "×" si hay texto en el input
            const clearButton = document.getElementById("clearSearch");
            clearButton.style.display = input.value ? "inline-block" : "none";

            // Filtrar las filas de la tabla
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName("td")[1]; // Columna del DNI
                if (td) {
                    const textValue = td.textContent || td.innerText;
                    tr[i].style.display = textValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }

        // Función para limpiar el campo de búsqueda
        function limpiarBusqueda() {
            const input = document.getElementById("buscarDNI");
            input.value = ""; // Vaciar el campo de texto
            filtrarPorDNI(); // Llamar a la función de filtrado para mostrar todas las filas
        }

        // Función para mostrar los detalles
        function verDetalles(id) {
            fetch(`detalles.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        let html = `
                    <p><strong>DNI:</strong> ${data.dni}</p>
                    <p><strong>Nombre:</strong> ${data.nombre}</p>
                    <p><strong>Edad:</strong> ${data.edad}</p>
                    <p><strong>Carrera:</strong> ${data.carrera}</p>
                    <p><strong>Semestre:</strong> ${data.semestre}</p>
                    <p><strong>Turno:</strong> ${data.turno}</p>
                    <p><strong>Síntomas:</strong> ${data.sintomas}</p>
                    <p><strong>Medicamentos:</strong></p>
                    <ul>
                        ${data.medicamentos.map(m => `<li>${m.nombre}</li>`).join('')}
                    </ul>
                `;
                        document.getElementById('detalles-content').innerHTML = html;
                        document.getElementById('modalDetalles').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error al obtener los detalles:', error);
                });
        }

        // Función para cerrar el modal de detalles
        function cerrarModalDetalles() {
            document.getElementById('modalDetalles').style.display = 'none';
        }
    </script>
</body>

</html>