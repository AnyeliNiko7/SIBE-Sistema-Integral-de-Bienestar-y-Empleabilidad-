<?php
// ========================= INICIO DEL SCRIPT PHP =========================

// Inicia la sesión para manejar variables de usuario
session_start();

// Verifica que el usuario haya iniciado sesión y que pertenezca al área de Tópico
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Tópico') {
    header("location: ../../index.php"); // Redirige al login si no cumple
    exit(); // Detiene la ejecución
}

// Incluye la conexión a la base de datos
require_once '../../config/conexion.php';

// Verificar si la conexión se estableció correctamente
if (!isset($enlace) || $enlace === null) {
    die("Error: No se pudo establecer conexión con la base de datos.");
}

// Obtiene el parámetro de agrupación desde GET (puede ser 'dia', 'mes' o 'anio'), por defecto es 'dia'
$agrupacion = $_GET['agrupacion'] ?? 'dia';
$dia_seleccionado = $_GET['dia'] ?? date('d');
$mes_seleccionado = $_GET['mes'] ?? date('m');
$anio_seleccionado = $_GET['anio'] ?? date('Y');

// Define cómo se agruparán los resultados según el filtro seleccionado
switch ($agrupacion) {
    case 'mes':
        $groupBy = "DATE_FORMAT(rt.fecha, '%Y-%m')";
        $format = '%m-%Y';
        $selectField = "DATE_FORMAT(rt.fecha, '%Y-%m') AS agrupacion";
        // Filtro por mes
        $filtro_fecha = "AND MONTH(rt.fecha) = '$mes_seleccionado' AND YEAR(rt.fecha) = '$anio_seleccionado'";
        break;
    case 'anio':
        $groupBy = "YEAR(rt.fecha)";
        $format = '%Y';
        $selectField = "YEAR(rt.fecha) AS agrupacion";
        // Filtro por año
        $filtro_fecha = "AND YEAR(rt.fecha) = '$anio_seleccionado'";
        break;
    default:
        $groupBy = "DATE(rt.fecha)";
        $format = '%d-%m-%Y';
        $selectField = "DATE(rt.fecha) AS agrupacion";
        // Filtro por día
        $filtro_fecha = "AND DATE(rt.fecha) = '$anio_seleccionado-$mes_seleccionado-$dia_seleccionado'";
}

// Consulta SQL que obtiene los registros activos de tópico
$query = "SELECT $selectField, rt.fecha, rt.id, rt.dni, rt.nombre, 
                 c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno, rt.sintomas 
          FROM registros_topico rt 
          JOIN carrera c ON rt.id_carrera = c.id_carrera 
          JOIN semestres s ON rt.id_semestre = s.id_semestre 
          JOIN turnos t ON rt.id_turno = t.id_turno 
          WHERE rt.estado = 'activo' 
          $filtro_fecha
          ORDER BY rt.fecha DESC";

// Ejecuta la consulta
$resultado = $enlace->query($query);

// Verificar si la consulta fue exitosa
if ($resultado === false) {
    die("Error en la consulta: " . $enlace->error);
}

