<aside class="sidebar <?php echo isset($_SESSION['usuario']) ? '' : 'collapsed'; ?>">
    <div class="sidebar-header">
        <div class="user-section">
            <div class="user-info">
                <span class="user-greeting">
                    <?php
                    if (isset($_SESSION['usuario'])) {
                        echo "Hola, <b>" . htmlspecialchars($_SESSION['usuario']) . "</b>";
                    } else {
                        echo "Hola, Usuario no encontrado";
                    }
                    ?>
                </span>
                <button class="sidebar-toggle">
                    <i class="bx bx-menu"></i>
                </button>
            </div>
            <div class="logo" id="logo">
                <img src="../../public/img/instituto.webp" alt="Logo Institucional" class="header-logo">
            </div>
        </div>
    </div>
    <div class="sidebar-content">
        <ul class="menu-list">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="menu-item">
                <a href="../../vista/topico/inicio.php" class="menu-link <?php echo $current_page == 'inicio.php' ? 'active' : ''; ?>" data-tooltip="Historial">
                    <i class='bx bx-grid-alt'></i>
                    <span class="menu-label">Inicio Historial</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../../vista/topico/acciones.php" class="menu-link <?php echo $current_page == 'acciones.php' ? 'active' : ''; ?>" data-tooltip="Agregar Registro">
                    <i class='bx bxs-user-plus'></i>
                    <span class="menu-label">Nuevo registro</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../../vista/topico/reportes.php" class="menu-link <?php echo $current_page == 'reportes.php' ? 'active' : ''; ?>" data-tooltip="Reportes">
                    <i class='bx bxs-objects-vertical-bottom'></i>
                    <span class="menu-label">Reportes</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../../vista/topico/informacion.php" class="menu-link <?php echo $current_page == 'informacion.php' ? 'active' : ''; ?>" data-tooltip="Gestión de información">
                    <i class='bx bxs-file-blank'></i>
                    <span class="menu-label">Información</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../../vista/topico/medicamentos.php" class="menu-link <?php echo $current_page == 'medicamentos.php' ? 'active' : ''; ?>" data-tooltip="Gestión de medicamentos">
                    <i class='bx bx-plus-medical'></i>
                    <span class="menu-label">Medicamentos</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../../vista/topico/manual.php" class="menu-link <?php echo $current_page == 'manual.php' ? 'active' : ''; ?>" data-tooltip="Manual de ayuda">
                    <i class='bx bxs-book-bookmark'></i>
                    <span class="menu-label">Manual</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <a href="../../config/logout.php" class="logout-btn">
            <div class="logout-label">
                <i class='bx bx-log-out'></i>
                <span class="logout-text">Cerrar Sesión</span>
            </div>
        </a>
    </div>
</aside>