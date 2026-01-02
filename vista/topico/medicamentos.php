<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Tópico') {
    header("location: ../../index.php");
    exit();
}

require '../../config/conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre']);

        if (!empty($nombre)) {
            $query = "INSERT INTO inventario_medicamentos (nombre) VALUES (?)";
            $stmt = $enlace->prepare($query);
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: medicamentos.php");
        exit();
    }
    if ($accion === 'eliminar') {
        $id = intval($_POST['id']);
        $query_eliminar_registros = "DELETE FROM registros_medicamentos WHERE id_medicamento = ?";
        $stmt_eliminar = $enlace->prepare($query_eliminar_registros);
        $stmt_eliminar->bind_param("i", $id);
        $stmt_eliminar->execute();
        $stmt_eliminar->close();
        $query = "DELETE FROM inventario_medicamentos WHERE id = ?";
        $stmt = $enlace->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: medicamentos.php");
        exit();
    }
    if ($accion === 'editar') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);

        if (!empty($nombre)) {
            $query = "UPDATE inventario_medicamentos SET nombre = ? WHERE id = ?";
            $stmt = $enlace->prepare($query);
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: medicamentos.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicamentos</title>
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
        <section>
            <h2>Gestión de Medicamentos</h2>
            <div class="contenedor-formulario">
                <form id="form-crear" method="POST" onsubmit="return validarFormulario()">
                    <input type="hidden" name="accion" value="crear">
                    <div class="contenedor-campos">
                        <div class="campo">
                            <label for="nombre">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre del medicamento" maxlength="50">
                        </div>
                    </div>
                    <button class="guardar_me" type="submit">Guardar</button>
                </form>
            </div>
            <div class="busqueda">
                <div class="campo-busqueda">
                    <input type="text" id="buscador" placeholder="Buscar medicamento...">
                    <span class="clear-busqueda" onclick="document.getElementById('buscador').value = ''; document.getElementById('buscador').dispatchEvent(new Event('keyup'));">×</span>
                </div>
            </div>
            <table id="tabla-medicamentos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM inventario_medicamentos WHERE nombre != '' ORDER BY id DESC";
                    $resultado = $enlace->query($query);
                    $numero = 1;

                    while ($row = $resultado->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $numero++; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td>
                                <button class="btn-edit" onclick="abrirModalEditar(<?php echo $row['id']; ?>, '<?php echo $row['nombre']; ?>')">Editar</button>
                                <form method="POST" class="form-eliminar" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button class="btn-delete" type="submit" onclick="return confirmarEliminacion()">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModal()">×</button>
            <h3>Editar Medicamento</h3>
            <form id="form-editar" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="editar-id">

                <label for="editar-nombre">Nombre:</label>
                <input type="text" name="nombre" id="editar-nombre" maxlength="50" required>

                <div class="button-group">
                    <button type="submit" name="actualizar">Actualizar</button>
                    <button type="button" onclick="cerrarModal()">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function validarFormulario() {
            const nombre = document.getElementById('nombre').value.trim();

            if (!nombre) {
                alert("El campo nombre es obligatorio.");
                return false;
            }
            return true;
        }

        function confirmarEliminacion() {
            return confirm("¿Estás seguro de eliminar este registro?");
        }

        function abrirModalEditar(id, nombre) {
            document.getElementById('modal-editar').style.display = 'block';
            document.getElementById('editar-id').value = id;
            document.getElementById('editar-nombre').value = nombre;
        }

        function cerrarModal() {
            document.getElementById('modal-editar').style.display = 'none';
        }
        document.getElementById('buscador').addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tabla-medicamentos tbody tr');
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>

</html>