// Array de meses en español
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Generar opciones para días (1-31)
$dias = range(1, 31);
// Generar opciones para años (2020-2026)
$anios = range(2020, 2026);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Información Tópico</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/topico.css?v=<?php echo filemtime('../../public/css/topico.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="../../public/js/script.js" defer></script>
    <style>
        .filtros-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: end;
        }
        .filtro-group {
            display: flex;
            flex-direction: column;
        }
        .filtro-group label {
            color: #000983ff;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .select-filtro {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-filtrar {
            padding: 8px 16px;
            background-color: #053c8f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            height: 36px;
        }
        .btn-filtrar:hover {
            background-color: #112872;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php include '../../includes/asideTopico.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <h2>Visualización de registros de tópico</h2>

            <!-- Formulario para seleccionar el tipo de agrupación -->
            <form method="GET" action="informacion.php" class="form-filtro">
                <input type="hidden" name="agrupacion" id="agrupacionHidden" value="<?= $agrupacion ?>">
                
                <div class="filtros-container">
                    <div class="filtro-group">
                        <label for="tipoAgrupacion">Agrupar por:</label>
                        <select name="tipoAgrupacion" id="tipoAgrupacion" class="select-agrupacion">
                            <option value="dia" <?= $agrupacion == 'dia' ? 'selected' : '' ?>>Día</option>
                            <option value="mes" <?= $agrupacion == 'mes' ? 'selected' : '' ?>>Mes</option>
                            <option value="anio" <?= $agrupacion == 'anio' ? 'selected' : '' ?>>Año</option>
                        </select>
                    </div>

                    <!-- Selector de Día (visible solo cuando agrupación es por día) -->
                    <div class="filtro-group" id="filtroDia" style="<?= $agrupacion != 'dia' ? 'display: none;' : '' ?>">
                        <label for="dia">Día:</label>
                        <select name="dia" id="dia" class="select-filtro">
                            <?php foreach ($dias as $dia): ?>
                                <option value="<?= sprintf('%02d', $dia) ?>" <?= $dia_seleccionado == sprintf('%02d', $dia) ? 'selected' : '' ?>>
                                    <?= $dia ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selector de Mes (visible cuando agrupación es por día o mes) -->
                    <div class="filtro-group" id="filtroMes" style="<?= $agrupacion == 'anio' ? 'display: none;' : '' ?>">
                        <label for="mes">Mes:</label>
                        <select name="mes" id="mes" class="select-filtro">
                            <?php foreach ($meses as $num => $nombre): ?>
                                <option value="<?= sprintf('%02d', $num) ?>" <?= $mes_seleccionado == sprintf('%02d', $num) ? 'selected' : '' ?>>
                                    <?= $nombre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selector de Año (siempre visible) -->
                    <div class="filtro-group" id="filtroAnio">
                        <label for="anio">Año:</label>
                        <select name="anio" id="anio" class="select-filtro">
                            <?php foreach ($anios as $anio): ?>
                                <option value="<?= $anio ?>" <?= $anio_seleccionado == $anio ? 'selected' : '' ?>>
                                    <?= $anio ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-filtrar">Filtrar</button>
                </div>
            </form>

            <!-- Botones para exportar la tabla -->
            <div class="button-group">
                <button id="btnDescargarPDF">
                    <i class="bx bxs-file-pdf"></i> Descargar PDF
                </button>
                <button id="btnDescargarExcel">
                    <i class="bx bxs-file"></i> Descargar Excel
                </button>
            </div>

            <!-- Tabla de registros -->
            <div class="tabla-container">
                <table class="tabla-registros" id="tablaRegistros">
                    <tbody>
                        <?php
                        if ($resultado->num_rows > 0) {
                            $i = 1;
                            $ultimaAgrupacion = '';
                            while ($fila = $resultado->fetch_assoc()):
                                $agrupacionActual = $fila['agrupacion'];

                                if ($agrupacion == 'mes') {
                                    $mes = date('n', strtotime($fila['fecha']));
                                    $nombreMes = $meses[$mes];
                                    $fecha = $nombreMes . ' ' . date('Y', strtotime($fila['fecha']));
                                } else {
                                    $fecha = $agrupacionActual;
                                }

                                if ($agrupacionActual !== $ultimaAgrupacion): ?>
                                    <tr>
                                        <th colspan="7" class="fecha-agrupacion">
                                            Fecha: <?= $fecha ?>
                                        </th>
                                    </tr>
                                    <tr class="tabla-header">
                                        <th>Nº</th>
                                        <th>DNI</th>
                                        <th>Apellidos y Nombres</th>
                                        <th>Carrera</th>
                                        <th>Semestre</th>
                                        <th>Turno</th>
                                        <th>Síntomas</th>
                                    </tr>
                                <?php
                                    $ultimaAgrupacion = $agrupacionActual;
                                endif; ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($fila['dni']) ?></td>
                                    <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                    <td><?= htmlspecialchars($fila['carrera']) ?></td>
                                    <td><?= htmlspecialchars($fila['semestre']) ?></td>
                                    <td><?= htmlspecialchars($fila['turno']) ?></td>
                                    <td><?= htmlspecialchars($fila['sintomas']) ?></td>
                                </tr>
                            <?php endwhile;
                        } else {
                            echo '<tr><td colspan="7" style="text-align: center; padding: 20px;">No se encontraron registros para los filtros seleccionados.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Mostrar/ocultar filtros según el tipo de agrupación
        document.getElementById('tipoAgrupacion').addEventListener('change', function() {
            const tipo = this.value;
            document.getElementById('agrupacionHidden').value = tipo;
            
            // Mostrar/ocultar filtros según la selección
            document.getElementById('filtroDia').style.display = tipo === 'dia' ? 'flex' : 'none';
            document.getElementById('filtroMes').style.display = tipo === 'anio' ? 'none' : 'flex';
        });

        // ================== EXPORTAR A PDF ==================
        document.getElementById('btnDescargarPDF').addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const nombreUsuario = "<?= $_SESSION['usuario'] ?>";

            doc.setFont("helvetica", "bold");
            doc.setFontSize(12);
            doc.text(`Generado por: ${nombreUsuario}`, 14, 15);

            const tableConfig = {
                head: [["Nº", "DNI", "Apellidos y Nombres", "Carrera", "Semestre", "Turno", "Síntomas"]],
                body: [],
                theme: "grid",
                styles: {
                    lineColor: [211, 211, 211],
                    lineWidth: 0.1,
                    fontSize: 10,
                    valign: "middle",
                    halign: "center",
                    textColor: [51, 51, 51],
                },
                headStyles: {
                    fillColor: [72, 84, 96],
                    textColor: [255, 255, 255],
                    fontSize: 12,
                    fontStyle: "bold"
                },
                bodyStyles: {
                    fillColor: [255, 255, 255],
                    textColor: [51, 51, 51],
                },
                alternateRowStyles: { fillColor: [245, 245, 245] },
                startY: 25,
            };

            const tabla = document.getElementById('tablaRegistros');
            const filas = tabla.querySelectorAll('tr');
            let agrupacionActual = '';

            filas.forEach((fila) => {
                if (fila.querySelector('th[colspan="7"]')) {
                    agrupacionActual = fila.textContent.trim();
                    tableConfig.body.push([{
                        content: `Fecha: ${agrupacionActual}`,
                        colSpan: 7,
                        styles: {
                            fillColor: [102, 128, 153],
                            textColor: [255, 255, 255],
                            halign: "left",
                            fontStyle: "bold"
                        }
                    }]);
                } else if (!fila.querySelector('th')) {
                    const datos = Array.from(fila.children).map(cell => cell.textContent.trim());
                    tableConfig.body.push(datos);
                }
            });

            doc.autoTable(tableConfig);
            doc.save('tabla_informacion_topico.pdf');
        });

        // ================== EXPORTAR A EXCEL ==================
        document.getElementById('btnDescargarExcel').addEventListener('click', () => {
            const tabla = document.getElementById('tablaRegistros');
            if (!tabla) {
                alert("No se encontró la tabla para exportar.");
                return;
            }

            const filas = tabla.querySelectorAll('tr');
            const datos = [];
            const nombreUsuario = "<?= $_SESSION['usuario'] ?>";

            datos.push([`Generado por: ${nombreUsuario}`]);
            datos.push([]);

            filas.forEach((fila) => {
                const celdas = fila.querySelectorAll('th, td');
                const filaDatos = Array.from(celdas).map(celda => celda.textContent.trim());
                datos.push(filaDatos);
            });

            if (datos.length <= 2) {
                alert("No hay datos en la tabla para exportar.");
                return;
            }

            const ws = XLSX.utils.aoa_to_sheet(datos);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Registros Tópico');

            const nombreArchivo = `Registros_Tópico.xlsx`;
            XLSX.writeFile(wb, nombreArchivo);
        });
    </script>
</body>
</html>