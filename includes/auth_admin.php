<?php
/* =========================================================
 *  includes/auth_admin.php
 *  Solo verifica la sesión del administrador.
 *  NO incluye header.php — cada página lo hace ella misma
 *  DESPUÉS de definir $tituloPagina y cargar funciones.php
 * ========================================================= */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// $tituloPagina debe definirse en la página que hace el include
if (!isset($tituloPagina)) {
    $tituloPagina = 'Sistema de Asistencia';
}
// header.php ya NO se incluye aquí.
// Cada página admin lo incluye después de cargar funciones.php