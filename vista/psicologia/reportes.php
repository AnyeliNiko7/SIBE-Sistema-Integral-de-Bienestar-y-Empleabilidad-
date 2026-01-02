<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
require_once '../../config/conexion.php';

// Verificar la conexión a la base de datos
if ($enlace->connect_error) {
    die("Error de conexión: " . $enlace->connect_error);
}

// 1. Obtener datos para la distribución por Turno
$queryTurno = "
    SELECT turnos.nombre AS turno, COUNT(*) AS cantidad
    FROM registros_psicologia
    INNER JOIN turnos ON registros_psicologia.id_turno = turnos.id_turno
    WHERE registros_psicologia.estado = 'activo'
    GROUP BY turnos.nombre 
    ORDER BY turnos.nombre
";
$resultTurno = $enlace->query($queryTurno);
$datosTurno = $resultTurno ? $resultTurno->fetch_all(MYSQLI_ASSOC) : [];

// 2. Obtener datos para la distribución por Semestre
$querySemestre = "
    SELECT semestres.nombre AS semestre, COUNT(*) AS cantidad
    FROM registros_psicologia
    INNER JOIN semestres ON registros_psicologia.id_semestre = semestres.id_semestre
    WHERE registros_psicologia.estado = 'activo'
    GROUP BY semestres.nombre
    ORDER BY semestres.nombre
";
$resultSemestre = $enlace->query($querySemestre);
$datosSemestre = $resultSemestre ? $resultSemestre->fetch_all(MYSQLI_ASSOC) : [];

// 3. Obtener datos para la distribución por Carreras
$queryCarrera = "
    SELECT carrera.nombre AS carrera, COUNT(*) AS cantidad
    FROM registros_psicologia
    INNER JOIN carrera ON registros_psicologia.id_carrera = carrera.id_carrera
    WHERE registros_psicologia.estado = 'activo'
    GROUP BY carrera.nombre
    ORDER BY carrera.nombre
";
$resultCarrera = $enlace->query($queryCarrera);
$datosCarrera = $resultCarrera ? $resultCarrera->fetch_all(MYSQLI_ASSOC) : [];

// 4. Obtener datos para las Actividades Más Realizadas
$queryActividades = "
    SELECT descripcion, COUNT(*) AS cantidad
    FROM actividades_realizadas
    INNER JOIN registros_psicologia ON actividades_realizadas.paciente_id = registros_psicologia.id
    WHERE registros_psicologia.estado = 'activo'
    GROUP BY descripcion
    ORDER BY cantidad DESC
    LIMIT 10";
