<?php
session_start();
// Verificar que el usuario esté autenticado y pertenezca al área
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
// Incluir la conexión a la base de datos
require_once '../../config/conexion.php';
// Consultar los registros activos
$query = "SELECT rp.id, rp.dni, rp.apellidos_nombres, rp.edad, rp.direccion, rp.telefono, rp.vive_con, 
            rp.motivo_consulta, rp.antecedentes, rp.correo_estudiantil, rp.sesiones, rp.tratamiento, rp.foto_evidencia,
            rp.ultima_actualizacion, 
            rp.id_carrera, rp.id_semestre, rp.id_turno, 
            c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno
        FROM registros_psicologia rp
        JOIN carrera c ON rp.id_carrera = c.id_carrera
        JOIN semestres s ON rp.id_semestre = s.id_semestre
        JOIN turnos t ON rp.id_turno = t.id_turno
        WHERE rp.estado = 'activo'
        ORDER BY rp.fecha DESC";
$resultado = $enlace->query($query);
// Mover registro a la papelera
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']); // Convertir a entero para evitar inyección
    $stmt = $enlace->prepare("UPDATE registros_psicologia SET estado = 'papelera' WHERE id = ?");
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
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $vive_con = $_POST['vive_con'];
    $motivo_consulta = $_POST['motivo_consulta'];
    $antecedentes = $_POST['antecedentes'];
    $carrera = intval($_POST['carrera']);
    $semestre = intval($_POST['semestre']);
    $turno = intval($_POST['turno']);
    $correo_estudiantil = $_POST['correo_estudiantil'];
    $tratamiento = $_POST['tratamiento'];

    $query = "UPDATE registros_psicologia 
              SET dni = ?, apellidos_nombres = ?, edad = ?, direccion = ?, telefono = ?, vive_con = ?, 
                  motivo_consulta = ?, antecedentes = ?, id_carrera = ?, id_semestre = ?, id_turno = ?, 
                  correo_estudiantil = ?, tratamiento = ?
              WHERE id = ?";
    $stmt = $enlace->prepare($query);
$stmt->bind_param(
    "ssisssssiisssi",
    $dni,
    $nombre,
    $edad,
    $direccion,
    $telefono,
    $vive_con,
    $motivo_consulta,
    $antecedentes,
    $carrera,
    $semestre,
    $turno,
    $correo_estudiantil,
    $tratamiento,
    $id
);

    $stmt->execute();
    $stmt->close();
    header("location: inicio.php");
    exit();
}


// Consultas para llenar los dropdowns
$queryCarreras = "SELECT id_carrera, nombre FROM carrera";
$resultCarreras = $enlace->query($queryCarreras);
$querySemestres = "SELECT id_semestre, nombre FROM semestres";
$resultSemestres = $enlace->query($querySemestres);
$queryTurnos = "SELECT id_turno, nombre FROM turnos";
$resultTurnos = $enlace->query($queryTurnos);

