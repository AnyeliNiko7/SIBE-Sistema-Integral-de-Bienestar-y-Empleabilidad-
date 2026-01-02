<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Consejería') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';

$error = "";
$mensaje = "";

// Obtener lista de tutores
$sqlTutores = "SELECT id_tutor, nombre, dni FROM tutores WHERE estado = 'activo' ORDER BY nombre";
$tutores = $enlace->query($sqlTutores);

// Procesar formulario de nueva entrega
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_entrega'])) {
    $id_tutor = $_POST['tutor'];
    $tema = trim($_POST['tema']);
    $fecha_entrega = $_POST['fecha_entrega'];
    
    // Validaciones
    if (empty($id_tutor) || empty($tema) || empty($fecha_entrega)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Procesar archivo subido
        $evidencia = null;
        if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = $_FILES['evidencia']['name'];
            $tipo_archivo = $_FILES['evidencia']['type'];
            $tamano_archivo = $_FILES['evidencia']['size'];
            $archivo_temporal = $_FILES['evidencia']['tmp_name'];
            
            // Validar tipo de archivo
            $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $extensiones_permitidas)) {
                $error = "Tipo de archivo no permitido. Solo se permiten JPG, PNG, PDF, DOC y DOCX.";
            } elseif ($tamano_archivo > 100 * 1024 * 1024) { // 100MB máximo
                $error = "El archivo es demasiado grande. El tamaño máximo permitido es 100MB.";
            } else {
                // Generar nombre único para el archivo
                $evidencia = uniqid() . '_' . $nombre_archivo;
                $ruta_destino = '../../uploads/entregas/' . $evidencia;
                
                // Crear directorio si no existe
                if (!is_dir('../../uploads/entregas')) {
                    mkdir('../../uploads/entregas', 0777, true);
                }
                
                // Mover archivo
                if (!move_uploaded_file($archivo_temporal, $ruta_destino)) {
                    $error = "Error al subir el archivo.";
                    $evidencia = null;
                }
            }
        }
        
        if (empty($error)) {
            // Insertar en la base de datos
            $stmt = $enlace->prepare("INSERT INTO entregas (id_tutor, tema, fecha_entrega, evidencia) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_tutor, $tema, $fecha_entrega, $evidencia);
            
            if ($stmt->execute()) {
                $mensaje = "Entrega registrada correctamente.";
            } else {
                $error = "Error al registrar la entrega: " . $enlace->error;
            }
            $stmt->close();
        }
    }
}

// Procesar exportación a Excel
if (isset($_POST['exportar_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="entregas_' . date('Y-m-d') . '.xls"');
    
    $sqlExport = "SELECT t.nombre as tutor, t.dni, e.tema, e.fecha_entrega 
                  FROM entregas e 
                  JOIN tutores t ON e.id_tutor = t.id_tutor 
                  WHERE e.estado = 'activo' 
                  ORDER BY e.fecha_registro ASC";
    $resultExport = $enlace->query($sqlExport);
    
    echo "<table border='1'>";
    echo "<tr><th>N°</th><th>Nombre Tutor</th><th>DNI</th><th>Tema</th><th>Fecha de Entrega</th></tr>";
    
    $contador = 1;
    while ($row = $resultExport->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $contador . "</td>";
        echo "<td>" . htmlspecialchars($row['tutor']) . "</td>";
        echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tema']) . "</td>";
        echo "<td>" . $row['fecha_entrega'] . "</td>";
        echo "</tr>";
        $contador++;
    }
    echo "</table>";
    exit();
}

// Obtener listado de entregas (ordenadas por fecha de registro ascendente)
$sqlEntregas = "SELECT e.id, t.nombre, t.dni, e.tema, e.fecha_entrega, e.evidencia, e.fecha_registro
                FROM entregas e 
                JOIN tutores t ON e.id_tutor = t.id_tutor 
                WHERE e.estado = 'activo' 
                ORDER BY e.fecha_registro ASC";
$entregas = $enlace->query($sqlEntregas);

// Procesar búsqueda
$busqueda = "";
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $busqueda = $enlace->real_escape_string($_GET['buscar']);
    $sqlEntregas = "SELECT e.id, t.nombre, t.dni, e.tema, e.fecha_entrega, e.evidencia, e.fecha_registro
                    FROM entregas e 
                    JOIN tutores t ON e.id_tutor = t.id_tutor 
                    WHERE e.estado = 'activo' 
                    AND (t.nombre LIKE '%$busqueda%' OR t.dni LIKE '%$busqueda%' OR e.tema LIKE '%$busqueda%')
                    ORDER BY e.fecha_registro ASC";
    $entregas = $enlace->query($sqlEntregas);
}

