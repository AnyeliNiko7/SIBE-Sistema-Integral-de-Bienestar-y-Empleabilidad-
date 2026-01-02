<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Consejería') {
    header("location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Consejería</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/consejeria.css?v=<?php echo filemtime('../../public/css/consejeria.css'); ?>">
    <link rel="stylesheet" href="../../public/css/manual.css?v=<?php echo filemtime('../../public/css/manual.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>

<body>
    <?php include '../../includes/asideConsejeria.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <div class="contenedor-manual">
            <!-- Encabezado -->
            <div class="encabezado-manual">
                <div class="encabezado-superior">
                    <div class="logo-manual">
                        <h1></i> Manual del Sistema de Consejería</h1>
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
                        <i class='bx bx-history'></i> Historial de Registros
                    </a>
                    <a href="#" class="item-menu" data-seccion="crear-registros">
                        <i class='bx bx-user-plus'></i> Crear Registros
                    </a>
                    <a href="#" class="item-menu" data-seccion="gestion-registros">
                        <i class='bx bx-edit-alt'></i> Gestión de Registros
                    </a>
                    <a href="#" class="item-menu" data-seccion="informacion">
                        <i class='bx bx-info-circle'></i> Información
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
                            <h3>Historial de Registros</h3>
                            <p>Visualiza y gestiona todos los registros de tutores</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('crear-registros')">
                            <div class="icono-accion"><i class='bx bx-user-plus'></i></div>
                            <h3>Crear Registros</h3>
                            <p>Agrega nuevos registros de tutores al sistema</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('gestion-registros')">
                            <div class="icono-accion"><i class='bx bx-edit-alt'></i></div>
                            <h3>Gestión de Registros</h3>
                            <p>Edita, elimina y restaura registros de tutores</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('informacion')">
                            <div class="icono-accion"><i class='bx bx-info-circle'></i></div>
                            <h3>Información General</h3>
                            <p>Accede a información sobre el sistema</p>
                        </div>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Funcionalidades Principales</h3>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li><strong>Gestión de tutores</strong> - Administra todos los registros de tutores</li>
                            <li><strong>Creación de registros</strong> - Agrega nuevos tutores con sus datos</li>
                            <li><strong>Edición de información</strong> - Actualiza datos de tutores existentes</li>
                            <li><strong>Papelera de registros</strong> - Recupera o elimina permanentemente registros</li>
                        </ul>
                    </div>
                </div>

                <!-- Sección Historial de Registros -->
                <div class="seccion-contenido" id="historial">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-history'></i> Historial de Registros</h2>
                        <p>Página principal para visualizar y gestionar registros de tutores</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página Principal de Historial</h3>
                        <p class="descripcion-paso">Esta es la página principal donde puedes visualizar todos los tutores que han sido registrados en el sistema. La tabla muestra información detallada de cada tutor.</p>
                        <img src="../../public/img/manual/conse1.jpg" alt="Vista principal del historial de registros" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Funcionalidad de Botones</h3>
                        <p class="descripcion-paso">Cada botón en la tabla tiene una función específica para gestionar los registros de tutores:</p>
                        <img src="../../public/img/manual/conse2.png" alt="Funcionalidad de botones en la tabla" class="imagen-paso">
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-info-circle'></i> Información Importante</h3>
                        <p>Desde esta página puedes acceder a todas las funciones de gestión de registros de tutores, incluyendo edición, eliminación y restauración.</p>
                    </div>
                </div>

                <!-- Sección Crear Registros -->
                <div class="seccion-contenido" id="crear-registros">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-user-plus'></i> Crear Registros de Tutor</h2>
                        <p>Agrega nuevos registros de tutores al sistema</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Formulario de Creación</h3>
                        <p class="descripcion-paso">En esta página puedes agregar los tutores que representan a cada carrera, semestre y turno. Completa todos los campos requeridos para crear un nuevo registro.</p>
                        <img src="../../public/img/manual/conse5.jpg" alt="Formulario para crear registros de tutores" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Recomendaciones</h3>
                        <p class="descripcion-paso">Para un mejor uso del sistema:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-user-circle'></i> <strong>Datos completos</strong> - Proporciona toda la información requerida</li>
                            <li><i class='bx bx-book'></i> <strong>Carrera específica</strong> - Selecciona la carrera correspondiente al tutor</li>
                            <li><i class='bx bx-calendar'></i> <strong>Semestre correcto</strong> - Indica el semestre actual del tutor</li>
                            <li><i class='bx bx-time'></i> <strong>Turno adecuado</strong> - Selecciona el turno (matutino, vespertino, etc.)</li>
                        </ul>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Datos Importantes</h3>
                        <p>Asegúrate de que toda la información del tutor sea correcta y actualizada para mantener la integridad de los datos del sistema.</p>
                    </div>
                </div>

                <!-- Sección Gestión de Registros -->
                <div class="seccion-contenido" id="gestion-registros">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-edit-alt'></i> Gestión de Registros</h2>
                        <p>Edita, elimina y restaura registros de tutores</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Editar Registros</h3>
                        <p class="descripcion-paso">En la tabla de registros, al hacer clic en el botón "Editar", puedes actualizar cualquier dato que no sea correcto. Luego haz clic en "Actualizar" para guardar los cambios. Si no deseas realizar cambios, puedes cancelar haciendo clic en la "X" o fuera del área de edición.</p>
                        <img src="../../public/img/manual/conse3.png" alt="Ventana de edición de registros" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Eliminar y Restaurar Registros</h3>
                        <p class="descripcion-paso">Al hacer clic en el botón "Eliminar", se muestra la papelera con los registros eliminados. Desde aquí puedes restaurar registros o eliminarlos permanentemente al vaciar la papelera. El botón "Volver" te regresa a la página de inicio.</p>
                        <img src="../../public/img/manual/conse4.png" alt="Papelera de registros eliminados" class="imagen-paso">
                    </div>

                    <div class="caja-advertencia">
                        <p class='advertencia'><i class='bx bx-error'></i> Advertencia</p>
                        <p>La eliminación permanente de registros <strong>NO SE PUEDE DESHACER</strong>. Asegúrate de que realmente deseas eliminar el registro antes de vaciar la papelera.</p>
                    </div>
                </div>

                <!-- Sección Información -->
                <div class="seccion-contenido" id="informacion">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-info-circle'></i> Información General</h2>
                        <p>Consulta información sobre el sistema de consejería</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Funcionalidades del Sistema</h3>
                        <p class="descripcion-paso">Como consejero, tienes acceso a todas las funciones de gestión de registros de tutores con capacidades completas de administración.</p>
                        
                        <div class="caja-consejo">
                            <h3><i class='bx bx-data'></i> Capacidades del Rol</h3>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li><strong>Creación de registros</strong> - Agregar nuevos tutores al sistema</li>
                                <li><strong>Edición de información</strong> - Actualizar datos de tutores existentes</li>
                                <li><strong>Eliminación temporal</strong> - Enviar registros a la papelera</li>
                                <li><strong>Restauración</strong> - Recuperar registros eliminados</li>
                                <li><strong>Eliminación permanente</strong> - Vaciar la papelera de registros</li>
                            </ul>
                        </div>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Área de Responsabilidad</h3>
                        <p class="descripcion-paso">Tu rol de consejero te permite gestionar:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-user'></i> <strong>Registros de tutores</strong> - Información completa de cada tutor</li>
                            <li><i class='bx bx-book'></i> <strong>Datos académicos</strong> - Carrera, semestre y turno</li>
                            <li><i class='bx bx-history'></i> <strong>Historial completo</strong> - Todos los registros del sistema</li>
                            <li><i class='bx bx-trash'></i> <strong>Gestión de papelera</strong> - Recuperación y eliminación permanente</li>
                        </ul>
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