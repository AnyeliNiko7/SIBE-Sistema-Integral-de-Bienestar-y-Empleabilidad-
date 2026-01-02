<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_id'])) {
        $idEliminar = $_POST['eliminar_id'];
        $stmtImagen = $enlace->prepare("SELECT foto FROM realizacion_psicologia WHERE id = ?");
        $stmtImagen->bind_param("i", $idEliminar);
        $stmtImagen->execute();
        $stmtImagen->bind_result($foto);
        $stmtImagen->fetch();
        $stmtImagen->close();
        $stmtCarrera = $enlace->prepare("DELETE FROM actividad_carrera WHERE id_actividad = ?");
        $stmtCarrera->bind_param("i", $idEliminar);
        $stmtCarrera->execute();
        $stmt = $enlace->prepare("DELETE FROM realizacion_psicologia WHERE id = ?");
        $stmt->bind_param("i", $idEliminar);

        if ($stmt->execute()) {
            if ($foto && file_exists("../../uploads/" . $foto)) {
                unlink("../../uploads/" . $foto);
            }
            echo "";
        } else {
            echo "Error al eliminar la actividad: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $duracion = $_POST['duracion'];
        $cantidad_participantes = $_POST['cantidad_participantes'];
        $lugar = $_POST['lugar'];
        $responsables = $_POST['responsables'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $carreras = isset($_POST['carreras']) ? $_POST['carreras'] : [];
        $idActividad = isset($_POST['id']) ? $_POST['id'] : null;
        
        // VERIFICACIÓN MEJORADA DE LA SUBIDA DE IMAGEN
        $rutaImagen = null;
        $subioImagen = false;
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de archivo
            $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
            $tipoArchivo = $_FILES['foto']['type'];
            
            if (in_array($tipoArchivo, $permitidos)) {
                $nombreArchivo = $_FILES['foto']['name'];
                $tempArchivo = $_FILES['foto']['tmp_name'];
                $directorioDestino = '../../uploads/';
                
                // Verificar que el directorio existe
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }
                
                $nombreUnico = uniqid() . '-' . $nombreArchivo;
                
                if (move_uploaded_file($tempArchivo, $directorioDestino . $nombreUnico)) {
                    $rutaImagen = $nombreUnico;
                    $subioImagen = true;
                }
            }
        }
        
        if ($idActividad) {
            if ($subioImagen) {
                $stmtImagen = $enlace->prepare("SELECT foto FROM realizacion_psicologia WHERE id = ?");
                $stmtImagen->bind_param("i", $idActividad);
                $stmtImagen->execute();
                $stmtImagen->bind_result($fotoAnterior);
                $stmtImagen->fetch();
                $stmtImagen->close();
                if ($fotoAnterior && file_exists("../../uploads/" . $fotoAnterior)) {
                    unlink("../../uploads/" . $fotoAnterior);
                }
                $query = "UPDATE realizacion_psicologia 
                          SET titulo = ?, descripcion = ?, duracion = ?, cantidad_participantes = ?, lugar = ?, responsables = ?, fecha = ?, hora = ?, foto = ? 
                          WHERE id = ?";
                $stmt = $enlace->prepare($query);
                $stmt->bind_param("ssiisssssi", $titulo, $descripcion, $duracion, $cantidad_participantes, $lugar, $responsables, $fecha, $hora, $rutaImagen, $idActividad);
            } else {
                $query = "UPDATE realizacion_psicologia 
                          SET titulo = ?, descripcion = ?, duracion = ?, cantidad_participantes = ?, lugar = ?, responsables = ?, fecha = ?, hora = ? 
                          WHERE id = ?";
                $stmt = $enlace->prepare($query);
                $stmt->bind_param("ssiissssi", $titulo, $descripcion, $duracion, $cantidad_participantes, $lugar, $responsables, $fecha, $hora, $idActividad);
            }
            
            if ($stmt->execute()) {
                $queryEliminarCarreras = "DELETE FROM actividad_carrera WHERE id_actividad = ?";
                $stmtEliminarCarreras = $enlace->prepare($queryEliminarCarreras);
                $stmtEliminarCarreras->bind_param("i", $idActividad);
                $stmtEliminarCarreras->execute();

                if (!empty($carreras)) {
                    foreach ($carreras as $idCarrera) {
                        $stmtCarrera = $enlace->prepare("INSERT INTO actividad_carrera (id_actividad, id_carrera) VALUES (?, ?)");
                        $stmtCarrera->bind_param("ii", $idActividad, $idCarrera);
                        $stmtCarrera->execute();
                    }
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            $stmt->close();
            $stmtEliminarCarreras->close();
        } else {
            if ($subioImagen) {
                $query = "INSERT INTO realizacion_psicologia (titulo, descripcion, duracion, cantidad_participantes, lugar, responsables, fecha, hora, foto) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $enlace->prepare($query);
                $stmt->bind_param("ssiisssss", $titulo, $descripcion, $duracion, $cantidad_participantes, $lugar, $responsables, $fecha, $hora, $rutaImagen);
            } else {
                $query = "INSERT INTO realizacion_psicologia (titulo, descripcion, duracion, cantidad_participantes, lugar, responsables, fecha, hora) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $enlace->prepare($query);
                $stmt->bind_param("ssiissss", $titulo, $descripcion, $duracion, $cantidad_participantes, $lugar, $responsables, $fecha, $hora);
            }
            
            if ($stmt->execute()) {
                $idActividadNuevo = $stmt->insert_id;
                if (!empty($carreras)) {
                    foreach ($carreras as $idCarrera) {
                        $stmtCarrera = $enlace->prepare("INSERT INTO actividad_carrera (id_actividad, id_carrera) VALUES (?, ?)");
                        $stmtCarrera->bind_param("ii", $idActividadNuevo, $idCarrera);
                        $stmtCarrera->execute();
                    }
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error al agregar la actividad: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$actividades = $enlace->query("SELECT a.*, GROUP_CONCAT(c.nombre SEPARATOR ', ') AS carreras
                               FROM realizacion_psicologia a
                               LEFT JOIN actividad_carrera ac ON a.id = ac.id_actividad
                               LEFT JOIN carrera c ON ac.id_carrera = c.id_carrera
                               GROUP BY a.id");

// Obtener todas las carreras para los modales
$todasCarreras = $enlace->query("SELECT * FROM carrera");
$carrerasArray = [];
while ($carrera = $todasCarreras->fetch_assoc()) {
    $carrerasArray[] = $carrera;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Actividades</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/psicologia.css?v=<?php echo filemtime('../../public/css/psicologia.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .input-file {
            display: none;
        }
        
        .custom-file-upload {
            display: inline-block;
            padding: 10px 15px;
            background: #053c8f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            text-align: center;
            margin: 10px 0;
        }
        
        .custom-file-upload:hover {
            background: #002761ff;
        }
        
        .file-selected {
            color: #04107cff;
            font-weight: bold;
            margin: 5px 0;
        }
        #modalDetalles strong {
        color: #001696ff; 
        font-weight: bold;
        }
        #modalAgregar label {
        color: #001696ff; 
        }
        #modalFoto {
            width: 100%;
            max-width: 300px;
            height: 300px; 
            object-fit: cover;
            border-radius: 12px;
            display: none;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <?php include '../../includes/asidePsicologia.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <h2>Gestión de Actividades</h2>
        <div class="agregar">
            <button id="btnAbrirModalAgregar" class="btn-agregar">+ Agregar Actividad</button>
        </div>
        <div class="cards-container">
            <?php while ($fila = $actividades->fetch_assoc()): ?>
                <div class="card">
                    <?php if (!empty($fila['foto'])): ?>
                        <img src="../../uploads/<?php echo $fila['foto']; ?>" alt="Foto de actividad" class="card-img">
                    <?php else: ?>
                        <div class="card-img" style="background:#eee; display:flex; align-items:center; justify-content:center; height:150px;">
                            <i class='bx bx-image-add'></i>
                            Sin imagen
                        </div>
                    <?php endif; ?>
                    <h3 class="card-title"><?php echo $fila['titulo']; ?></h3>
                    <p class="card-hora">Hora: <?php echo $fila['hora']; ?></p>
                    <button class="btn-ver-detalles" onclick="mostrarDetalles(<?php echo htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8'); ?>)">Ver más detalles</button>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Modal Agregar -->
        <div id="modalAgregar" class="modal">
            <div class="modal-content">
                <button id="btnCerrarModalAgregar" class="modal-close">x</button>
                <h3>Agregar Actividad</h3><br>
                <form action="" method="POST" enctype="multipart/form-data">
                    <label for="titulo">Título:</label>
                    <input type="text" name="titulo" maxlength="100" required>
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" maxlength="300" required style="height: 120px; resize: none;"></textarea>
                    <label for="duracion">Duración (minutos):</label>
                    <input type="number" name="duracion" required>
                    <label for="cantidad_participantes">Cantidad de Participantes:</label>
                    <input type="number" name="cantidad_participantes" required>
                    <label for="lugar">Lugar:</label>
                    <input type="text" name="lugar" required>
                    <label for="responsables">Responsables:</label>
                    <input type="text" name="responsables" required>
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                    <label for="hora">Hora:</label>
                    <input type="time" name="hora" required>
                    <div class="custom-file-upload" id="triggerAgregar">
                        <i class='bx bx-image-add'></i>
                        Seleccione una foto como evidencia
                    </div>
                    <input type="file" name="foto" id="fotoAgregar" class="input-file" accept="image/jpeg, image/png, image/gif" onchange="mostrarNombreArchivo(this, 'archivo-seleccionado-agregar')" />
                    <p id="archivo-seleccionado-agregar">No se ha seleccionado ningún archivo.</p><br>
                    <label for="carreras">Carreras participantes:</label>
                    <div class="carreras-checkbox">
                        <?php foreach ($carrerasArray as $carrera): ?>
                            <label><input type='checkbox' name='carreras[]' value='<?php echo $carrera['id_carrera']; ?>'> <?php echo $carrera['nombre']; ?></label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-guardar">Guardar</button>
                </form>
            </div>
        </div>

        <!-- Modal Detalles -->
        <div id="modalDetalles" class="modal">
            <div class="modal-content">
                <button id="btnCerrarModalDetalles" class="modal-close">X</button>
                <h3 id="modalTitulo"></h3><br>
                <p><strong>Descripción:</strong> <span id="modalDescripcion"></span></p>
                <p><strong>Duración:</strong> <span id="modalDuracion"></span></p>
                <p><strong>Participantes:</strong> <span id="modalParticipantes"></span></p>
                <p><strong>Lugar:</strong> <span id="modalLugar"></span></p>
                <p><strong>Responsables:</strong> <span id="modalResponsables"></span></p>
                <p><strong>Fecha:</strong> <span id="modalFecha"></span></p>
                <p><strong>Hora:</strong> <span id="modalHora"></span></p>
                <p><strong>Carreras:</strong> <span id="modalCarreras"></span></p><br>
                <img id="modalFoto" alt="Foto de actividad" style="width: 100%; height: auto; display: none;">
                <div id="modalNoFoto" style="display: none; text-align: center; padding: 20px; background: #eee;">
                    <i class='bx bx-image-add'></i>
                    Sin imagen
                </div>
                <br><br>
                <div class="modal-actions">
                    <button id="btnEliminar" class="btn-delete">Eliminar</button>
                    <button id="btnEditar" class="btn-edit">Editar</button>
                </div>
            </div>
        </div>

        <!-- Modal Editar -->
        <div id="modalEditar" class="modal">
            <div class="modal-content">
                <button id="btnCerrarModalEditar" class="modal-close">X</button>
                <h3>Editar Actividad</h3><br>
                <form id="formEditar" action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editarId">
                    <label for="titulo">Título:</label>
                    <input type="text" name="titulo" id="editarTitulo" required>
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="editarDescripcion" maxlength="300" required style="height: 120px; resize: none;"></textarea>
                    <label for="duracion">Duración (minutos):</label>
                    <input type="number" name="duracion" id="editarDuracion" required>
                    <label for="cantidad_participantes">Cantidad de Participantes:</label>
                    <input type="number" name="cantidad_participantes" id="editarParticipantes" required>
                    <label for="lugar">Lugar:</label>
                    <input type="text" name="lugar" id="editarLugar" required>
                    <label for="responsables">Responsables:</label>
                    <input type="text" name="responsables" id="editarResponsables" required>
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="editarFecha" required min="<?php echo date('Y-m-d'); ?>">
                    <label for="hora">Hora:</label>
                    <input type="time" name="hora" id="editarHora" required>
                    <div class="custom-file-upload" id="triggerEditar">
                        <i class='bx bx-image-add'></i>
                        Seleccione una foto como evidencia
                    </div>
                    <input type="file" name="foto" id="fotoEditar" class="input-file" accept="image/jpeg, image/png, image/gif" onchange="mostrarNombreArchivo(this, 'archivo-seleccionado-editar')" />
                    <p id="archivo-seleccionado-editar">No se ha seleccionado ningún archivo.</p><br>
                    <label for="carreras">Carreras:</label>
                    <div class="carreras-checkbox" id="carrerasEditar">
                        <?php foreach ($carrerasArray as $carrera): ?>
                            <label><input type='checkbox' name='carreras[]' value='<?php echo $carrera['id_carrera']; ?>'> <?php echo $carrera['nombre']; ?></label><br>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-guardar">Guardar</button>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Variables globales para almacenar datos temporales
    let actividadActual = null;
    let carrerasSeleccionadas = [];

    // Funciones para mostrar/ocultar modales
    document.getElementById('btnAbrirModalAgregar').onclick = function() {
        document.getElementById('modalAgregar').style.display = 'block';
    };
    
    document.getElementById('btnCerrarModalAgregar').onclick = function() {
        document.getElementById('modalAgregar').style.display = 'none';
        resetearFormulario('modalAgregar');
    };
    
    document.getElementById('btnCerrarModalDetalles').onclick = function() {
        document.getElementById('modalDetalles').style.display = 'none';
    };
    
    document.getElementById('btnCerrarModalEditar').onclick = function() {
        document.getElementById('modalEditar').style.display = 'none';
        resetearFormulario('modalEditar');
    };

    // Función para mostrar nombre de archivo seleccionado
    function mostrarNombreArchivo(inputElement, paragraphId) {
        const nombreArchivo = inputElement.files[0] ? inputElement.files[0].name : 'No se ha seleccionado ningún archivo.';
        const paragraphElement = document.getElementById(paragraphId);
        if (paragraphElement) {
            paragraphElement.textContent = 'Archivo seleccionado: ' + nombreArchivo;
            paragraphElement.className = 'file-selected';
        }
    }

    // Función para resetear formularios
    function resetearFormulario(modalId) {
        const modal = document.getElementById(modalId);
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => form.reset());
        
        // Resetear mensajes de archivo
        const fileMessages = modal.querySelectorAll('[id^="archivo-seleccionado-"]');
        fileMessages.forEach(msg => {
            msg.textContent = 'No se ha seleccionado ningún archivo.';
            msg.className = '';
        });
    }

    // Función para mostrar detalles de la actividad
    function mostrarDetalles(data) {
        actividadActual = data;
        document.getElementById('modalTitulo').innerText = data.titulo;
        document.getElementById('modalDescripcion').innerText = data.descripcion;
        document.getElementById('modalDuracion').innerText = data.duracion + ' minutos';
        document.getElementById('modalParticipantes').innerText = data.cantidad_participantes;
        document.getElementById('modalLugar').innerText = data.lugar;
        document.getElementById('modalResponsables').innerText = data.responsables;
        document.getElementById('modalFecha').innerText = data.fecha;
        document.getElementById('modalHora').innerText = data.hora;
        document.getElementById('modalCarreras').innerText = data.carreras;
        
        // Manejar la imagen
        const modalFoto = document.getElementById('modalFoto');
        const modalNoFoto = document.getElementById('modalNoFoto');
        
        if (data.foto) {
            modalFoto.src = "../../uploads/" + data.foto;
            modalFoto.style.display = 'block';
            modalNoFoto.style.display = 'none';
        } else {
            modalFoto.style.display = 'none';
            modalNoFoto.style.display = 'block';
        }
        
        // Guardar las carreras seleccionadas para usar en edición
        carrerasSeleccionadas = data.carreras.split(', ');
        
        document.getElementById('modalDetalles').style.display = 'block';
        
        // Configurar botón eliminar
        document.getElementById('btnEliminar').onclick = function() {
            if (confirm('¿Estás seguro de que deseas eliminar esta actividad?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar_id';
                input.value = data.id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        };
        
        // Configurar botón editar
        document.getElementById('btnEditar').onclick = function() {
            document.getElementById('editarId').value = data.id;
            document.getElementById('editarTitulo').value = data.titulo;
            document.getElementById('editarDescripcion').value = data.descripcion;
            document.getElementById('editarDuracion').value = data.duracion;
            document.getElementById('editarParticipantes').value = data.cantidad_participantes;
            document.getElementById('editarLugar').value = data.lugar;
            document.getElementById('editarResponsables').value = data.responsables;
            document.getElementById('editarFecha').value = data.fecha;
            document.getElementById('editarHora').value = data.hora;
            
            // Marcar las carreras seleccionadas
            const checkboxes = document.querySelectorAll('#carrerasEditar input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                const carreraNombre = checkbox.parentElement.textContent.trim();
                checkbox.checked = carrerasSeleccionadas.includes(carreraNombre);
            });
            
            document.getElementById('modalDetalles').style.display = 'none';
            document.getElementById('modalEditar').style.display = 'block';
        };
    }

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modales = document.querySelectorAll('.modal');
        modales.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
                resetearFormulario(modal.id);
            }
        });
    };

    // Configuración simple para los botones de selección de archivo
    document.addEventListener('DOMContentLoaded', function() {
        // Para el modal de agregar
        document.getElementById('triggerAgregar').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('fotoAgregar').click();
        });
        
        // Para el modal de editar
        document.getElementById('triggerEditar').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('fotoEditar').click();
        });
    });
</script>
</body>
</html>