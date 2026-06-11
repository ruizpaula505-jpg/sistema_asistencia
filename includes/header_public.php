<?php
/* =========================================================
 *  includes/header_public.php
 *  Header para páginas en la raíz del proyecto (index.php).
 *  La ruta al CSS es directa sin ../ porque está en raíz.
 * ========================================================= */

if (!isset($tituloPagina)) {
    $tituloPagina = 'Sistema de Asistencia';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
