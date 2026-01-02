<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../inicio.php");
    exit();
}
include '../../config/conexion.php';

$area = isset($_GET['area']) ? $_GET['area'] : '';
// CORRECCIÓN: Inicializar filtrar_por_fecha como vacío y solo activar si está marcado
$filtrar_por_fecha = isset($_GET['filtrar_fecha']) && $_GET['filtrar_fecha'] === '1' ? true : false;
$tipo_filtro = isset($_GET['tipo_filtro']) ? $_GET['tipo_filtro'] : 'dia';
$fecha_filtro = isset($_GET['fecha_filtro']) ? $_GET['fecha_filtro'] : '';

$resultado = null;
$titulo = '';
$columnas = [];
$datosGraficoTurno = [];
$datosGraficoSemestre = [];
$datosGraficoCarrera = [];
$datosExportar = [];

// Construir condición WHERE para fecha si se activó el filtro
$condicion_fecha = "";
if ($filtrar_por_fecha && $fecha_filtro) {
    switch ($tipo_filtro) {
        case 'dia':
            $condicion_fecha = " AND DATE(fecha) = '$fecha_filtro'";
            break;
        case 'mes':
            // CORRECCIÓN: Formato correcto para filtro por mes
            $fecha_mes = date('Y-m', strtotime($fecha_filtro));
            $condicion_fecha = " AND DATE_FORMAT(fecha, '%Y-%m') = '$fecha_mes'";
            break;
        case 'anio':
            // CORRECCIÓN: Formato correcto para filtro por año
            $condicion_fecha = " AND YEAR(fecha) = '$fecha_filtro'";
            break;
    }
}

