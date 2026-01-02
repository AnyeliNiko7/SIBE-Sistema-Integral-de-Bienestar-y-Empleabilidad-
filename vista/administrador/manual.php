<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['area'] !== 'Administrador') {
    header("location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Administrador</title>
    <link rel="icon" href="../../public/img/icono.png">
    <link rel="stylesheet" href="../../public/css/sidebar.css?v=<?php echo filemtime('../../public/css/sidebar.css'); ?>">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo filemtime('../../public/css/admin.css'); ?>">
    <link rel="stylesheet" href="../../public/css/manual.css?v=<?php echo filemtime('../../public/css/manual.css'); ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="../../public/js/script.js" defer></script>
</head>
<body>
    <?php include '../../includes/asideAdmin.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <main class="home-section">
        <div class="contenedor-manual">
            <!-- Encabezado -->
            <div class="encabezado-manual">
                <div class="encabezado-superior">
                    <div class="logo-manual">
                        <h1></i> Manual del Sistema Administrador</h1>
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
                    <a href="#" class="item-menu" data-seccion="usuarios">
                        <i class='bx bx-user'></i> Gestión de Usuarios
                    </a>
                    <a href="#" class="item-menu" data-seccion="crear-usuarios">
                        <i class='bx bx-user-plus'></i> Crear Usuarios
                    </a>
                    <a href="#" class="item-menu" data-seccion="reportes">
                        <i class='bx bx-bar-chart-alt-2'></i> Reportes por Áreas
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
                        <div class="tarjeta-accion" onclick="mostrarSeccion('usuarios')">
                            <div class="icono-accion"><i class='bx bx-user'></i></div>
                            <h3>Gestión de Usuarios</h3>
                            <p>Visualiza y gestiona todos los usuarios del sistema</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('crear-usuarios')">
                            <div class="icono-accion"><i class='bx bx-user-plus'></i></div>
                            <h3>Crear Usuarios</h3>
                            <p>Agrega nuevos usuarios al sistema</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('reportes')">
                            <div class="icono-accion"><i class='bx bx-bar-chart-alt-2'></i></div>
                            <h3>Reportes por Áreas</h3>
                            <p>Consulta reportes de todas las áreas del sistema</p>
                        </div>
                        <div class="tarjeta-accion" onclick="mostrarSeccion('informacion')">
                            <div class="icono-accion"><i class='bx bx-info-circle'></i></div>
                            <h3>Información General</h3>
                            <p>Accede a información y exportación de datos</p>
                        </div>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Funcionalidades Principales</h3>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li><strong>Gestión de usuarios</strong> - Administra todos los usuarios del sistema</li>
                            <li><strong>Creación de cuentas</strong> - Agrega nuevos usuarios con diferentes roles</li>
                            <li><strong>Reportes consolidados</strong> - Visualiza datos de todas las áreas</li>
                            <li><strong>Exportación de datos</strong> - Descarga información en Excel y PDF</li>
                        </ul>
                    </div>
                </div>

                <!-- Sección Gestión de Usuarios -->
                <div class="seccion-contenido" id="usuarios">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-user'></i> Gestión de Usuarios</h2>
                        <p>Página principal para visualizar y gestionar usuarios del sistema</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Página Principal de Usuarios</h3>
                        <p class="descripcion-paso">Esta es la página principal donde puedes visualizar todos los usuarios registrados en el sistema. La tabla muestra información detallada de cada usuario.</p>
                        <img src="../../public/img/manual/admin1.png" alt="Vista principal de gestión de usuarios" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Cambiar Contraseña</h3>
                        <p class="descripcion-paso">Al hacer clic en el botón “Cambiar contraseña”, se abrirá una ventana donde podrás ingresar una nueva contraseña (máximo 8 caracteres).
Si deseas ver lo que escribes, haz clic en el ícono del ojo; para ocultarlo nuevamente, vuelve a hacer clic en el mismo ícono.
Finalmente, haz clic en “Actualizar” para guardar los cambios. Para cancelar, puedes hacer clic fuera del área o en la “X”.</p>
                        <img src="../../public/img/manual/admin2.png" alt="Ventana de cambio de contraseña" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">3</span>
                        <h3 class="titulo-paso">Eliminar Usuario</h3>
                        <p class="descripcion-paso">El botón "Eliminar" permite remover permanentemente un usuario del sistema. Esta acción no se puede deshacer.</p>
                        
                        <div class="caja-advertencia">
                            <p class='advertencia'><i class='bx bx-error'></i> Advertencia</p>
                            <p>La eliminación de usuarios es <strong>PERMANENTE</strong> y no se puede recuperar. Úsalo solo cuando estés completamente seguro.</p>
                        </div>
                    </div>
                </div>

                <!-- Sección Crear Usuarios -->
                <div class="seccion-contenido" id="crear-usuarios">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-user-plus'></i> Crear Nuevos Usuarios</h2>
                        <p>Agrega nuevos usuarios al sistema con diferentes roles y permisos</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Formulario de Creación</h3>
                        <p class="descripcion-paso">En esta página puedes crear nuevos usuarios. El nombre de usuario y correo deben ser únicos en el sistema.</p>
                        <img src="../../public/img/manual/admin3.png" alt="Formulario para crear nuevos usuarios" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Recomendaciones</h3>
                        <p class="descripcion-paso">Para un mejor uso del sistema:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-user-circle'></i> <strong>Usuario corto</strong> - Facilita el ingreso rápido al sistema</li>
                            <li><i class='bx bx-envelope'></i> <strong>Correo único</strong> - No puede repetirse en el sistema</li>
                            <li><i class='bx bx-cog'></i> <strong>Área específica</strong> - Selecciona el área de acceso permitido</li>
                            <li><i class='bx bx-check'></i> <strong>Crear Usuario</strong> - Guarda el nuevo usuario en el sistema</li>
                        </ul>
                    </div>

                    <div class="caja-consejo">
                        <h3><i class='bx bx-bulb'></i> Datos Importantes</h3>
                        <p>Procura que el nombre de usuario sea corto y fácil de recordar para facilitar el acceso al sistema.</p>
                    </div>
                </div>

                <!-- Sección Reportes por Áreas -->
                <div class="seccion-contenido" id="reportes">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-bar-chart-alt-2'></i> Reportes por Áreas</h2>
                        <p>Visualiza reportes consolidados de todas las áreas del sistema</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Vista de Reportes Consolidados</h3>
                        <p class="descripcion-paso">En esta página puedes visualizar los reportes generados por cada área (Tópico, Psicología, Consejería y Jefatura). Se muestran todas las acciones realizadas con opciones de filtrado avanzado.</p>
                        <img src="../../public/img/manual/admin4.png" alt="Dashboard de reportes por áreas" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Filtrado de Datos</h3>
                        <p class="descripcion-paso">Para realizar correctamente el filtrado de datos, sigue estas indicaciones:</p>
                        <img src="../../public/img/manual/admin5.png" alt="Opciones de filtrado de datos" class="imagen-paso">
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">3</span>
                        <h3 class="titulo-paso">Opciones de Filtrado</h3>
                        <p class="descripcion-paso">Filtra la información por diferentes criterios:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-calendar'></i> <strong>Por día específico</strong></li>
                            <li><i class='bx bx-calendar-week'></i> <strong>Por mes completo</strong></li>
                            <li><i class='bx bx-calendar-event'></i> <strong>Por año académico</strong></li>
                        </ul>
                    </div>

                    <div class="caja-informacion">
                        <h3><i class='bx bx-export'></i> Exportación de Datos</h3>
                        <p>Puedes exportar los registros en Excel o PDF, así como exportar los gráficos estadísticos en formato de tabla.</p>
                    </div>
                </div>

                <!-- Sección Información -->
                <div class="seccion-contenido" id="informacion">
                    <div class="encabezado-seccion">
                        <h2><i class='bx bx-info-circle'></i> Información General</h2>
                        <p>Consulta y exporta información del sistema</p>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">1</span>
                        <h3 class="titulo-paso">Funcionalidades de Información</h3>
                        <p class="descripcion-paso">Como administrador, tienes acceso a toda la información del sistema con capacidades avanzadas de filtrado y exportación.</p>
                        
                        <div class="caja-consejo">
                            <h3><i class='bx bx-data'></i> Capacidades de Exportación</h3>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li><strong>Excel (.xlsx)</strong> - Para análisis detallado y procesamiento de datos</li>
                                <li><strong>PDF (.pdf)</strong> - Para reportes formales y documentación</li>
                                <li><strong>Gráficos estadísticos</strong> - Exportación en formato de tabla</li>
                            </ul>
                        </div>
                    </div>

                    <div class="contenedor-paso">
                        <span class="numero-paso">2</span>
                        <h3 class="titulo-paso">Acceso Completo</h3>
                        <p class="descripcion-paso">Tu rol de administrador te permite:</p>
                        <ul style="margin-left: 50px; margin-top: 10px;">
                            <li><i class='bx bx-shield'></i> <strong>Gestión total de usuarios</strong></li>
                            <li><i class='bx bx-chart'></i> <strong>Reportes de todas las áreas</strong></li>
                            <li><i class='bx bx-download'></i> <strong>Exportación completa de datos</strong></li>
                            <li><i class='bx bx-cog'></i> <strong>Configuración del sistema</strong></li>
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