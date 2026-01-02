<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Jefatura') {
    header("location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Jefatura - Seguimiento de Egresados</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/jefatura.css?v=<?php echo filemtime('../../public/css/jefatura.css'); ?>">
    <link rel="stylesheet" href="../../public/css/manual.css?v=<?php echo filemtime('../../public/css/manual.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideJefatura.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <div class="contenedor-manual">
            <!-- Encabezado -->
            <div class="encabezado-manual">
                <div class="encabezado-superior">
                    <div class="logo-manual">
                        <h1><i class='bx bx-user-check'></i> Manual de Jefatura - Seguimiento de Egresados</h1>
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
                        <i class='bx bx-history'></i> Historial de Egresados
                    </a>
                    <a href="#" class="item-menu" data-seccion="crear-registros">
                        <i class='bx bx-user-plus'></i> Crear Registros
                    </a>
                    <a href="#" class="item-menu" data-seccion="gestion-registros">
                        <i class='bx bx-edit-alt'></i> Gestión de Registros
                    </a>
                    <a href="#" class="item-menu" data-seccion="seguimiento">
                        <i class='bx bx-trending-up'></i> Seguimiento
                    </a>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="contenido-principal">
                <!-- Sección Inicio -->
                <div class="seccion-contenido activa" id="inicio">
                    <div class="acciones-rapidas">
                        <div class="tarjeta-accion" onclick="mostrarSeccion('historial')">
                            <div class="icono-accion"><i class='bx bx-history'></i></div>
                            <h3>Historial de Egresados</h3>
                            <p>Visualiza y gestiona todos los registros de egresados</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('crear-registros')">
                            <div class="icono-accion"><i class='bx bx-user-plus'></i></div>
                            <h3>Crear Registros</h3>
                            <p>Agrega nuevos registros de estudiantes interesados</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('gestion-registros')">
                            <div class="icono-accion"><i class='bx bx-edit-alt'></i></div>
                            <h3>Gestión de Registros</h3>
                            <p>Edita, elimina y restaura registros de estudiantes</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('seguimiento')">
                            <div class="icono-accion"><i class='bx bx-trending-up'></i></div>
                            <h3>Seguimiento</h3>
                            <p>Monitorea el progreso de los estudiantes</p>
                        </div>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Funcionalidades Principales</h3>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li><strong>Gestión de egresados</strong> - Administra todos los registros de estudiantes</li>
                            <li><strong>Registro de interesados</strong> - Captura datos de alumnos de colegios</li>
                            <li><strong>Seguimiento académico</strong> - Monitorea el progreso de los estudiantes</li>
                            <li><strong>Gestión de información</strong> - Actualiza y mantiene datos actualizados</li>
                        </ul>
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-info-circle'></i> Acerca del Sistema</h3>
                        <p>El sistema de Seguimiento de Egresados permite gestionar eficientemente la información de estudiantes interesados en ingresar al IESTP, facilitando el proceso de captación y seguimiento académico.</p>
                    </div>
                </div>

                <!-- Sección Historial de Egresados -->
                <div class="seccion-contenido" id="historial">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-history'></i> Historial de Registros de Egresados</h2>
                        <p>Página principal para visualizar y gestionar registros de estudiantes</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página Principal del Historial</h3>
                        <p class="descripcion-paso">Esta es la página principal donde puedes visualizar todos los alumnos de diferentes colegios que han sido registrados en el sistema. La tabla muestra información detallada de cada estudiante interesado.</p>
                        <img src="../../public/img/manual/jefa1.png" alt="Vista principal del historial de egresados" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Funcionalidades Disponibles</h3>
                        <p class="descripcion-paso">Desde esta página puedes realizar diferentes acciones de gestión sobre los registros de estudiantes:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-show'></i> <strong>Visualizar información</strong> - Consulta todos los datos registrados</li>
                            <li><i class='bx bx-edit'></i> <strong>Editar registros</strong> - Actualiza información de estudiantes</li>
                            <li><i class='bx bx-trash'></i> <strong>Eliminar registros</strong> - Remueve registros temporalmente</li>
                            <li><i class='bx bx-history'></i> <strong>Acceder a papelera</strong> - Gestiona registros eliminados</li>
                        </ul>
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-data'></i> Información de la Tabla</h3>
                        <p>La tabla muestra información completa de cada estudiante incluyendo datos personales, colegio de procedencia, carrera de interés y fecha de registro.</p>
                    </div>
                </div>

                <!-- Sección Crear Registros -->
                <div class="seccion-contenido" id="crear-registros">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-user-plus'></i> Crear Registros de Estudiantes</h2>
                        <p>Agrega nuevos registros de alumnos interesados en el IESTP</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Formulario de Registro</h3>
                        <p class="descripcion-paso">En esta página puedes agregar a los alumnos de los colegios interesados para estudiar alguna carrera dentro del IESTP. Completa todos los campos requeridos y luego haz clic en el botón "Crear" para guardar el registro.</p>
                        <img src="../../public/img/manual/jefa5.png" alt="Formulario para crear registros de estudiantes" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Datos Requeridos</h3>
                        <p class="descripcion-paso">Para un registro completo y efectivo:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-user'></i> <strong>Datos personales</strong> - Nombre completo, DNI, contacto</li>
                            <li><i class='bx bx-building'></i> <strong>Colegio de procedencia</strong> - Institución educativa de origen</li>
                            <li><i class='bx bx-book'></i> <strong>Carrera de interés</strong> - Programa académico de preferencia</li>
                            <li><i class='bx bx-calendar'></i> <strong>Información académica</strong> - Año de egreso, promedio, etc.</li>
                        </ul>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Recomendaciones</h3>
                        <p>Verifica que toda la información ingresada sea correcta y actualizada. Los datos precisos facilitan el proceso de seguimiento y contacto con los estudiantes interesados.</p>
                    </div>
                </div>

                <!-- Sección Gestión de Registros -->
                <div class="seccion-contenido" id="gestion-registros">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-edit-alt'></i> Gestión de Registros</h2>
                        <p>Edita, elimina y restaura registros de estudiantes</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Editar Registros</h3>
                        <p class="descripcion-paso">En la tabla de registros, al hacer clic en "Editar" puedes actualizar cualquier dato que no sea correcto. Luego haz clic en "Actualizar" para guardar los cambios. Si no deseas realizar modificaciones, puedes cancelar haciendo clic en la "X" o fuera del área de edición.</p>
                        <img src="../../public/img/manual/jefa4.png" alt="Ventana de edición de registros" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Gestión de Papelera</h3>
                        <p class="descripcion-paso">Al hacer clic en el botón "Papelera", se muestran los registros eliminados temporalmente. Desde aquí puedes restaurar registros o eliminarlos permanentemente al vaciar la papelera.</p>
                        <img src="../../public/img/manual/jefa3.png" alt="Papelera de registros eliminados" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">3</span>
                        <h3 class="titulo-paso">Opciones de la Papelera</h3>
                        <p class="descripcion-paso">En la vista de papelera dispones de:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-undo'></i> <strong>Restaurar</strong> - Recuperar registros eliminados</li>
                            <li><i class='bx bx-trash'></i> <strong>Vaciar papelera</strong> - Eliminación permanente</li>
                            <li><i class='bx bx-arrow-back'></i> <strong>Volver</strong> - Regresar al historial principal</li>
                        </ul>
                    </div>

                    <div class="caja-advertencia">
                        <p class='advertencia'><i class='bx bx-error'></i> Advertencia Importante</p>
                        <p>La eliminación permanente de registros <strong>NO SE PUEDE DESHACER</strong>. Asegúrate de realizar backups periódicos y verificar cuidadosamente antes de vaciar la papelera.</p>
                    </div>
                </div>

                <!-- Sección Seguimiento -->
                <div class="seccion-contenido" id="seguimiento">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-trending-up'></i> Seguimiento de Egresados</h2>
                        <p>Monitorea el progreso y estadísticas de los estudiantes</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Proceso de Seguimiento</h3>
                        <p class="descripcion-paso">El sistema de seguimiento permite monitorear el progreso de los estudiantes interesados en tiempo real, facilitando la toma de decisiones estratégicas para la jefatura.</p>
                        
                        <div class="caja-informacion">
                            <h3><i class='bx bx-stats'></i> Métricas Disponibles</h3>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li><strong>Total de registros</strong> - Número total de estudiantes interesados</li>
                                <li><strong>Por colegio</strong> - Distribución por institución educativa</li>
                                <li><strong>Por carrera</strong> - Preferencias académicas de los estudiantes</li>
                                <li><strong>Tendencias temporales</strong> - Evolución de registros por período</li>
                            </ul>
                        </div>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Beneficios del Seguimiento</h3>
                        <p class="descripcion-paso">El sistema proporciona ventajas significativas:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-target-lock'></i> <strong>Captación efectiva</strong> - Identifica fuentes principales de estudiantes</li>
                            <li><i class='bx bx-trending-up'></i> <strong>Análisis de tendencias</strong> - Detecta patrones de interés académico</li>
                            <li><i class='bx bx-calendar-event'></i> <strong>Planificación estratégica</strong> - Optimiza recursos y actividades</li>
                            <li><i class='bx bx-chart'></i> <strong>Reportes automáticos</strong> - Genera informes para toma de decisiones</li>
                        </ul>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Mejores Prácticas</h3>
                        <p>Revisa periódicamente las métricas del sistema para identificar oportunidades de mejora en el proceso de captación y seguimiento de estudiantes interesados en el IESTP.</p>
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