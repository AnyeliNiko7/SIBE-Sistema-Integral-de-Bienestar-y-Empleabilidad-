<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Tópico') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';
if (isset($_GET['dni'])) {
    $dni = $_GET['dni'];
    $query = "
        SELECT rt.nombre, rt.edad, c.nombre AS carrera, s.nombre AS semestre 
        FROM registros_topico rt
        JOIN carrera c ON rt.id_carrera = c.id_carrera
        JOIN semestres s ON rt.id_semestre = s.id_semestre
        WHERE rt.dni = ?";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'No se encontraron datos para el DNI proporcionado.']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del registro principal
    $dni = $_POST['dni'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $edad = intval($_POST['edad'] ?? 0);
    $id_carrera = intval($_POST['carrera'] ?? 0);
    $id_semestre = intval($_POST['semestre'] ?? 0);
    $id_turno = intval($_POST['turno'] ?? 0);
    $sintomas = $_POST['sintomas'] ?? '';

    // Validaciones de datos
    if (!preg_match('/^\d{8}$/', $dni)) {
        die('Error: El DNI debe tener exactamente 8 dígitos.');
    }
    if (strlen($nombre) > 50) {
        die('Error: El nombre y apellido no puede exceder los 50 caracteres.');
    }
    if ($edad < 1 || $edad > 100) {
        die('Error: La edad debe estar entre 1 y 100 años.');
    }
    if (empty($dni) || empty($nombre) || $edad <= 0 || $id_carrera <= 0 || $id_semestre <= 0 || $id_turno <= 0 || empty($sintomas)) {
        die('Error: Datos incompletos o inválidos.');
    }
    $query = "
        INSERT INTO registros_topico (fecha, dni, nombre, edad, id_carrera, id_semestre, id_turno, sintomas) 
        VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $enlace->prepare($query);
    $stmt->bind_param("ssiiiss", $dni, $nombre, $edad, $id_carrera, $id_semestre, $id_turno, $sintomas);

    if ($stmt->execute()) {
        $id_registro_topico = $stmt->insert_id;

        $medicamentos = $_POST['medicamentos'] ?? [];

        foreach ($medicamentos as $id_medicamento) {
            if (!empty($id_medicamento)) {
                $query_medicamento = "
                    INSERT INTO registros_medicamentos (id_registro_topico, id_medicamento) 
                    VALUES (?, ?)";
                $stmt_medicamento = $enlace->prepare($query_medicamento);
                $stmt_medicamento->bind_param("ii", $id_registro_topico, $id_medicamento);
                $stmt_medicamento->execute();
            }
        }

        header("location: inicio.php");
    } else {
        echo "Error al guardar el registro: " . $stmt->error;
    }
    exit();
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
    <link rel="stylesheet" href="../../public/css/topico.css?v=<?php echo filemtime('../../public/css/topico.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideTopico.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section class="form-container">
            <h2>Agregar Nuevo Registro</h2>
            <form method="POST" action="acciones.php" class="styled-form">
                <div class="registro">
                    <div> <label for="dni">DNI:</label>
                        <input type="text" id="dni" name="dni" pattern="\d{8}" maxlength="8" title="El DNI debe tener exactamente 8 dígitos" required>
                        <button class="boton_auto" type="button" id="auto-complete-btn">Auto completar campos</button><br>
                    </div>
                    <div>
                        <label for="nombre">Apellido y Nombre:</label>
                        <input type="text" id="nombre" name="nombre" maxlength="50" title="El nombre y apellido no puede exceder los 50 caracteres" required>
                    </div>
                    <div>
                        <label for="edad">Edad:</label>
                        <input type="number" id="edad" name="edad" min="1" max="100" title="La edad debe estar entre 1 y 100 años" required>
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
                        </select><br>
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
                        </select><br>
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
                        </select><br>
                    </div>
                    <div class="full-width">
                        <label for="sintomas">Síntomas:</label>
                        <textarea id="sintomas" name="sintomas" required></textarea>
                    </div>
                </div>
                <div>
                    <h3>Agrega el medicamento si es necesario</h3><br>
                    <div id="medicamentos-container"></div>
                    <button type="button" id="add-medicamento">+ Agregar Medicamento</button><br>
                </div>
                <button type="submit">Guardar</button>
            </form>
        </section>
    </main>
    <script>
        document.getElementById("add-medicamento").addEventListener("click", function() {
            const container = document.getElementById("medicamentos-container");

            const medicamentoItem = document.createElement("div");
            medicamentoItem.classList.add("medicamento-item");

            medicamentoItem.innerHTML = `
        <label for="medicamento">Medicamento:</label>
        <select name="medicamentos[]" class="medicamento-select" required>
            <option value="" disabled selected>Seleccione un medicamento</option>
            <?php
            $query = "SELECT id, nombre FROM inventario_medicamentos";
            $result = $enlace->query($query);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
            }
            ?>
        </select>
        <button type="button" class="remove-medicamento">Eliminar</button>
    `;

            container.appendChild(medicamentoItem);
            
            medicamentoItem.querySelector(".remove-medicamento").addEventListener("click", function() {
                container.removeChild(medicamentoItem);
            });
        });
        document.getElementById('auto-complete-btn').addEventListener('click', function() {
            var dni = document.getElementById('dni').value;

            if (dni.length > 0) {
                fetch(`acciones.php?dni=${dni}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            document.getElementById('nombre').value = data.nombre;
                            document.getElementById('edad').value = data.edad;

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
                            // Seleccionar turno (si tienes el dato)
                            if (data.turno) {
                                const turnoSelect = document.getElementById('turno');
                                for (let option of turnoSelect.options) {
                                    if (option.text === data.turno) {
                                        turnoSelect.value = option.value;
                                        break;
                                    }
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error("Error al obtener datos:", error);
                    });
            } else {
                alert("Por favor, ingrese un DNI.");
            }
        });
    </script>
</body>

</html>