// Selección dinámica de consultas
if ($area === 'Consejeria') {
    $titulo = "Reporte de Consejería (Tutores)";
    // AGREGAR $condicion_fecha en el WHERE
    $sql = "SELECT t.nombre, t.dni, t.correo, t.telefono, 
                   c.nombre AS carrera, s.nombre AS semestre, tr.nombre AS turno,
                   t.fecha  -- Agregar fecha para mostrar en la tabla
            FROM tutores t
            LEFT JOIN carrera c ON t.id_carrera = c.id_carrera
            LEFT JOIN semestres s ON t.id_semestre = s.id_semestre
            LEFT JOIN turnos tr ON t.id_turno = tr.id_turno
            WHERE t.estado = 'activo' $condicion_fecha";
    $resultado = $enlace->query($sql);
    $columnas = ['Nombre', 'DNI', 'Correo', 'Teléfono', 'Carrera', 'Semestre', 'Turno', 'Fecha']; // Agregar Fecha

    // Almacenar datos para exportación
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $datosExportar[] = $fila;
        }
        $resultado->data_seek(0);
    }

    // Datos para gráficos
    $sqlTurno = "SELECT tr.nombre AS turno, COUNT(*) AS total
                 FROM tutores t
                 JOIN turnos tr ON t.id_turno = tr.id_turno
                 WHERE t.estado='activo' $condicion_fecha  -- ← AGREGAR AQUÍ
                 GROUP BY tr.nombre";
    $datosGraficoTurno = $enlace->query($sqlTurno)->fetch_all(MYSQLI_ASSOC);

    $sqlSemestre = "SELECT s.nombre AS semestre, COUNT(*) AS total
                    FROM tutores t
                    JOIN semestres s ON t.id_semestre = s.id_semestre
                    WHERE t.estado='activo' $condicion_fecha  -- ← AGREGAR AQUÍ
                    GROUP BY s.nombre";
    $datosGraficoSemestre = $enlace->query($sqlSemestre)->fetch_all(MYSQLI_ASSOC);

    $sqlCarrera = "SELECT c.nombre AS carrera, COUNT(*) AS total
                   FROM tutores t
                   JOIN carrera c ON t.id_carrera = c.id_carrera
                   WHERE t.estado='activo' $condicion_fecha  -- ← AGREGAR AQUÍ
                   GROUP BY c.nombre";
    $datosGraficoCarrera = $enlace->query($sqlCarrera)->fetch_all(MYSQLI_ASSOC);

} elseif ($area === 'Jefatura') {
    $titulo = "Reporte de Jefatura (Orientación Vocacional)";
    // AGREGAR $condicion_fecha en el WHERE
    $sql = "SELECT ov.apellidos_nombres, ov.celular, ov.colegio, c.nombre AS carrera,
                   ov.fecha  -- Agregar fecha para mostrar en la tabla
            FROM orientacion_vocacional ov
            LEFT JOIN carrera c ON ov.id_carrera = c.id_carrera
            WHERE ov.estado = 'activo' $condicion_fecha";  // ← AGREGAR AQUÍ

    $resultado = $enlace->query($sql);
    $columnas = ['Apellidos y Nombres', 'Celular', 'Colegio', 'Carrera', 'Fecha'];

    // Almacenar datos para exportación
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $datosExportar[] = $fila;
        }
        $resultado->data_seek(0);
    }

    // Datos para gráficos
    $sqlCarrera = "SELECT c.nombre AS carrera, COUNT(*) AS total
                   FROM orientacion_vocacional ov
                   JOIN carrera c ON ov.id_carrera = c.id_carrera
                   WHERE ov.estado='activo' $condicion_fecha  -- ← AGREGAR AQUÍ
                   GROUP BY c.nombre";
    $datosGraficoCarrera = $enlace->query($sqlCarrera)->fetch_all(MYSQLI_ASSOC);

    $datosGraficoTurno = [];
    $datosGraficoSemestre = [];

} elseif ($area === 'Psicologia') {
    $titulo = "Reporte de Psicología";
    $sql = "SELECT rp.dni, rp.apellidos_nombres, rp.edad, rp.telefono, 
                   c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno,
                   rp.fecha
            FROM registros_psicologia rp
            JOIN carrera c ON rp.id_carrera = c.id_carrera
            JOIN semestres s ON rp.id_semestre = s.id_semestre
            JOIN turnos t ON rp.id_turno = t.id_turno
            WHERE rp.estado = 'activo' $condicion_fecha";

    // DEBUG: Mostrar consulta para verificar
    // echo "Consulta Psicología: " . $sql;

    $resultado = $enlace->query($sql);
    $columnas = ['DNI', 'Nombre', 'Edad', 'Teléfono', 'Carrera', 'Semestre', 'Turno', 'Fecha'];

    // Almacenar datos para exportación
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $datosExportar[] = $fila;
        }
        // Reiniciar el resultado para mostrarlo en la página
        $resultado->data_seek(0);
    }

    // Datos para gráficos
    $sqlTurno = "SELECT t.nombre AS turno, COUNT(*) AS total
                 FROM registros_psicologia rp
                 JOIN turnos t ON rp.id_turno = t.id_turno
                 WHERE rp.estado='activo' $condicion_fecha
                 GROUP BY t.nombre";
    $datosGraficoTurno = $enlace->query($sqlTurno)->fetch_all(MYSQLI_ASSOC);

    $sqlSemestre = "SELECT s.nombre AS semestre, COUNT(*) AS total
                    FROM registros_psicologia rp
                    JOIN semestres s ON rp.id_semestre = s.id_semestre
                    WHERE rp.estado='activo' $condicion_fecha
                    GROUP BY s.nombre";
    $datosGraficoSemestre = $enlace->query($sqlSemestre)->fetch_all(MYSQLI_ASSOC);

    $sqlCarrera = "SELECT c.nombre AS carrera, COUNT(*) AS total
                   FROM registros_psicologia rp
                   JOIN carrera c ON rp.id_carrera = c.id_carrera
                   WHERE rp.estado='activo' $condicion_fecha
                   GROUP BY c.nombre";
    $datosGraficoCarrera = $enlace->query($sqlCarrera)->fetch_all(MYSQLI_ASSOC);

} elseif ($area === 'Topico') {
    $titulo = "Reporte de Tópico";
    $sql = "SELECT rt.dni, rt.nombre, rt.edad, 
                   c.nombre AS carrera, s.nombre AS semestre, t.nombre AS turno,
                   rt.sintomas, rt.ultima_actualizacion, rt.fecha
            FROM registros_topico rt
            JOIN carrera c ON rt.id_carrera = c.id_carrera
            JOIN semestres s ON rt.id_semestre = s.id_semestre
            JOIN turnos t ON rt.id_turno = t.id_turno
            WHERE rt.estado = 'activo' $condicion_fecha";

    // DEBUG: Mostrar consulta para verificar
    // echo "Consulta Tópico: " . $sql;

    $resultado = $enlace->query($sql);
    $columnas = ['DNI', 'Nombre', 'Edad', 'Carrera', 'Semestre', 'Turno', 'Síntomas', 'Última Actualización', 'Fecha'];

    // Almacenar datos para exportación
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $datosExportar[] = $fila;
        }
        // Reiniciar el resultado para mostrarlo en la página
        $resultado->data_seek(0);
    }

    // Datos para gráficos
    $sqlTurno = "SELECT t.nombre AS turno, COUNT(*) AS total
                 FROM registros_topico rt
                 JOIN turnos t ON rt.id_turno = t.id_turno
                 WHERE rt.estado='activo' $condicion_fecha
                 GROUP BY t.nombre";
    $datosGraficoTurno = $enlace->query($sqlTurno)->fetch_all(MYSQLI_ASSOC);

    $sqlSemestre = "SELECT s.nombre AS semestre, COUNT(*) AS total
                    FROM registros_topico rt
                    JOIN semestres s ON rt.id_semestre = s.id_semestre
                    WHERE rt.estado='activo' $condicion_fecha
                    GROUP BY s.nombre";
    $datosGraficoSemestre = $enlace->query($sqlSemestre)->fetch_all(MYSQLI_ASSOC);

    $sqlCarrera = "SELECT c.nombre AS carrera, COUNT(*) AS total
                   FROM registros_topico rt
                   JOIN carrera c ON rt.id_carrera = c.id_carrera
                   WHERE rt.estado='activo' $condicion_fecha
                   GROUP BY c.nombre";
    $datosGraficoCarrera = $enlace->query($sqlCarrera)->fetch_all(MYSQLI_ASSOC);
}

// Calcular totales para porcentajes
$totalTurno = 0;
$totalSemestre = 0;
$totalCarrera = 0;

if (!empty($datosGraficoTurno)) {
    foreach ($datosGraficoTurno as $item) {
        $totalTurno += (int) $item['total'];
    }
}

if (!empty($datosGraficoSemestre)) {
    foreach ($datosGraficoSemestre as $item) {
        $totalSemestre += (int) $item['total'];
    }
}

