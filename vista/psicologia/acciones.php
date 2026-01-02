<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';

if (isset($_GET['dni'])) {
    $dni = $_GET['dni'];
    $query = "
        SELECT rp.apellidos_nombres, rp.edad, rp.direccion, rp.telefono, rp.motivo_consulta, 
            rp.vive_con, rp.antecedentes, rp.correo_estudiantil, rp.tratamiento, 
            rp.foto_evidencia, rp.id_turno, rp.sesiones,
            c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno
        FROM registros_psicologia rp
        JOIN carrera c ON rp.id_carrera = c.id_carrera
        JOIN semestres s ON rp.id_semestre = s.id_semestre
        JOIN turnos t ON rp.id_turno = t.id_turno
        WHERE rp.dni = ? AND rp.estado = 'activo'
        ORDER BY rp.id DESC
        LIMIT 1
    ";

    $stmt = $enlace->prepare($query);
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode($result->num_rows > 0 ? $result->fetch_assoc() : ['error' => 'No se encontraron datos para el DNI proporcionado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $edad = intval($_POST['edad'] ?? 0);
    $id_carrera = intval($_POST['carrera'] ?? 0);
    $id_semestre = intval($_POST['semestre'] ?? 0);
    $id_turno = intval($_POST['turno'] ?? 0);
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo_estudiantil = $_POST['correo_estudiantil'] ?? '';
    $motivo_consulta = $_POST['motivo_consulta'] ?? '';
    $vive_con = $_POST['vive_con'] ?? '';
    $antecedentes = $_POST['antecedentes'] ?? '';
    $tratamiento = $_POST['tratamiento'] ?? '';
    
    // Manejo de la subida de imagen
    $foto_evidencia = null;
    if (isset($_FILES['foto_evidencia']) && $_FILES['foto_evidencia']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        // Crear directorio si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['foto_evidencia']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        // Validar que sea una imagen
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['foto_evidencia']['tmp_name'], $uploadFile)) {
                $foto_evidencia = $fileName;
            } else {
                // Error al mover el archivo
                die('Error: No se pudo guardar la imagen.');
            }
        } else {
            // Tipo de archivo no permitido
            die('Error: Solo se permiten archivos JPG, JPEG, PNG y GIF.');
        }
    }

    // Validación
    if (empty($dni) || empty($nombre) || $edad <= 0 || $id_carrera <= 0 || $id_semestre <= 0 || $id_turno <= 0 || empty($direccion) || empty($motivo_consulta)) {
        die('Error: Datos incompletos o inválidos. Verifica los campos obligatorios.');
    }

    // 🔹 Calcular sesiones automáticamente
    $query_sesiones = "SELECT COUNT(*) as total FROM registros_psicologia WHERE dni = ?";
    $stmt_sesiones = $enlace->prepare($query_sesiones);
    $stmt_sesiones->bind_param("s", $dni);
    $stmt_sesiones->execute();
    $result_sesiones = $stmt_sesiones->get_result();
    $row_sesiones = $result_sesiones->fetch_assoc();
    $sesiones = $row_sesiones['total'] + 1;
    
    // 🔹 Insertar con nuevos campos
    $query = "
        INSERT INTO registros_psicologia 
        (dni, apellidos_nombres, edad, id_carrera, id_semestre, id_turno, direccion, telefono, 
         correo_estudiantil, motivo_consulta, vive_con, antecedentes, tratamiento, foto_evidencia, sesiones, fecha) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $enlace->prepare($query);
    $stmt->bind_param("ssiiisssssssssi", $dni, $nombre, $edad, $id_carrera, $id_semestre, $id_turno, $direccion, $telefono, $correo_estudiantil, $motivo_consulta, $vive_con, $antecedentes, $tratamiento, $foto_evidencia, $sesiones);
    
    if ($stmt->execute()) {
        $paciente_id = $stmt->insert_id;
        
        if (!empty($_POST['actividad'])) {
            $actividades = $_POST['actividad'];
            foreach ($actividades as $actividad) {
                if (!empty($actividad)) {
                    $query_actividad = "
                        INSERT INTO actividades_realizadas (paciente_id, descripcion) 
                        VALUES (?, ?)";
                    $stmt_actividad = $enlace->prepare($query_actividad);
                    $stmt_actividad->bind_param("is", $paciente_id, $actividad);

                    if (!$stmt_actividad->execute()) {
                        echo "Error al guardar la actividad: " . $stmt_actividad->error;
                        exit();
                    }
                }
            }
        }
        
        if (!empty($_POST['fecha']) && !empty($_POST['hora'])) {
            $psicologo_id = $_SESSION['id']; // ID del psicólogo
            $estado = 'Programada';
            $fechas = $_POST['fecha'];
            $horas = $_POST['hora'];

            foreach ($fechas as $index => $fecha_cita) {
                $hora_cita = $horas[$index] ?? null;
                if (!empty($fecha_cita) && !empty($hora_cita)) {
                    $query_cita = "
                        INSERT INTO gestion_citas (fecha, hora, paciente_id, psicologo_id, estado) 
                        VALUES (?, ?, ?, ?, ?)";
                    $stmt_cita = $enlace->prepare($query_cita);
                    $stmt_cita->bind_param("ssiss", $fecha_cita, $hora_cita, $paciente_id, $psicologo_id, $estado);

                    if (!$stmt_cita->execute()) {
                        echo "Error al guardar la cita: " . $stmt_cita->error;
                        exit();
                    } else {
                        // Enviar correo de notificación al estudiante
                        $asunto = "Recordatorio de cita psicológica";
                        $mensaje = "
                            Hola $nombre,<br><br>
                            Se ha programado tu cita en el área de Psicología:<br>
                            📅 Fecha: $fecha_cita<br>
                            ⏰ Hora: $hora_cita<br><br>
                            Por favor asiste puntualmente.<br><br>
                            Atte,<br>
                            Centro de Psicología
                        ";
                        $cabeceras  = "MIME-Version: 1.0" . "\r\n";
                        $cabeceras .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $cabeceras .= "From: psicologia@tudominio.com" . "\r\n";

                        if (!empty($correo_estudiantil)) {
                            if (mail($correo_estudiantil, $asunto, $mensaje, $cabeceras)) {
                                // Opcional: log o mensaje
                            } else {
                                error_log("Error al enviar el correo a $correo_estudiantil");
                            }
                        }
                    }
                }
            }
        }
        
        header("location: inicio.php");
        exit();
    } else {
        echo "Error al guardar el registro: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar nuevo registro</title>
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
        <section class="form-container">
            <h2>Agregar Nuevo Registro</h2>
            <!-- IMPORTANTE: Agregar enctype para permitir subida de archivos -->
            <form method="POST" action="acciones.php" class="styled-form" enctype="multipart/form-data">
                <div class="registro">
                    <div>
                        <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" required>
                        <button type="button" id="auto-complete-btn">Auto completar</button>
                    </div>
                    <div>
                        <label for="nombre">Apellido y Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div>
                        <label for="edad">Edad:</label>
                        <input type="number" id="edad" name="edad" required>
                    </div>
                    <div>
                        <label for="carrera">Carrera:</label>
                        <select id="carrera" name="carrera" required>
                            <?php
                            $query = "SELECT id_carrera, nombre FROM carrera";
                            $result = $enlace->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id_carrera']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="semestre">Semestre:</label>
                        <select id="semestre" name="semestre" required>
                            <?php
                            $query = "SELECT id_semestre, nombre FROM semestres";
                            $result = $enlace->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id_semestre']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="turno">Turno:</label>
                        <select id="turno" name="turno" required>
                            <?php
                            $query = "SELECT id_turno, nombre FROM turnos";
                            $result = $enlace->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id_turno']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" maxlength="100">
                    </div>
                    <div>
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" maxlength="9" name="telefono">
                    </div>
                    <div>
                        <label for="vive_con">Vive con:</label>
                        <textarea id="vive_con" name="vive_con" maxlength="100"></textarea>
                    </div>
                    <div>
                        <label for="antecedentes">Antecedentes:</label>
                        <textarea id="antecedentes" name="antecedentes" maxlength="100"></textarea>
                    </div>
                    <div>
                        <label for="motivo_consulta">Motivo de Consulta:</label>
                        <textarea id="motivo_consulta" name="motivo_consulta" maxlength="100" required></textarea>
                    </div>
                    <div>
                        <label for="correo_estudiantil">Correo Institucional:</label>
                        <input type="email" id="correo_estudiantil" name="correo_estudiantil">
                    </div>
                    <div>
                        <label for="tratamiento">Tratamiento:</label>
                        <textarea id="tratamiento" name="tratamiento" maxlength="255"></textarea>
                    </div>
                    <div>
                        <label for="sesiones">Número de Sesiones:</label>
                        <input type="text" id="sesiones" name="sesiones" value="1" readonly>
                    </div>
                    <div class="file-foto-container">
                        <label for="foto_evidencia" class="file-foto-label">
                            <i class='bx bx-image-add'></i>
                            <span>Seleccionar Foto</span>
                        </label>
                        <input type="file" id="foto_evidencia" name="foto_evidencia" accept="image/*">
                        <span id="file-name" class="file-name">Ningún archivo seleccionado</span>
                    </div>
                </div>
                <div>
                    <div>
                        <h3>Actividades Realizadas (Opcional)</h3>
                        <div id="actividades-container">
                        </div>
                        <button type="button" id="add-actividad">+ Agregar Actividad</button>
                    </div>
                    <div>
                        <h3>Agrega cita</h3>
                        <br>
                        <div id="citas-container">
                        </div>
                        <button type="button" id="add-cita">+ Agregar nueva cita</button><br>
                    </div>
                </div>
               <button type="submit">Guardar</button>
            </form>
        </section>
    </main>
    <script>
        document.getElementById("add-actividad").addEventListener("click", function() {
            const container = document.getElementById("actividades-container");

            const actividadItem = document.createElement("div");
            actividadItem.classList.add("actividad-item");
            actividadItem.style.marginBottom = "15px";

            actividadItem.innerHTML = `
                <div>
                    <label for="actividad[]">Actividad:</label>
                    <textarea name="actividad[]" rows="2"  maxlength="100" required></textarea>
                </div>
                <button type="button" class="remove-actividad">Eliminar</button>
            `;

            actividadItem.querySelector(".remove-actividad").addEventListener("click", function() {
                container.removeChild(actividadItem);
            });

            container.appendChild(actividadItem);
        });
        
        /*citas*/
        document.getElementById("add-cita").addEventListener("click", function() {
            const container = document.getElementById("citas-container");
            const citaItem = document.createElement("div");
            citaItem.classList.add("cita-item");
            citaItem.style.marginBottom = "15px";
            const today = new Date().toISOString().split("T")[0];
            citaItem.innerHTML = `
                <div>
                    <label for="fecha[]">Fecha de la Cita:</label>
                    <input type="date" name="fecha[]" min="${today}" required>
                </div>
                <div>
                    <label for="hora[]">Hora de la Cita:</label>
                    <input type="time" name="hora[]" required>
                </div>
                <button type="button" class="remove-cita">Eliminar</button>
            `;
            citaItem.querySelector(".remove-cita").addEventListener("click", function() {
                container.removeChild(citaItem);
            });
            container.appendChild(citaItem);
        });
        // Mostrar nombre del archivo seleccionado
            document.addEventListener('DOMContentLoaded', (event) => {
            const fileInput = document.getElementById('foto_evidencia');
            const fileNameDisplay = document.getElementById('file-name');

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    fileNameDisplay.textContent = file.name;
                } else {
                    fileNameDisplay.textContent = 'Ningún archivo seleccionado';
                }
            });
        });
        
        document.getElementById('auto-complete-btn').addEventListener('click', function() {
            const dni = document.getElementById('dni').value;
            if (dni.length > 0) {
                fetch(`acciones.php?dni=${dni}`)
                    .then(response => response.json())
                    .then(data => {
                        const sesionesInput = document.getElementById('sesiones');
                        // Si NO hay registros para ese DNI → primera vez = sesión 1
                        if (data.error) {
                            sesionesInput.value = '1';
                            alert(data.error);
                            return;
                        }
                            document.getElementById('nombre').value = data.apellidos_nombres || '';
                            document.getElementById('edad').value = data.edad || '';
                            document.getElementById('direccion').value = data.direccion || '';
                            document.getElementById('telefono').value = data.telefono || '';
                            document.getElementById('vive_con').value = data.vive_con || '';
                            document.getElementById('antecedentes').value = data.antecedentes || '';
                            document.getElementById('motivo_consulta').value = data.motivo_consulta || '';
                            document.getElementById('correo_estudiantil').value = data.correo_estudiantil || '';

                            // Calcular próxima sesión = última guardada + 1
                            const ultima = parseInt(data.sesiones || 0, 10);
                            sesionesInput.value = isNaN(ultima) ? '1' : String(ultima + 1);

                            const turnoSelect = document.getElementById('turno');
                            for (let option of turnoSelect.options) {
                                if (option.text === data.turno) {
                                    turnoSelect.value = option.value;
                                    break;
                                }
                            }

                            // Seleccionar carrera
                            const carreraSelect = document.getElementById('carrera');
                            for (let option of carreraSelect.options) {
                                if (option.text === data.carrera) {
                                    carreraSelect.value = option.value;
                                    break;
                                }
                            }

                            // Seleccionar semestre
                            const semestreSelect = document.getElementById('semestre');
                            for (let option of semestreSelect.options) {
                                if (option.text === data.semestre) {
                                    semestreSelect.value = option.value;
                                    break;
                                }
                            }
                        
                    })
                    .catch(error => {
                        console.error("Error al obtener datos:", error);
                        alert("Ocurrió un error al intentar autocompletar los campos.");
                    });

            } else {
                alert("Por favor, ingrese un DNI.");
            }
        });
    </script>
</body>

</html>