$resultActividades = $enlace->query($queryActividades);
$datosActividades = $resultActividades ? $resultActividades->fetch_all(MYSQLI_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Psicología</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/psicologia.css?v=<?php echo filemtime('../../public/css/psicologia.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Biblioteca para generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
    <style>
        .charts-container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 70px;
            padding: 20px;
            align-items: center;
        }

        .chart-item {
            flex: 0 1 40%;
            max-width: 35%;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            position: relative;
        }

        .chart-item.full-width {
            flex: 1 1 100%;
            max-width: 75%;
        }

        .no-data-message {
            color: #888;
            font-size: 1.2rem;
            margin: 20px;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
        }

        @media (max-width: 768px) {
            .chart-item {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }

        /* Estilos para el botón de descarga */
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
            transition: background-color 0.3s;
        }

        .download-btn:hover {
            background-color: #112872;
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
    <?php include '../../includes/asidePsicologia.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <section>
            <h2>Reportes Estadísticos</h2>
            
            <!-- Botón para descargar como PDF -->
            <div class="download-btn-container">
                <button id="downloadPdf" class="download-btn">
                    <i class='bx bxs-download'></i> Descargar como PDF
                </button>
            </div>
            
            <div class="charts-container" id="reportContent">
                <div class="chart-item">
                    <h2>Distribución por Turno</h2>
                    <canvas id="graficoTurno"></canvas>
                    <div id="no-data-turno" class="no-data-message" style="display: none;">No hay datos disponibles.</div>
                </div>
                <div class="chart-item">
                    <h2>Distribución por Semestre</h2>
                    <canvas id="graficoSemestre"></canvas>
                    <div id="no-data-semestre" class="no-data-message" style="display: none;">No hay datos disponibles.</div>
                </div>
                <div class="chart-item full-width">
                    <h2>Distribución por Carreras</h2>
                    <canvas id="graficoCarrera"></canvas>
                    <div id="no-data-carrera" class="no-data-message" style="display: none;">No hay datos disponibles.</div>
                </div>
            </div>
        </section>
    </main>

    <!-- Plantilla oculta para el PDF -->
    <div id="pdfTemplate" style="display: none;">
        <div id="pdfPages"></div>
    </div>

    <script>
        const datosTurno = <?php echo json_encode($datosTurno); ?>;
        const datosSemestre = <?php echo json_encode($datosSemestre); ?>;
        const datosCarrera = <?php echo json_encode($datosCarrera); ?>;
        const datosActividades = <?php echo json_encode($datosActividades); ?>;
        
        console.log("Datos Semestre:", datosSemestre);

        // Variables para almacenar las instancias de gráficos
        let chartTurno, chartSemestre, chartCarrera;

        // Función para mostrar el mensaje de "no hay datos"
        const showNoDataMessage = (id) => {
            document.getElementById(id).style.display = 'block';
        };

        // Función para calcular porcentajes
        const calcularPorcentajes = (datos) => {
            const total = datos.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
            return datos.map(item => {
                const porcentaje = total > 0 ? ((parseInt(item.cantidad) / total) * 100).toFixed(0) : 0;
                return {
                    ...item,
                    porcentaje: porcentaje
                };
            });
        };

        // Función para generar análisis de datos
        const generarAnalisis = (tipo, datosConPorcentaje) => {
            const total = datosConPorcentaje.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
            let analisis = `<h3>Lectura del Gráfico - Distribución por ${tipo}</h3>`;
            
            if (total === 0) {
                analisis += `<p>No hay datos disponibles para el análisis.</p>`;
                return analisis;
            }
            
            analisis += `<p>Total de estudiantes: <span class="total-count">${total}</span></p>`;
            analisis += `<ul>`;
            
            datosConPorcentaje.forEach(item => {
                analisis += `<li>${item.porcentaje}% corresponde al ${tipo.toLowerCase()} ${item[tipo.toLowerCase()]} (${item.cantidad} estudiantes)</li>`;
            });
            
            // Encontrar el valor máximo y mínimo
            if (datosConPorcentaje.length > 1) {
                const maxItem = datosConPorcentaje.reduce((max, item) => 
                    parseInt(item.porcentaje) > parseInt(max.porcentaje) ? item : max, datosConPorcentaje[0]);
                
                const minItem = datosConPorcentaje.reduce((min, item) => 
                    parseInt(item.porcentaje) < parseInt(min.porcentaje) ? item : min, datosConPorcentaje[0]);
                
                analisis += `</ul>`;
                analisis += `<p><strong>Análisis:</strong> El ${tipo.toLowerCase()} con mayor asistencia es ${maxItem[tipo.toLowerCase()]} con ${maxItem.porcentaje}% (${maxItem.cantidad} estudiantes), 
                mientras que el ${tipo.toLowerCase()} con menor asistencia es ${minItem[tipo.toLowerCase()]} con ${minItem.porcentaje}% (${minItem.cantidad} estudiantes).</p>`;
            } else {
                analisis += `</ul>`;
                analisis += `<p><strong>Análisis:</strong> Todos los estudiantes pertenecen al ${tipo.toLowerCase()} ${datosConPorcentaje[0][tipo.toLowerCase()]}.</p>`;
            }
            
            return analisis;
        };

        const generarGrafico = (ctx, tipo, etiquetas, datos, colores, indexAxis = 'x') => {
            return new Chart(ctx, {
                type: tipo,
                data: {
                    labels: etiquetas,
                    datasets: [{
                        data: datos,
                        backgroundColor: colores,
                        borderWidth: 1,
                    }],
                },
                options: {
                    indexAxis: indexAxis,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            display: tipo === 'pie'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                                    const porcentaje = total > 0 ? ((value / total) * 100).toFixed(0) + '%' : '0%';
                                    return `${label}: ${value} (${porcentaje})`;
                                }
                            }
                        },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                                const porcentaje = total > 0 ? (value / total) * 100 : 0;
                                
                                if (porcentaje === 0) return '';
                                if (porcentaje < 1) return '<1%';
                                return porcentaje.toFixed(0) + '%';
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            anchor: 'center',
                            align: 'center'
                        },
                    },
                },
                plugins: [ChartDataLabels],
            });
        };

        // Gráfico Turno
        if (datosTurno.length > 0) {
            chartTurno = generarGrafico(
                document.getElementById('graficoTurno').getContext('2d'),
                'pie',
                datosTurno.map(d => d.turno),
                datosTurno.map(d => parseInt(d.cantidad)),
                ['#132bb3ff', '#c00000ff', '#369200ff']
            );
        } else {
            document.getElementById('graficoTurno').style.display = 'none';
            showNoDataMessage('no-data-turno');
        }

        // Gráfico Semestre - CORREGIDO
        if (datosSemestre.length > 0) {
            // Ordenar los semestres numéricamente para que coincidan con los porcentajes
            const semestresOrdenados = ['1er Semestre', '2do Semestre', '3er Semestre', '4to Semestre', '5to Semestre', '6to Semestre'];
            const datosOrdenados = semestresOrdenados.map(semestre => {
                const dato = datosSemestre.find(d => d.semestre === semestre);
                return dato ? parseInt(dato.cantidad) : 0;
            });

            chartSemestre = generarGrafico(
                document.getElementById('graficoSemestre').getContext('2d'),
                'pie',
                semestresOrdenados.filter((_, index) => datosOrdenados[index] > 0), // Mostrar solo semestres con datos
                datosOrdenados.filter(cantidad => cantidad > 0),
                ['#132bb3ff', '#c00000ff', '#369200ff', '#cf5300ff', '#b51bc9ff', '#5a00cfff']
            );
        } else {
            document.getElementById('graficoSemestre').style.display = 'none';
            showNoDataMessage('no-data-semestre');
        }

        // Gráfico Carrera
        if (datosCarrera.length > 0) {
            chartCarrera = generarGrafico(
                document.getElementById('graficoCarrera').getContext('2d'),
                'bar',
                datosCarrera.map(d => d.carrera),
                datosCarrera.map(d => parseInt(d.cantidad)),
                ['#132bb3ff', '#c00000ff', '#369200ff', '#cf5300ff', '#b51bc9ff', '#5a00cfff', '#005e94ff', '#00944aff']
            );
        } else {
            document.getElementById('graficoCarrera').style.display = 'none';
            showNoDataMessage('no-data-carrera');
        }

        // Función para descargar como PDF
        document.getElementById('downloadPdf').addEventListener('click', async function() {
            // Mostrar mensaje de carga
            const downloadBtn = document.getElementById('downloadPdf');
            const originalText = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Generando PDF...';
            downloadBtn.disabled = true;
            
            try {
                // Obtener las imágenes de los gráficos
                const turnoImg = chartTurno ? chartTurno.toBase64Image() : null;
                const semestreImg = chartSemestre ? chartSemestre.toBase64Image() : null;
                const carreraImg = chartCarrera ? chartCarrera.toBase64Image() : null;
                
                // Calcular porcentajes para el análisis
                const turnoConPorcentajes = calcularPorcentajes(datosTurno);
                const semestreConPorcentajes = calcularPorcentajes(datosSemestre);
                const carreraConPorcentajes = calcularPorcentajes(datosCarrera);
                
                // Generar análisis
                const analisisTurno = generarAnalisis('Turno', turnoConPorcentajes);
                const analisisSemestre = generarAnalisis('Semestre', semestreConPorcentajes);
                const analisisCarrera = generarAnalisis('Carrera', carreraConPorcentajes);
                
                // Crear contenido para el PDF
                const pdfPages = document.getElementById('pdfPages');
                pdfPages.innerHTML = '';
                
                // Página 1: Gráfico de Turno
                if (turnoImg) {
                    const page1 = document.createElement('div');
                    page1.className = 'pdf-page';
                    page1.innerHTML = `
                        <div class="pdf-header">
                            <h2>Reporte de Psicología - Distribución por Turno</h2>
                            <p>Fecha: ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="pdf-chart-container">
                            <img src="${turnoImg}" style="max-width: 100%; max-height: 100%;" alt="Gráfico de Turno">
                        </div>
                        <div class="pdf-analysis">
                            ${analisisTurno}
                        </div>
                        <div class="pdf-footer">
                            <p>Reporte generado por el Sistema de Psicología</p>
                        </div>
                    `;
                    pdfPages.appendChild(page1);
                }
                
                // Página 2: Gráfico de Semestre
                if (semestreImg) {
                    const page2 = document.createElement('div');
                    page2.className = 'pdf-page';
                    page2.innerHTML = `
                        <div class="pdf-header">
                            <h2>Reporte de Psicología - Distribución por Semestre</h2>
                            <p>Fecha: ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="pdf-chart-container">
                            <img src="${semestreImg}" style="max-width: 100%; max-height: 100%;" alt="Gráfico de Semestre">
                        </div>
                        <div class="pdf-analysis">
                            ${analisisSemestre}
                        </div>
                        <div class="pdf-footer">
                            <p>Reporte generado por el Sistema de Psicología</p>
                        </div>
                    `;
                    pdfPages.appendChild(page2);
                }
                
                // Página 3: Gráfico de Carrera
                if (carreraImg) {
                    const page3 = document.createElement('div');
                    page3.className = 'pdf-page';
                    page3.innerHTML = `
                        <div class="pdf-header">
                            <h2>Reporte de Psicología - Distribución por Carreras</h2>
                            <p>Fecha: ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="pdf-chart-container">
                            <img src="${carreraImg}" style="max-width: 100%; max-height: 100%;" alt="Gráfico de Carrera">
                        </div>
                        <div class="pdf-analysis">
                            ${analisisCarrera}
                        </div>
                        <div class="pdf-footer">
                            <p>Reporte generado por el area de psicología</p>
                        </div>
                    `;
                    pdfPages.appendChild(page3);
                }
                
                // Opciones de configuración para el PDF
                const opt = {
                    margin: 10,
                    filename: 'Reporte_Psicologia_' + new Date().toISOString().slice(0, 10) + '.pdf',
                    image: { type: 'jpeg', quality: 1.0 }, // Máxima calidad
                    html2canvas: { 
                        scale: 2, // Doble resolución para mejor calidad
                        useCORS: true,
                        logging: false
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a4', 
                        orientation: 'portrait' 
                    }
                };

                // Generar y descargar el PDF
                await html2pdf().set(opt).from(pdfPages).save();
                
            } catch (error) {
                console.error('Error al generar el PDF:', error);
                alert('Error al generar el PDF. Por favor, intente nuevamente.');
            } finally {
                // Restaurar el botón
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            }
        });
    </script>
</body>

</html>