// Cerrar conexión
$enlace->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/psicologia.css?v=<?php echo filemtime('../../public/css/psicologia.css'); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="../../public/js/script.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
    <?php include '../../includes/asidePsicologia.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <h2>Historial de psicológicas</h2>
            <div class="buscar_eliminar">
                <div class="search-container">
                    <input
                        type="text"
                        id="buscarDNI"
                        placeholder="Buscar por DNI..."
                        onkeyup="filtrarPorDNI()">
                    <span id="clearSearch" onclick="limpiarBusqueda()">×</span>
                </div>
                <div class="papelera-container">
                    <a href="papelera.php" class="btn btn-secondary">
                        <i class='bx bx-trash'></i> Papelera
                    </a>
                </div>
            </div>
            <table id="tablaRegistros">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Edad</th>
                        <th>Teléfono</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                        <th>Turno</th>
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
                            <td><?= $fila['apellidos_nombres'] ?></td>
                            <td><?= $fila['edad'] ?></td>
                            <td><?= $fila['telefono'] ?></td>
                            <td><?= $fila['carrera'] ?></td>
                            <td><?= $fila['semestre'] ?></td>
                            <td><?= $fila['turno'] ?></td>
                            <td><?= $fila['ultima_actualizacion'] ?></td>
                            <td>
                                <button class="btn btn-details" onclick="verDetalles(<?= $fila['id'] ?>)">Detalles</button> <!-- Botón -->
                            </td>
                            <td>
                                <button class="btn btn-edit"
                                    onclick="abrirModal({
                                    id: '<?= $fila['id'] ?>',
                                    dni: '<?= $fila['dni'] ?>',
                                    apellidos_nombres: '<?= addslashes($fila['apellidos_nombres']) ?>',
                                    edad: '<?= $fila['edad'] ?>',
                                    direccion: '<?= addslashes($fila['direccion']) ?>',
                                    telefono: '<?= $fila['telefono'] ?>',
                                    vive_con: '<?= addslashes($fila['vive_con']) ?>',
                                    motivo_consulta: '<?= addslashes($fila['motivo_consulta']) ?>',
                                    antecedentes: '<?= addslashes($fila['antecedentes']) ?>',
                                    correo_estudiantil: '<?= addslashes($fila['correo_estudiantil']) ?>',
                                    tratamiento: '<?= addslashes($fila['tratamiento']) ?>',
                                    id_carrera: '<?= $fila['id_carrera'] ?>',
                                    id_semestre: '<?= $fila['id_semestre'] ?>',
                                    id_turno: '<?= $fila['id_turno'] ?>'
                                })">Editar</button>
                            </td>
                            <td>
                                <a href="inicio.php?eliminar=<?= $fila['id'] ?>"
                                    class="btn btn-delete"
                                    onclick="return confirm('¿Mover este registro a la papelera?');">Eliminar</a>
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

                    <label for="nombre">Apellidos y Nombres:</label>
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
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="100" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" maxlength="9" pattern="\d{9}" title="Es un numero peruano" required>

                    <label for="vive_con">Vive con:</label>
                    <input type="text" id="vive_con" name="vive_con" maxlength="100">

                    <label for="motivo_consulta">Motivo de consulta:</label>
                    <textarea id="motivo_consulta" name="motivo_consulta" rows="3" maxlength="100" required></textarea>

                    <label for="antecedentes">Antecedentes:</label>
                    <textarea id="antecedentes" name="antecedentes" rows="3" maxlength="100"></textarea>

                    <label for="correo_estudiantil">Correo Estudiantil:</label>
                    <input type="email" id="correo_estudiantil" name="correo_estudiantil" maxlength="100">

                    <label for="tratamiento">Tratamiento:</label>
                    <textarea id="tratamiento" name="tratamiento" rows="3" maxlength="100"></textarea>

                    <div class="modal-actions">
                        <button type="submit" name="actualizar" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        // Función para abrir el modal y cargar datos
        function abrirModal(datos) {
            // Asignar valores a los campos del formulario del modal
            document.getElementById('id').value = datos.id || '';
            document.getElementById('dni').value = datos.dni || '';
            document.getElementById('nombre').value = datos.apellidos_nombres || '';
            document.getElementById('edad').value = datos.edad || '';
            document.getElementById('direccion').value = datos.direccion || '';
            document.getElementById('telefono').value = datos.telefono || '';
            document.getElementById('vive_con').value = datos.vive_con || '';
            document.getElementById('motivo_consulta').value = datos.motivo_consulta || '';
            document.getElementById('antecedentes').value = datos.antecedentes || '';
            document.getElementById('correo_estudiantil').value = datos.correo_estudiantil || '';
            document.getElementById('tratamiento').value = datos.tratamiento || '';
            

            // Seleccionar valores en los dropdowns
            document.getElementById('carrera').value = datos.id_carrera || '';
            document.getElementById('semestre').value = datos.id_semestre || '';
            document.getElementById('turno').value = datos.id_turno || '';

            // Mostrar el modal
            const modal = document.getElementById('modalEditar');
            modal.style.display = 'block';
        }

        // Cerrar el modal al hacer clic fuera de él o en un botón específico
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        function cerrarModal() {
            const modal = document.getElementById('modalEditar');
            modal.style.display = 'none';
        }
        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                cerrarModal();
            }
        };
        // Función para mostrar los detalles en el modal
// Función para mostrar los detalles en el modal
        // Función para mostrar los detalles en el modal