// Contador para numeración
$numero = 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Entregas</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/consejeria.css?v=<?php echo filemtime('../../public/css/consejeria.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <link rel="icon" href="../../public/img/icono2.png">
    <style>
        /* Estilos específicos para entregas.php */
        .entregas-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .formulario-entrega {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .formulario-entrega h3 {
            margin-top: 0;
            color: #112872;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .fila-formulario {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .columna-formulario {
            flex: 1;
        }
        
        .columna-formulario label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #001f5a;
        }
        
        .columna-formulario input,
        .columna-formulario select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }
        
        .columna-formulario input:focus,
        .columna-formulario select:focus {
            outline: none;
            border-color: #053c8f;
        }
        
        .boton-crear {
            background-color: #053c8f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            display: block;
            margin: 0 auto;
        }
        
        .boton-crear:hover {
            background-color: #112872;
        }
        
        .listado-entregas {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .listado-entregas h3 {
            background: #112872;
            color: white;
            margin: 0;
            padding: 15px 20px;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            background: #f1f1f1;
            align-items: center;
            justify-content: space-between;
        }
        
        .botones-izquierda {
            display: flex;
            gap: 10px;
        }
        
        .buscar-derecha {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn-archivop, .btn-archivoe {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-archivop {
            background-color: #a71847;
        }
        
        .btn-archivop:hover {
            background-color: rgb(107, 0, 41);
        }
        
        .btn-archivoe {
            background-color: #053c8f;
        }
        
        .btn-archivoe:hover {
            background-color: #112872;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 250px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #053c8f;
        }
        
        .btn-buscar {
            background-color: #053c8f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-buscar:hover {
            background-color: #112872;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            border: 1px solid #112872;
            padding: 10px;
            text-align: center;
        }
        
        th {
            background-color: #112872;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .btn-ver {
            background-color: #053c8f;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
        
        .btn-ver:hover {
            background-color: #112872;
        }
        
        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            transition: opacity 0.5s ease;
        }
        
        .mensaje.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        
        .mensaje.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        
        .mensaje.oculto {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
        
        .numero-columna {
            width: 50px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../../includes/asideConsejeria.php'; ?>
    <?php include '../../includes/header.php'; ?>
    
    <main class="home-section">
        <div class="entregas-container">
            <div class="titulo">
                <h2>Gestión de Entregas</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="mensaje error" id="mensaje-error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje success" id="mensaje-success"><?= htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            
            <!-- Formulario para registrar entrega -->
            <div class="formulario-entrega">
                <h3>Registrar Entrega</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="fila-formulario">
                        <div class="columna-formulario">
                            <label for="tutor">Tutor:</label>
                            <select id="tutor" name="tutor" required>
                                <option value="">Seleccione un tutor</option>
                                <?php 
                                // Reiniciar el puntero del resultado de tutores
                                $tutores->data_seek(0);
                                while ($tutor = $tutores->fetch_assoc()): ?>
                                    <option value="<?= $tutor['id_tutor'] ?>">
                                        <?= htmlspecialchars($tutor['nombre']) ?> - <?= htmlspecialchars($tutor['dni']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="columna-formulario">
                            <label for="tema">Tema:</label>
                            <input type="text" id="tema" name="tema" placeholder="Ingrese el tema" required>
                        </div>
                    </div>
                    
                    <div class="fila-formulario">
                        <div class="columna-formulario">
                            <label for="fecha_entrega">Fecha de Entrega:</label>
                            <input type="date" id="fecha_entrega" name="fecha_entrega" required>
                        </div>
                        <div class="columna-formulario">
                            <label for="evidencia">Evidencia (foto o archivo):</label>
                            <input type="file" id="evidencia" name="evidencia" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                    </div>
                    
                    <button type="submit" name="guardar_entrega" class="boton-crear">Guardar Entrega</button>
                </form>
            </div>
            
            <!-- Listado de entregas -->
            <div class="listado-entregas">
                <h3>Listado de Entregas</h3>
                
                <div class="export-buttons">
                    <div class="botones-izquierda">
                        <form method="POST" action="" style="display: inline;">
                            <button type="submit" name="exportar_excel" class="btn-archivoe">
                                <i class='bx bxs-file-excel'></i> Excel
                            </button>
                        </form>
                        <button class="btn-archivoe" id="btnDescargarPDF">
                            <i class='bx bxs-file-pdf'></i> PDF
                        </button>
                        <button class="btn-archivop" onclick="window.print()">
                            <i class='bx bxs-printer'></i> Imprimir
                        </button>
                    </div>
                    
                    <div class="buscar-derecha">
                        <form method="GET" action="" style="display: flex; gap: 5px; align-items: center;">
                            <input type="text" name="buscar" class="search-input" placeholder="Buscar..." value="<?= htmlspecialchars($busqueda) ?>">
                            <button type="submit" class="btn-buscar"><i class='bx bx-search'></i> Buscar</button>
                        </form>
                    </div>
                </div>
                
                <table id="tablaEntregas">
                    <thead>
                        <tr>
                            <th class="numero-columna">N°</th>
                            <th>Nombre Tutor</th>
                            <th>DNI</th>
                            <th>Tema</th>
                            <th>Fecha de Entrega</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($entregas && $entregas->num_rows > 0): ?>
                            <?php 
                            $numero = 1;
                            while ($entrega = $entregas->fetch_assoc()): ?>
                                <tr>
                                    <td class="numero-columna"><?= $numero ?></td>
                                    <td><?= htmlspecialchars($entrega['nombre']) ?></td>
                                    <td><?= htmlspecialchars($entrega['dni']) ?></td>
                                    <td><?= htmlspecialchars($entrega['tema']) ?></td>
                                    <td><?= $entrega['fecha_entrega'] ?></td>
                                    <td>
                                        <?php if (!empty($entrega['evidencia'])): ?>
                                            <a href="../../uploads/entregas/<?= $entrega['evidencia'] ?>" target="_blank" class="btn-ver">Ver</a>
                                        <?php else: ?>
                                            <span>Sin archivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $numero++; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No se encontraron entregas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Ocultar mensajes después de 3 segundos
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const mensajeError = document.getElementById('mensaje-error');
                const mensajeSuccess = document.getElementById('mensaje-success');
                
                if (mensajeError) {
                    mensajeError.classList.add('oculto');
                }
                
                if (mensajeSuccess) {
                    mensajeSuccess.classList.add('oculto');
                }
            }, 3000);
        });

        // ================== EXPORTAR A PDF ==================
        document.getElementById('btnDescargarPDF').addEventListener('click', () => {
            const tabla = document.getElementById('tablaEntregas');
            const filas = tabla.querySelectorAll('tr');
            const datos = [];
            
            // Agregar encabezados
            const encabezados = [];
            tabla.querySelectorAll('thead th').forEach(th => {
                if (th.textContent !== 'Acciones') { // Excluir columna de acciones
                    encabezados.push(th.textContent);
                }
            });
            datos.push(encabezados);
            
            // Agregar datos de las filas
            tabla.querySelectorAll('tbody tr').forEach(fila => {
                const filaDatos = [];
                fila.querySelectorAll('td').forEach((td, index) => {
                    if (index !== 5) { // Excluir la última columna (acciones)
                        filaDatos.push(td.textContent.trim());
                    }
                });
                if (filaDatos.length > 0) {
                    datos.push(filaDatos);
                }
            });
            
            // Crear contenido HTML para el PDF
            let contenidoHTML = `
                <html>
                <head>
                    <title>Lista de Entregas</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #112872; padding: 8px; text-align: center; }
                        th { background-color: #112872; color: white; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .fecha { text-align: right; margin-bottom: 10px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Lista de Entregas</h2>
                        <p>Generado el: ${new Date().toLocaleDateString()}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
            `;
            
            // Agregar encabezados de tabla
            encabezados.forEach(encabezado => {
                contenidoHTML += `<th>${encabezado}</th>`;
            });
            
            contenidoHTML += `
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // Agregar filas de datos (empezando desde 1 para saltar los encabezados)
            for (let i = 1; i < datos.length; i++) {
                contenidoHTML += '<tr>';
                datos[i].forEach(dato => {
                    contenidoHTML += `<td>${dato}</td>`;
                });
                contenidoHTML += '</tr>';
            }
            
            contenidoHTML += `
                        </tbody>
                    </table>
                </body>
                </html>
            `;
            
            // Crear ventana para imprimir/guardar como PDF
            const ventana = window.open('', '_blank');
            ventana.document.write(contenidoHTML);
            ventana.document.close();
            
            // Esperar a que se cargue el contenido y luego imprimir/guardar como PDF
            ventana.onload = function() {
                ventana.print();
            };
        });
    </script>
</body>
</html>