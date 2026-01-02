<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Psicología') {
    header("location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Interactivo - Sistema Psicología</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/psicologia.css?v=<?php echo filemtime('../../public/css/psicologia.css'); ?>">
    <link rel="stylesheet" href="../../public/css/manual.css?v=<?php echo filemtime('../../public/css/manual.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>
<body>
    <?php include '../../includes/asidePsicologia.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <div class="contenedor-manual">
            <!-- Encabezado -->
            <div class="encabezado-manual">
                <div class="encabezado-superior">
                    <div class="logo-manual">
                        <h1>Manual del Sistema Psicología</h1>
                    </div>
                    
                    <div class="caja-busqueda">
                        <input type="text" class="entrada-busqueda" placeholder="Buscar en el manual..." id="entradaBusqueda">
                        <i class='bx bx-search icono-busqueda'></i>
                    </div>
                </div>
            </div>

            <!-- Menú de navegación -->
            <div class="menu-navegacion">
                <div class="pestanas-menu">
                    <a href="#" class="item-menu activo" data-seccion="inicio">
                        <i class='bx bx-home'></i> Inicio
                    </a>
                    <a href="#" class="item-menu" data-seccion="historial">
                        <i class='bx bx-clipboard'></i> Historial
                    </a>
                    <a href="#" class="item-menu" data-seccion="sesiones">
                        <i class='bx bx-conversation'></i> Sesiones
                    </a>
                    <a href="#" class="item-menu" data-seccion="actividades">
                        <i class='bx bx-group'></i> Actividades
                    </a>
                    <a href="#" class="item-menu" data-seccion="reportes">
                        <i class='bx bx-bar-chart-alt-2'></i> Reportes
                    </a>
                    <a href="#" class="item-menu" data-seccion="informacion">
                        <i class='bx bx-info-circle'></i> Información
                    </a>
                    <a href="#" class="item-menu" data-seccion="papelera">
                        <i class='bx bx-trash'></i> Papelera
                    </a>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="contenido-principal">
                <!-- Sección Inicio -->
                <div class="seccion-contenido activa" id="inicio">
                    <div class="acciones-rapidas">
                        <div class="tarjeta-accion" onclick="mostrarSeccion('historial')">
                            <div class="icono-accion"><i class='bx bx-clipboard'></i></div>
                            <h3>Historial de Sesiones</h3>
                            <p>Visualiza y gestiona el historial de atenciones psicológicas</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('sesiones')">
                            <div class="icono-accion"><i class='bx bx-conversation'></i></div>
                            <h3>Registrar Sesiones</h3>
                            <p>Agrega nuevas sesiones y evaluaciones psicológicas</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('actividades')">
                            <div class="icono-accion"><i class='bx bx-group'></i></div>
                            <h3>Actividades Grupales</h3>
                            <p>Gestiona charlas, talleres y actividades preventivas</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('reportes')">
                            <div class="icono-accion"><i class='bx bx-bar-chart-alt-2'></i></div>
                            <h3>Reportes Estadísticos</h3>
                            <p>Consulta estadísticas automáticas de salud mental</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('informacion')">
                            <div class="icono-accion"><i class='bx bx-info-circle'></i></div>
                            <h3>Información</h3>
                            <p>Consulta y exporta registros por períodos específicos</p>
                        </div>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Funcionalidades Principales</h3>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li><strong>Sesiones individuales</strong> - Historial completo de atenciones psicológicas</li>
                            <li><strong>Actividades grupales</strong> - Gestión de charlas y talleres preventivos</li>
                            <li><strong>Reportes automáticos</strong> - Estadísticas en tiempo real</li>
                            <li><strong>Exportación</strong> - Datos en Excel y PDF para informes</li>
                            <li><strong>Confidencialidad</strong> - Gestión segura de información sensible</li>
                        </ul>
                    </div>
                </div>

                <!-- Sección Historial -->
                <div class="seccion-contenido" id="historial">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-clipboard'></i> Historial de Sesiones Psicológicas</h2>
                        <p>Página principal para visualizar y gestionar el historial de atenciones</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página Principal</h3>
                        <p class="descripcion-paso">Esta es la página principal donde puedes realizar diferentes acciones. En la tabla de registros se muestra el historial de todas las sesiones psicológicas realizadas.</p>
                        <img src="../../public/img/manual/psi1.png" alt="Vista principal del historial de sesiones" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Botón "Detalles"</h3>
                        <p class="descripcion-paso">Al hacer clic en "Detalles" en la tabla de registros, puedes visualizar detalladamente la información de la sesión psicológica. Para cerrar la vista detallada, haz clic en el botón "Cerrar".</p>
                        <img src="../../public/img/manual/psi2.png" alt="Vista detallada de una sesión psicológica" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">3</span>
                        <h3 class="titulo-paso">Función "Editar"</h3>
                        <p class="descripcion-paso">Al hacer clic en "Editar" puedes actualizar información de la sesión. Después de realizar los cambios, haz clic en "Actualizar". Si deseas cancelar, puedes hacer clic afuera del área de "Editar Registro".</p>
                        <img src="../../public/img/manual/psi3.png" alt="Formulario de edición de sesión" class="imagen-paso">
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-info-circle'></i> Funciones Disponibles</h3>
                        <p><strong>Detalles:</strong> Ver información completa de la sesión</p>
                        <p><strong>Editar:</strong> Modificar datos existentes de la atención</p>
                        <p><strong>Eliminar:</strong> Enviar registro a papelera (recuperable)</p>
                    </div>
                </div>

                <!-- Sección Sesiones -->
                <div class="seccion-contenido" id="sesiones">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-conversation'></i> Registrar Sesiones Psicológicas</h2>
                        <p>Agrega nuevas atenciones y evaluaciones psicológicas al historial</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página de Sesiones</h3>
                        <p class="descripcion-paso">En esta página puedes registrar las sesiones psicológicas que se han realizado para incluirlas en el historial del estudiante.</p>
                        <img src="../../public/img/manual/psi5.png" alt="Formulario para crear nuevas sesiones psicológicas" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Formulario Completo</h3>
                        <p class="descripcion-paso">El formulario incluye todos los campos necesarios para una evaluación psicológica completa.</p>
                        <img src="../../public/img/manual/psi6.png" alt="Vista completa del formulario de sesiones" class="imagen-paso">
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Datos Importantes</h3>
                        <p>Incluye siempre: Motivo de consulta, observaciones relevantes y recomendaciones para el seguimiento</p>
                    </div>
                </div>

                <!-- Sección Actividades -->
                <div class="seccion-contenido" id="actividades">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-group'></i> Gestión de Actividades Grupales</h2>
                        <p>Administra charlas, talleres y actividades preventivas</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página de Actividades</h3>
                        <p class="descripcion-paso">En esta página puedes agregar las actividades como charlas, orientaciones y talleres que se realizan en el área de psicología.</p>
                        <img src="../../public/img/manual/psi8.png" alt="Interfaz de gestión de actividades" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Editar Actividades</h3>
                        <p class="descripcion-paso">Puedes editar la información de las actividades existentes para mantener actualizado el registro de intervenciones grupales.</p>
                        <img src="../../public/img/manual/psi9.png" alt="Formulario de edición de actividades" class="imagen-paso">
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-info-circle'></i> Tipos de Actividades</h3>
                        <p><strong>Charlas preventivas:</strong> Salud mental, prevención de adicciones, etc.</p>
                        <p><strong>Talleres grupales:</strong> Habilidades sociales, manejo de emociones</p>
                        <p><strong>Orientación vocacional:</strong> Asesoramiento para elección de carrera</p>
                    </div>
                </div>

                <!-- Sección Reportes -->
                <div class="seccion-contenido" id="reportes">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-bar-chart-alt-2'></i> Reportes en Tiempo Real</h2>
                        <p>Visualiza estadísticas automáticas del sistema de psicología</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Vista de Reportes</h3>
                        <p class="descripcion-paso">En esta página puedes visualizar los reportes que se han generado automáticamente en tiempo real, mostrando estadísticas actualizadas del área psicológica.</p>
                        <img src="../../public/img/manual/psi7.png" alt="Dashboard de reportes con gráficos estadísticos" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Tipos de Reportes</h3>
                        <p class="descripcion-paso">Los reportes cuentan con su lector de gráficos con la información sobre:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-pie-chart-alt'></i> <strong>Distribución de carrera</strong></li>
                            <li><i class='bx bx-calendar'></i> <strong>Distribución de turno</strong></li>
                            <li><i class='bx bx-book-open'></i> <strong>Distribución de semestre</strong></li>
                        </ul>
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-refresh'></i> Actualización Automática</h3>
                        <p>Los reportes se actualizan automáticamente cada vez que se registra una nueva sesión o actividad, proporcionando datos siempre actuales para la toma de decisiones.</p>
                    </div>
                </div>

                <!-- Sección Información -->
                <div class="seccion-contenido" id="informacion">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-info-circle'></i> Página de Información</h2>
                        <p>Consulta y exporta registros por períodos específicos</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Consulta por Períodos</h3>
                        <p class="descripcion-paso">En esta página puedes visualizar los registros que se han agregado filtrados por día, mes y año, así como descargar dichos registros en formato Excel o PDF.</p>
                        <img src="../../public/img/manual/psi10.png" alt="Interfaz de información con filtros y opciones de descarga" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Vista Completa</h3>
                        <p class="descripcion-paso">La página muestra todos los registros con opciones de filtrado avanzado para una búsqueda específica.</p>
                        <img src="../../public/img/manual/psi11.png" alt="Vista completa de la página de información" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">3</span>
                        <h3 class="titulo-paso">Opciones de Filtrado</h3>
                        <p class="descripcion-paso">Filtra la información por:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-calendar'></i> <strong>Por día específico</strong></li>
                            <li><i class='bx bx-calendar-week'></i> <strong>Por mes completo</strong></li>
                            <li><i class='bx bx-calendar-event'></i> <strong>Por año académico</strong></li>
                        </ul>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">4</span>
                        <h3 class="titulo-paso">Exportación de Datos</h3>
                        <p class="descripcion-paso">Opciones de descarga disponibles:</p>
                        <div style="margin-left: 50px; margin-top: 15px;">
                            <p><i class='bx bx-spreadsheet'></i> <strong>Excel (.xlsx)</strong> - Para análisis detallado de datos</p>
                            <p><i class='bx bx-file'></i> <strong>PDF (.pdf)</strong> - Para reportes formales e impresión</p>
                        </div>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Uso Recomendado</h3>
                        <p>Utiliza Excel para análisis estadístico detallado y PDF para reportes oficiales y presentaciones.</p>
                    </div>
                </div>

                <!-- Sección Papelera -->
                <div class="seccion-contenido" id="papelera">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-trash'></i> Gestión de Papelera</h2>
                        <p>Administra registros eliminados y recupera información</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Acceder a la Papelera</h3>
                        <p class="descripcion-paso">Al hacer clic en el botón "Papelera" aparecen los registros eliminados. Desde aquí puedes restaurar registros individuales o eliminar todo permanentemente.</p>
                        <img src="../../public/img/manual/psi4.png" alt="Vista de la papelera con registros eliminados" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Opciones de Papelera</h3>
                        <p class="descripcion-paso">Funciones disponibles en la papelera:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-reset'></i> <strong>Restaurar</strong> - Devolver registro al sistema</li>
                            <li><i class='bx bx-show'></i> <strong>Ver detalles</strong> - Revisar información eliminada</li>
                            <li><i class='bx bx-trash-alt'></i> <strong>Eliminar permanentemente</strong> - Borrado definitivo</li>
                            <li><i class='bx bx-broom'></i> <strong>Eliminar todo</strong> - Vaciar papelera completa</li>
                        </ul>
                    </div>

                    <div class="caja-advertencia">
                        <p class='advertencia'><i class='bx bx-error'></i> Eliminación Permanente</p>
                        <p>Una vez eliminado permanentemente, <strong>NO se puede recuperar</strong>. Úsalo solo cuando estés completamente seguro, especialmente con información psicológica sensible.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Funcionalidad de navegación
        document.addEventListener('DOMContentLoaded', function() {
            const itemsMenu = document.querySelectorAll('.item-menu');
            const seccionesContenido = document.querySelectorAll('.seccion-contenido');
            
            itemsMenu.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const seccionObjetivo = this.getAttribute('data-seccion');
                    mostrarSeccion(seccionObjetivo);
                    
                    // Actualizar elemento de menú activo
                    itemsMenu.forEach(mi => mi.classList.remove('activo'));
                    this.classList.add('activo');
                });
            });
            
            // Funcionalidad de búsqueda
            const entradaBusqueda = document.getElementById('entradaBusqueda');
            entradaBusqueda.addEventListener('input', function() {
                const terminoBusqueda = this.value.toLowerCase();
                const todasSecciones = document.querySelectorAll('.seccion-contenido');
                
                todasSecciones.forEach(seccion => {
                    const contenido = seccion.textContent.toLowerCase();
                    const contenedoresPaso = seccion.querySelectorAll('.contenedor-paso, .tarjeta-accion');
                    
                    contenedoresPaso.forEach(contenedor => {
                        const contenidoContenedor = contenedor.textContent.toLowerCase();
                        if (contenidoContenedor.includes(terminoBusqueda) || terminoBusqueda === '') {
                            contenedor.style.display = 'block';
                        } else {
                            contenedor.style.display = 'none';
                        }
                    });
                });
            });
        });
        
        function mostrarSeccion(idSeccion) {
            const seccionesContenido = document.querySelectorAll('.seccion-contenido');
            seccionesContenido.forEach(seccion => {
                seccion.classList.remove('activa');
            });
            
            const seccionObjetivo = document.getElementById(idSeccion);
            if (seccionObjetivo) {
                seccionObjetivo.classList.add('activa');
            }
        }
    </script>
</body>

</html>