if (!empty($datosGraficoCarrera)) {
    foreach ($datosGraficoCarrera as $item) {
        $totalCarrera += (int) $item['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet"
        href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo filemtime('../../public/css/admin.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .graficos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .grafico-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .grafico-item h4 {
            margin-bottom: 15px;
            color: #112872;
            font-size: 16px;
        }

        .canvas-container {
            position: relative;
            height: 200px;
            margin: 0 auto;
        }

        .leyenda-grafico {
            margin-top: 15px;
            font-size: 14px;
            text-align: left;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .leyenda-item {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .color-box {
            width: 15px;
            height: 15px;
            display: inline-block;
            margin-right: 8px;
            border-radius: 3px;
        }

        /* Estilos para el botón de exportar gráficos */
        .download-btn-container {
            text-align: center;
            margin: 20px 0;
        }

        .download-btn {
            background-color: #053c8f;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .download-btn:hover {
            background-color: #112872;
        }

        /* Estilos para el filtro de fecha */
        .filtro-fecha {
            padding: 15px;
            margin: 5px 0;
        }

        .filtro-fecha .filtro-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .filtro-fecha .filtro-header input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #053c8f;
        }

        .filtro-fecha .filtro-header label {
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
            margin: 0;
            font-size: 16px;
        }

        .filtro-fecha .filtro-options {
            display: grid;
            grid-template-columns: auto auto 1fr auto;
            gap: 15px;
            align-items: center;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filtro-fecha .filtro-options.hidden {
            display: none;
        }

        .filtro-fecha .filtro-group {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .filtro-fecha .filtro-group label {
            font-weight: 500;
            color: #2c4d6e;
            font-size: 14px;
            margin: 0;
            min-width: max-content;
        }

        .filtro-fecha select,
        .filtro-fecha input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            min-width: 160px;
            background: white;
        }

        .filtro-fecha select:focus,
        .filtro-fecha input:focus {
            outline: none;
            border-color: #053c8f;
            box-shadow: 0 0 0 3px rgba(5, 60, 143, 0.1);
        }

        .filtro-fecha .button-group {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
        }

        .filtro-fecha button[type="submit"] {
            background-color: #053c8f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            font-weight: 500;
        }

        .filtro-fecha button[type="submit"]:hover {
            background-color: #112872;
        }

        .filtro-fecha button[type="button"] {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            font-weight: 500;
        }

        .filtro-fecha button[type="button"]:hover {
            background-color: #5a6268;
        }

        /* ESTILOS ACTUALIZADOS PARA BOTONES DE EXPORTAR */
        .agregar {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        /* Botón Excel - VERDE */
        .btn-agregar.excel-btn {
            background-color: #009758ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-agregar.excel-btn:hover {
            background-color: #00774fff;
        }

        /* Botón PDF - ROJO */
        .btn-agregar.pdf-btn {
            background-color: #da1d30ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-agregar.pdf-btn:hover {
            background-color: #9b0615ff;
        }

        /* Estilos para el PDF */
        .pdf-page {
            page-break-after: always;
            width: 100%;
            padding: 15px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .pdf-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .pdf-header h2 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
        }

        .pdf-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #7f8c8d;
        }

        .pdf-chart-container {
            width: 80%;
            height: 380px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pdf-analysis {
            width: 80%;
            margin: 20px auto;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.5;
        }

        .pdf-analysis h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 16px;
        }

        .pdf-analysis ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .pdf-analysis li {
            margin-bottom: 5px;
        }

        .pdf-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #95a5a6;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
        }

        .total-count {
            font-weight: bold;
            color: #e74c3c;
        }
    </style>
</head>

<body>
    <?php include '../../includes/asideAdmin.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <h2>Reportes por Área</h2>

        <div class="form-container">
            <form method="get" action="reportes.php" class="styled-form">
                <div class="input-container">
                    <label>Seleccione un área:</label>
                    <select name="area" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Seleccione --</option>
                        <option value="Consejeria" <?= $area === 'Consejeria' ? 'selected' : ''; ?>>Consejería</option>
                        <option value="Jefatura" <?= $area === 'Jefatura' ? 'selected' : ''; ?>>Jefatura</option>
                        <option value="Psicologia" <?= $area === 'Psicologia' ? 'selected' : ''; ?>>Psicología</option>
                        <option value="Topico" <?= $area === 'Topico' ? 'selected' : ''; ?>>Tópico</option>
                    </select>
                </div>

                <!-- Filtro de fecha CORREGIDO -->
                <div class="filtro-fecha">
                    <div class="filtro-header">
                        <!-- CORRECCIÓN: Checkbox deseleccionado por defecto -->
                        <input type="checkbox" id="filtrar_fecha_check" name="filtrar_fecha" value="1"
                            <?= $filtrar_por_fecha ? 'checked' : '' ?> onchange="toggleFiltroFecha()">
                        <label for="filtrar_fecha_check">Filtrar por fecha</label>
                    </div>

                    <div class="filtro-options <?= $filtrar_por_fecha ? '' : 'hidden' ?>" id="opciones_fecha">
                        <div>
                            <label for="tipo_filtro">Tipo de filtro:</label>
                            <!-- CORRECCIÓN: Campo separado para tipo de filtro -->
                            <select name="tipo_filtro" id="tipo_filtro" onchange="cambiarTipoFecha()">
                                <option value="dia" <?= $tipo_filtro === 'dia' ? 'selected' : ''; ?>>Por día</option>
                                <option value="mes" <?= $tipo_filtro === 'mes' ? 'selected' : ''; ?>>Por mes</option>
                                <option value="anio" <?= $tipo_filtro === 'anio' ? 'selected' : ''; ?>>Por año</option>
                            </select>
                        </div>

                        <div id="campo_fecha">
                            <?php if ($tipo_filtro === 'dia'): ?>
                                <input type="date" name="fecha_filtro" value="<?= $fecha_filtro ?>">
                            <?php elseif ($tipo_filtro === 'mes'): ?>
                                <input type="month" name="fecha_filtro" value="<?= $fecha_filtro ?>">
                            <?php elseif ($tipo_filtro === 'anio'): ?>
                                <input type="number" name="fecha_filtro" min="2000" max="2030" value="<?= $fecha_filtro ?>"
                                    placeholder="Año (ej: 2024)">
                            <?php else: ?>
                                <input type="date" name="fecha_filtro" value="<?= $fecha_filtro ?>">
                            <?php endif; ?>
                        </div>

                        <button type="submit">Aplicar Filtro</button>
                        <?php if ($filtrar_por_fecha): ?>
                            <button type="button" onclick="limpiarFiltro()">Limpiar Filtro</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <div class="form-container">
                <h3><?= $titulo ?></h3>

                <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                    <div style="background: #e3f2fd; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                        <strong>Filtro aplicado:</strong>
                        <?php
                        if ($tipo_filtro === 'dia') {
                            echo "Día: " . date('d/m/Y', strtotime($fecha_filtro));
                        } elseif ($tipo_filtro === 'mes') {
                            echo "Mes: " . date('m/Y', strtotime($fecha_filtro . '-01'));
                        } elseif ($tipo_filtro === 'anio') {
                            echo "Año: " . $fecha_filtro;
                        }
                        ?>
                        - <strong>Total de registros:</strong> <?= $resultado->num_rows ?>
                    </div>
                <?php endif; ?>

                <div class="agregar">
                    <button class="btn-agregar excel-btn" onclick="exportarExcel()">
                        <i class='bx bx-file'></i> Exportar a Excel
                    </button>
                    <button class="btn-agregar pdf-btn" onclick="exportarPDF()">
                        <i class='bx bx-file'></i> Exportar a PDF
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table id="tablaReporte">
                        <thead>
                            <tr>
                                <?php foreach ($columnas as $col): ?>
                                    <th><?= $col ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($fila as $val): ?>
                                        <td><?= htmlspecialchars($val) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- SECCIÓN DE GRÁFICOS ESTADÍSTICOS -->
                <div class="graficos-container">
                    <!-- Gráfico por Turno -->
                    <?php if (!empty($datosGraficoTurno)): ?>
                        <div class="grafico-item">
                            <h4>Distribución por Turno</h4>
                            <div class="canvas-container">
                                <canvas id="graficoTurno"></canvas>
                            </div>
                            <div class="leyenda-grafico">
                                <?php foreach ($datosGraficoTurno as $item): ?>
                                    <div class="leyenda-item">
                                        <span class="color-box"
                                            style="background-color: <?= obtenerColorTurno($item['turno']) ?>"></span>
                                        <span><?= $item['turno'] ?>: <?= $item['total'] ?>
                                            (<?= round(($item['total'] / $totalTurno) * 100, 1) ?>%)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Gráfico por Semestre -->
                    <?php if (!empty($datosGraficoSemestre)): ?>
                        <div class="grafico-item">
                            <h4>Distribución por Semestre</h4>
                            <div class="canvas-container">
                                <canvas id="graficoSemestre"></canvas>
                            </div>
                            <div class="leyenda-grafico">
                                <?php foreach ($datosGraficoSemestre as $item): ?>
                                    <div class="leyenda-item">
                                        <span class="color-box"
                                            style="background-color: <?= obtenerColorSemestre($item['semestre']) ?>"></span>
                                        <span><?= $item['semestre'] ?>: <?= $item['total'] ?>
                                            (<?= round(($item['total'] / $totalSemestre) * 100, 1) ?>%)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Gráfico por Carrera -->
                    <?php if (!empty($datosGraficoCarrera)): ?>
                        <div class="grafico-item">
                            <h4>Distribución por Carrera</h4>
                            <div class="canvas-container">
                                <canvas id="graficoCarrera"></canvas>
                            </div>
                            <div class="leyenda-grafico">
                                <?php foreach ($datosGraficoCarrera as $item): ?>
                                    <div class="leyenda-item">
                                        <span class="color-box"
                                            style="background-color: <?= obtenerColorCarrera($item['carrera']) ?>"></span>
                                        <span><?= $item['carrera'] ?>: <?= $item['total'] ?>
                                            (<?= round(($item['total'] / $totalCarrera) * 100, 1) ?>%)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botón para exportar gráficos -->
                <div class="download-btn-container">
                    <button class="download-btn" onclick="exportarGraficosPDF()">
                        <i class='bx bx-download'></i> Exportar Gráficos a PDF
                    </button>
                </div>

                <script>

                    // Función para mostrar/ocultar el filtro de fecha
                    function toggleFiltroFecha() {
                        const opciones = document.getElementById('opciones_fecha');
                        const checkbox = document.getElementById('filtrar_fecha_check');

                        if (checkbox.checked) {
                            opciones.classList.remove('hidden');
                            // CORRECCIÓN: Cambiar automáticamente al tipo de filtro actual
                            cambiarTipoFecha();
                        } else {
                            opciones.classList.add('hidden');
                        }
                    }

                    // Función para cambiar el tipo de campo de fecha
                    function cambiarTipoFecha() {
                        const tipo = document.getElementById('tipo_filtro').value;
                        const campoFecha = document.getElementById('campo_fecha');

                        let html = '';
                        switch (tipo) {
                            case 'dia':
                                html = '<input type="date" name="fecha_filtro" value="<?= $fecha_filtro ?>">';
                                break;
                            case 'mes':
                                html = '<input type="month" name="fecha_filtro" value="<?= $fecha_filtro ?>">';
                                break;
                            case 'anio':
                                html = '<input type="number" name="fecha_filtro" min="2000" max="2030" value="<?= $fecha_filtro ?>" placeholder="Año (ej: 2024)">';
                                break;
                        }

                        campoFecha.innerHTML = html;
                    }

                    // Función para limpiar el filtro
                    function limpiarFiltro() {
                        window.location.href = 'reportes.php?area=<?= $area ?>';
                    }

                    // CORRECCIÓN: Inicializar el estado del filtro al cargar la página
                    document.addEventListener('DOMContentLoaded', function () {
                        // Asegurar que el checkbox esté en el estado correcto
                        const checkbox = document.getElementById('filtrar_fecha_check');
                        if (!checkbox.checked) {
                            document.getElementById('opciones_fecha').classList.add('hidden');
                        }

                        // Inicializar gráficos
                        inicializarGraficos();
                    });

                    // Función para inicializar los gráficos
                    function inicializarGraficos() {
                        <?php if (!empty($datosGraficoTurno)): ?>
                            // Gráfico de Turno
                            const ctxTurno = document.getElementById('graficoTurno').getContext('2d');
                            new Chart(ctxTurno, {
                                type: 'pie',
                                data: {
                                    labels: [<?php echo implode(',', array_map(function ($item) {
                                        return "'" . $item['turno'] . "'"; }, $datosGraficoTurno)); ?>],
                                    datasets: [{
                                        data: [<?php echo implode(',', array_column($datosGraficoTurno, 'total')); ?>],
                                        backgroundColor: [
                                            <?php
                                            foreach ($datosGraficoTurno as $item):
                                                echo "'" . obtenerColorTurno($item['turno']) . "',";
                                            endforeach;
                                            ?>
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function (context) {
                                                    const label = context.label || '';
                                                    const value = context.raw || 0;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = Math.round((value / total) * 100);
                                                    return `${label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        <?php endif; ?>

                        <?php if (!empty($datosGraficoSemestre)): ?>
                            // Gráfico de Semestre
                            const ctxSemestre = document.getElementById('graficoSemestre').getContext('2d');
                            new Chart(ctxSemestre, {
                                type: 'doughnut',
                                data: {
                                    labels: [<?php echo implode(',', array_map(function ($item) {
                                        return "'" . $item['semestre'] . "'"; }, $datosGraficoSemestre)); ?>],
                                    datasets: [{
                                        data: [<?php echo implode(',', array_column($datosGraficoSemestre, 'total')); ?>],
                                        backgroundColor: [
                                            <?php
                                            foreach ($datosGraficoSemestre as $item):
                                                echo "'" . obtenerColorSemestre($item['semestre']) . "',";
                                            endforeach;
                                            ?>
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function (context) {
                                                    const label = context.label || '';
                                                    const value = context.raw || 0;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = Math.round((value / total) * 100);
                                                    return `${label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        <?php endif; ?>
                        <?php if (!empty($datosGraficoCarrera)): ?>
                            // Gráfico de Carrera - AHORA ES CIRCULAR
                            const ctxCarrera = document.getElementById('graficoCarrera').getContext('2d');
                            new Chart(ctxCarrera, {
                                type: 'pie', // ← CAMBIADO A GRÁFICO CIRCULAR
                                data: {
                                    labels: [<?php echo implode(',', array_map(function ($item) {
                                        return "'" . $item['carrera'] . "'"; }, $datosGraficoCarrera)); ?>],
                                    datasets: [{
                                        data: [<?php echo implode(',', array_column($datosGraficoCarrera, 'total')); ?>],
                                        backgroundColor: [
                                            <?php
                                            foreach ($datosGraficoCarrera as $item):
                                                echo "'" . obtenerColorCarrera($item['carrera']) . "',";
                                            endforeach;
                                            ?>
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function (context) {
                                                    const label = context.label || '';
                                                    const value = context.raw || 0;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = Math.round((value / total) * 100);
                                                    return `${label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        <?php endif; ?>
                    }

                    // Función para exportar gráficos a PDF
                    function exportarGraficosPDF() {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF();

                        // Título del documento
                        doc.setFontSize(18);
                        doc.text('Reporte Estadístico - <?= $titulo ?>', 105, 15, { align: 'center' });

                        // Información del filtro si está activo
                        let filtroInfo = '';
                        <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                            filtroInfo = 'Filtro aplicado: ';
                            <?php
                            if ($tipo_filtro === 'dia') {
                                echo "filtroInfo += 'Día: ' + '" . date('d/m/Y', strtotime($fecha_filtro)) . "';";
                            } elseif ($tipo_filtro === 'mes') {
                                echo "filtroInfo += 'Mes: ' + '" . date('m/Y', strtotime($fecha_filtro . '-01')) . "';";
                            } elseif ($tipo_filtro === 'anio') {
                                echo "filtroInfo += 'Año: ' + '$fecha_filtro';";
                            }
                            ?>
                            filtroInfo += ' - Total de registros: <?= $resultado->num_rows ?>';
                        <?php endif; ?>

                        if (filtroInfo) {
                            doc.setFontSize(10);
                            doc.text(filtroInfo, 105, 25, { align: 'center' });
                        }

                        // Fecha de generación
                        doc.setFontSize(10);
                        doc.text('Generado el: <?= date("d/m/Y H:i") ?>', 105, 35, { align: 'center' });

                        let yPosition = 45;

                        // Gráfico de Turno
                        <?php if (!empty($datosGraficoTurno)): ?>
                            doc.setFontSize(14);
                            doc.text('Distribución por Turno', 105, yPosition, { align: 'center' });
                            yPosition += 10;

                            // Aquí podrías agregar el gráfico como imagen si quisieras
                            // Por ahora, agregamos una tabla con los datos
                            doc.autoTable({
                                startY: yPosition,
                                head: [['Turno', 'Cantidad', 'Porcentaje']],
                                body: [
                                    <?php
                                    foreach ($datosGraficoTurno as $item):
                                        $porcentaje = round(($item['total'] / $totalTurno) * 100, 1);
                                        echo "['" . $item['turno'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                                    endforeach;
                                    ?>
                                ],
                                theme: 'grid',
                                headStyles: { fillColor: [17, 40, 114] }
                            });

                            yPosition = doc.lastAutoTable.finalY + 15;
                        <?php endif; ?>

                        // Gráfico de Semestre
                        <?php if (!empty($datosGraficoSemestre)): ?>
                            doc.setFontSize(14);
                            doc.text('Distribución por Semestre', 105, yPosition, { align: 'center' });
                            yPosition += 10;

                            doc.autoTable({
                                startY: yPosition,
                                head: [['Semestre', 'Cantidad', 'Porcentaje']],
                                body: [
                                    <?php
                                    foreach ($datosGraficoSemestre as $item):
                                        $porcentaje = round(($item['total'] / $totalSemestre) * 100, 1);
                                        echo "['" . $item['semestre'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                                    endforeach;
                                    ?>
                                ],
                                theme: 'grid',
                                headStyles: { fillColor: [17, 40, 114] }
                            });

                            yPosition = doc.lastAutoTable.finalY + 15;
                        <?php endif; ?>

                        // Gráfico de Carrera
                        <?php if (!empty($datosGraficoCarrera)): ?>
                            doc.setFontSize(14);
                            doc.text('Distribución por Carrera', 105, yPosition, { align: 'center' });
                            yPosition += 10;

                            doc.autoTable({
                                startY: yPosition,
                                head: [['Carrera', 'Cantidad', 'Porcentaje']],
                                body: [
                                    <?php
                                    foreach ($datosGraficoCarrera as $item):
                                        $porcentaje = round(($item['total'] / $totalCarrera) * 100, 1);
                                        echo "['" . $item['carrera'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                                    endforeach;
                                    ?>
                                ],
                                theme: 'grid',
                                headStyles: { fillColor: [17, 40, 114] }
                            });
                        <?php endif; ?>

                        // Guardar el PDF
                        doc.save('reporte_estadistico_<?= $area ?>_<?= date("Y-m-d") ?>.pdf');
                    }

                    // Funciones de exportación existentes
                    function exportarExcel() {
                        // Tu código existente para exportar a Excel
                    }

                    function exportarPDF() {
                        // Tu código existente para exportar a PDF
                    }
                </script>
            </div>
        <?php elseif ($area && (!$resultado || $resultado->num_rows === 0)): ?>
            <div class="form-container">
                <p>No se encontraron datos para el área
                    seleccionada<?= $filtrar_por_fecha ? ' con el filtro aplicado' : '' ?>.</p>
                <?php if ($area === 'Psicologia' || $area === 'Topico'): ?>
                    <p style="color: #666; font-style: italic;">
                        <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                            Sugerencia: Intente con un rango de fechas diferente o verifique que existan registros para la fecha
                            seleccionada.
                        <?php else: ?>
                            Sugerencia: Verifique que existan registros activos en la base de datos para esta área.
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    <script src="../../public/js/script.js"></script>
</body>

</html>
<script>
    // Función para exportar a Excel
    function exportarExcel() {
        try {
            // Crear un nuevo libro de trabajo
            const wb = XLSX.utils.book_new();

            // Preparar los datos para exportar
            const datosExportar = [];

            // Agregar encabezados
            datosExportar.push(<?= json_encode($columnas) ?>);

            // Agregar filas de datos desde PHP
            <?php foreach ($datosExportar as $fila): ?>
                datosExportar.push(<?= json_encode(array_values($fila)) ?>);
            <?php endforeach; ?>

            // Crear hoja de trabajo
            const ws = XLSX.utils.aoa_to_sheet(datosExportar);

            // Agregar la hoja al libro
            XLSX.utils.book_append_sheet(wb, ws, "Reporte");

            // Generar nombre del archivo con información del filtro
            let nombreArchivo = 'reporte_<?= $area ?>_<?= date("Y-m-d") ?>';

            <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                <?php
                if ($tipo_filtro === 'dia') {
                    echo "nombreArchivo += '_' + '" . date('Y-m-d', strtotime($fecha_filtro)) . "';";
                } elseif ($tipo_filtro === 'mes') {
                    echo "nombreArchivo += '_' + '" . date('Y-m', strtotime($fecha_filtro . '-01')) . "';";
                } elseif ($tipo_filtro === 'anio') {
                    echo "nombreArchivo += '_' + '$fecha_filtro';";
                }
                ?>
            <?php endif; ?>

            nombreArchivo += '.xlsx';

            // Descargar el archivo
            XLSX.writeFile(wb, nombreArchivo);

        } catch (error) {
            console.error('Error al exportar a Excel:', error);
            alert('Error al exportar a Excel. Por favor, intente nuevamente.');
        }
    }

    // Función para exportar a PDF
    function exportarPDF() {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Configuración del documento
            doc.setFontSize(16);
            doc.setTextColor(40, 40, 40);

            // Título principal
            doc.text('<?= $titulo ?>', 105, 15, { align: 'center' });

            // Información del filtro si está activo
            let yPosition = 25;

            <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                doc.setFontSize(10);
                let textoFiltro = 'Filtro aplicado: ';
                <?php
                if ($tipo_filtro === 'dia') {
                    echo "textoFiltro += 'Día: " . date('d/m/Y', strtotime($fecha_filtro)) . "';";
                } elseif ($tipo_filtro === 'mes') {
                    echo "textoFiltro += 'Mes: " . date('m/Y', strtotime($fecha_filtro . '-01')) . "';";
                } elseif ($tipo_filtro === 'anio') {
                    echo "textoFiltro += 'Año: $fecha_filtro';";
                }
                ?>
                textoFiltro += ' - Total de registros: <?= $resultado->num_rows ?>';
                doc.text(textoFiltro, 105, yPosition, { align: 'center' });
                yPosition += 8;
            <?php endif; ?>

            // Fecha de generación
            doc.text('Generado el: <?= date("d/m/Y H:i") ?>', 105, yPosition, { align: 'center' });
            yPosition += 15;

            // Preparar datos para la tabla
            const headers = <?= json_encode($columnas) ?>;
            const data = [
                <?php foreach ($datosExportar as $fila): ?>
                    <?= json_encode(array_values($fila)) ?>,
                <?php endforeach; ?>
            ];

            // Configurar la tabla
            doc.autoTable({
                startY: yPosition,
                head: [headers],
                body: data,
                theme: 'grid',
                headStyles: {
                    fillColor: [17, 40, 114],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                styles: {
                    fontSize: 8,
                    cellPadding: 3
                },
                margin: { top: yPosition },
                didDrawPage: function (data) {
                    // Número de página
                    doc.setFontSize(10);
                    doc.setTextColor(100);
                    doc.text(
                        'Página ' + doc.internal.getNumberOfPages(),
                        doc.internal.pageSize.width / 2,
                        doc.internal.pageSize.height - 10,
                        { align: 'center' }
                    );
                }
            });

            // Generar nombre del archivo
            let nombreArchivo = 'reporte_<?= $area ?>_<?= date("Y-m-d") ?>';

            <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                <?php
                if ($tipo_filtro === 'dia') {
                    echo "nombreArchivo += '_' + '" . date('Y-m-d', strtotime($fecha_filtro)) . "';";
                } elseif ($tipo_filtro === 'mes') {
                    echo "nombreArchivo += '_' + '" . date('Y-m', strtotime($fecha_filtro . '-01')) . "';";
                } elseif ($tipo_filtro === 'anio') {
                    echo "nombreArchivo += '_' + '$fecha_filtro';";
                }
                ?>
            <?php endif; ?>

            nombreArchivo += '.pdf';

            // Descargar el PDF
            doc.save(nombreArchivo);

        } catch (error) {
            console.error('Error al exportar a PDF:', error);
            alert('Error al exportar a PDF. Por favor, intente nuevamente.');
        }
    }

    // Función para exportar gráficos a PDF (mejorada)
    function exportarGraficosPDF() {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Título del documento
            doc.setFontSize(18);
            doc.text('Reporte Estadístico - <?= $titulo ?>', 105, 15, { align: 'center' });

            // Información del filtro si está activo
            let filtroInfo = '';
            <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                filtroInfo = 'Filtro aplicado: ';
                <?php
                if ($tipo_filtro === 'dia') {
                    echo "filtroInfo += 'Día: " . date('d/m/Y', strtotime($fecha_filtro)) . "';";
                } elseif ($tipo_filtro === 'mes') {
                    echo "filtroInfo += 'Mes: " . date('m/Y', strtotime($fecha_filtro . '-01')) . "';";
                } elseif ($tipo_filtro === 'anio') {
                    echo "filtroInfo += 'Año: $fecha_filtro';";
                }
                ?>
                filtroInfo += ' - Total de registros: <?= $resultado->num_rows ?>';
            <?php endif; ?>

            if (filtroInfo) {
                doc.setFontSize(10);
                doc.text(filtroInfo, 105, 25, { align: 'center' });
            }

            // Fecha de generación
            doc.setFontSize(10);
            doc.text('Generado el: <?= date("d/m/Y H:i") ?>', 105, 35, { align: 'center' });

            let yPosition = 45;

            // Gráfico de Turno
            <?php if (!empty($datosGraficoTurno)): ?>
                doc.setFontSize(14);
                doc.text('Distribución por Turno', 105, yPosition, { align: 'center' });
                yPosition += 10;

                doc.autoTable({
                    startY: yPosition,
                    head: [['Turno', 'Cantidad', 'Porcentaje']],
                    body: [
                        <?php
                        foreach ($datosGraficoTurno as $item):
                            $porcentaje = round(($item['total'] / $totalTurno) * 100, 1);
                            echo "['" . $item['turno'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                        endforeach;
                        ?>
                    ],
                    theme: 'grid',
                    headStyles: { fillColor: [17, 40, 114] },
                    styles: { fontSize: 10 }
                });

                yPosition = doc.lastAutoTable.finalY + 15;
            <?php endif; ?>

            // Gráfico de Semestre
            <?php if (!empty($datosGraficoSemestre)): ?>
                // Verificar si necesitamos nueva página
                if (yPosition > 250) {
                    doc.addPage();
                    yPosition = 20;
                }

                doc.setFontSize(14);
                doc.text('Distribución por Semestre', 105, yPosition, { align: 'center' });
                yPosition += 10;

                doc.autoTable({
                    startY: yPosition,
                    head: [['Semestre', 'Cantidad', 'Porcentaje']],
                    body: [
                        <?php
                        foreach ($datosGraficoSemestre as $item):
                            $porcentaje = round(($item['total'] / $totalSemestre) * 100, 1);
                            echo "['" . $item['semestre'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                        endforeach;
                        ?>
                    ],
                    theme: 'grid',
                    headStyles: { fillColor: [17, 40, 114] },
                    styles: { fontSize: 10 }
                });

                yPosition = doc.lastAutoTable.finalY + 15;
            <?php endif; ?>

            // Gráfico de Carrera
            <?php if (!empty($datosGraficoCarrera)): ?>
                // Verificar si necesitamos nueva página
                if (yPosition > 250) {
                    doc.addPage();
                    yPosition = 20;
                }

                doc.setFontSize(14);
                doc.text('Distribución por Carrera', 105, yPosition, { align: 'center' });
                yPosition += 10;

                doc.autoTable({
                    startY: yPosition,
                    head: [['Carrera', 'Cantidad', 'Porcentaje']],
                    body: [
                        <?php
                        foreach ($datosGraficoCarrera as $item):
                            $porcentaje = round(($item['total'] / $totalCarrera) * 100, 1);
                            echo "['" . $item['carrera'] . "', " . $item['total'] . ", '" . $porcentaje . "%'],";
                        endforeach;
                        ?>
                    ],
                    theme: 'grid',
                    headStyles: { fillColor: [17, 40, 114] },
                    styles: { fontSize: 9 } // Tamaño más pequeño para carreras largas
                });
            <?php endif; ?>

            // Generar nombre del archivo
            let nombreArchivo = 'reporte_estadistico_<?= $area ?>_<?= date("Y-m-d") ?>';

            <?php if ($filtrar_por_fecha && $fecha_filtro): ?>
                <?php
                if ($tipo_filtro === 'dia') {
                    echo "nombreArchivo += '_' + '" . date('Y-m-d', strtotime($fecha_filtro)) . "';";
                } elseif ($tipo_filtro === 'mes') {
                    echo "nombreArchivo += '_' + '" . date('Y-m', strtotime($fecha_filtro . '-01')) . "';";
                } elseif ($tipo_filtro === 'anio') {
                    echo "nombreArchivo += '_' + '$fecha_filtro';";
                }
                ?>
            <?php endif; ?>

            nombreArchivo += '.pdf';

            // Descargar el PDF
            doc.save(nombreArchivo);

        } catch (error) {
            console.error('Error al exportar gráficos a PDF:', error);
            alert('Error al exportar gráficos a PDF. Por favor, intente nuevamente.');
        }
    }
</script>

<?php
// Funciones auxiliares para obtener colores consistentes
function obtenerColorTurno($turno)
{
    $colores = [
        'Diurno' => '#3498db',
        'Vespertino' => '#e74c3c'
    ];
    return $colores[$turno] ?? '#' . substr(md5($turno), 0, 6);
}

function obtenerColorSemestre($semestre)
{
    $colores = [
        '1er Semestre' => '#1abc9c',
        '2do Semestre' => '#2ecc71',
        '3er Semestre' => '#3498db',
        '4to Semestre' => '#9b59b6',
        '5to Semestre' => '#f1c40f',
        '6to Semestre' => '#e67e22'
    ];
    return $colores[$semestre] ?? '#' . substr(md5($semestre), 0, 6);
}

function obtenerColorCarrera($carrera)
{
    $colores = [
        'Diseño y Programación Web' => '#e74c3c',
        'Asistencia Administrativa' => '#3498db',
        'Electricidad Industrial' => '#2ecc71',
        'Mecánica de Producción Industrial' => '#f39c12',
        'Mecatrónica Automotriz' => '#9b59b6',
        'Mantenimiento de Maquinaria Pesada' => '#1abc9c',
        'Metalurgia' => '#d35400',
        'Electrónica Industrial' => '#34495e',
        'Tecnología de Análisis Químico' => '#7f8c8d'
    ];
    return $colores[$carrera] ?? '#' . substr(md5($carrera), 0, 6);
}
?>