// En inicio.php, modifica la función verDetalles
// Función para mostrar los detalles en el modal
function verDetalles(id) {
    console.log("Intentando abrir detalles para ID:", id); // Para depuración
    
    fetch(`detalles.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                // Generar el contenido del modal
                let detalles = `
                <p><strong>DNI:</strong> ${data.dni}</p>
                <p><strong>Nombre:</strong> ${data.nombre}</p>
                <p><strong>Edad:</strong> ${data.edad}</p>
                <p><strong>Carrera:</strong> ${data.carrera}</p>
                <p><strong>Semestre:</strong> ${data.semestre}</p>
                <p><strong>Turno:</strong> ${data.turno}</p>
                <p><strong>Dirección:</strong> ${data.direccion}</p>
                <p><strong>Teléfono:</strong> ${data.telefono}</p>
                <p><strong>Vive con:</strong> ${data.vive_con}</p>
                ${data.correo_estudiantil ? `<p><strong>Correo Institucional:</strong> ${data.correo_estudiantil}</p>` : ''}
                ${data.sesiones ? `<p><strong>Sesiones:</strong> ${data.sesiones}</p>` : ''}
                <p><strong>Motivo de Consulta:</strong> ${data.motivo_consulta}</p>
                <p><strong>Antecedentes:</strong> ${data.antecedentes}</p>
                ${data.tratamiento ? `<p><strong>Tratamiento:</strong> ${data.tratamiento}</p>` : ''}
                
                <!-- Mostrar la imagen si existe -->
                ${data.foto_evidencia ? `
                    <p><strong>Evidencia:</strong></p>
                    <img src="../../uploads/${data.foto_evidencia}" alt="Evidencia" 
                         style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                ` : '<p><strong>Evidencia:</strong> No hay imagen adjunta</p>'}
                
                <h4>Historial de Citas</h4>
                <ul>
                    ${data.historial_citas.length > 0 ? 
                      data.historial_citas.map(cita => `<li>${cita.fecha} - ${cita.hora} (${cita.estado})</li>`).join('') : 
                      '<li>No hay citas registradas.</li>'}
                </ul>
                <h4>Actividades Realizadas</h4>
                <ul>
                    ${data.actividades_realizadas.length > 0 ? 
                      data.actividades_realizadas.map(act => `<li>${act.fecha_creacion}: ${act.descripcion}</li>`).join('') : 
                      '<li>No hay actividades registradas.</li>'}
                </ul>
            `;
                
                document.getElementById('detalles-content').innerHTML = detalles;
                
                // Mostrar el modal
                const modal = document.getElementById('modalDetalles');
                modal.style.display = 'block';
                console.log("Modal mostrado"); // Para depuración
            }
        })
        .catch(error => {
            console.error('Error al cargar detalles:', error);
            alert('Error al cargar los detalles: ' + error.message);
        });
}

// Función para cerrar el modal de detalles
function cerrarModalDetalles() {
    const modal = document.getElementById('modalDetalles');
    modal.style.display = 'none';
}

// Cerrar el modal si se hace clic fuera de él
window.onclick = function(event) {
    const modalDetalles = document.getElementById('modalDetalles');
    const modalEditar = document.getElementById('modalEditar');
    
    if (event.target === modalDetalles) {
        cerrarModalDetalles();
    }
    if (event.target === modalEditar) {
        cerrarModal();
    }
};
        /*esto para filtrar por DNI*/
        document.addEventListener('DOMContentLoaded', () => {
            const buscarDNI = document.getElementById('buscarDNI');
            const clearSearch = document.getElementById('clearSearch');

            // Mostrar/ocultar la "X" según si hay texto en el input
            buscarDNI.addEventListener('input', () => {
                if (buscarDNI.value.trim() !== '') {
                    clearSearch.style.display = 'block';
                } else {
                    clearSearch.style.display = 'none';
                }
            });

            // Limpiar el campo de búsqueda al hacer clic en la "X"
            clearSearch.addEventListener('click', () => {
                buscarDNI.value = '';
                clearSearch.style.display = 'none';
                buscarDNI.focus();
                // Si tienes una función de filtrado, llama aquí para actualizar la tabla
                filtrarPorDNI();
            });
        });

        // Filtrar los resultados por DNI
        function filtrarPorDNI() {
            const input = document.getElementById('buscarDNI').value.toLowerCase();
            const rows = document.querySelectorAll('#tablaRegistros tbody tr');

            rows.forEach(row => {
                const dni = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                row.style.display = dni.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>

</html>