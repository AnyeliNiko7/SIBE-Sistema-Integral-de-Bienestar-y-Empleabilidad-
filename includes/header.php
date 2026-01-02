<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-header">
    <div class="header-content">
    <h1 class="header-title">BIENESTAR Y EMPLEABILIDAD</h1>
    </div>
</header>