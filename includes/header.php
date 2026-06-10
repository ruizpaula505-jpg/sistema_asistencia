<?php
/* =========================================================
 *  includes/header.php
 *  Abre el HTML de todas las páginas del panel admin.
 *  Cada página define $tituloPagina ANTES de incluir este archivo.
 *  footer.php cierra el </body> y </html> que este archivo abre.
 * ========================================================= */

// Verifica si $tituloPagina fue definida en el archivo que hace el include
// Si no existe, asigna un título genérico por defecto
if (!isset($tituloPagina)) {
    $tituloPagina = 'Sistema de Asistencia';
}
?>
<!DOCTYPE html>
<!-- lang="es" indica al navegador que el contenido está en español -->
<html lang="es">
<head>
    <!-- UTF-8 permite mostrar tildes, ñ y caracteres especiales correctamente -->
    <meta charset="UTF-8">
    <!-- viewport hace que el diseño sea responsive en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- e() escapa el título para evitar XSS, cada página define su propio $tituloPagina -->
    <title><?= e($tituloPagina) ?></title>

    <!-- Bootstrap CSS desde CDN: proporciona grillas, componentes y utilidades -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons desde CDN: íconos usados con clases bi-* -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- admin.css: estilos propios del proyecto, separados del HTML -->
    <link href="../css/styles.css" rel="stylesheet">
</head>
<!-- El </body> y </html> los cierra footer.php -->
<body>
