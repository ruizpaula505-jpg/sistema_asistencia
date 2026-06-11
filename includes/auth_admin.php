<?php
/* =========================================================
 *  includes/auth_admin.php
 * ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($tituloPagina)) {
    $tituloPagina = 'Sistema de Asistencia';
}