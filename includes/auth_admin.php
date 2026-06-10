<?php
/* =========================================================
 *  includes/auth_admin.php
 * ========================================================= */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($tituloPagina)) {
    $tituloPagina = 'Sistema de Asistencia';
}