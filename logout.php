<?php
// Inicia la sesión para poder manipularla y destruirla
session_start();
 
// Vacía el arreglo de sesión eliminando todas las variables guardadas
// Esto incluye admin_id, admin_nombre y cualquier otra variable de sesión
$_SESSION = [];
 
// Verifica si la sesión usa cookies (configuración en php.ini)
// ini_get() lee el valor de una directiva de configuración de PHP
if (ini_get('session.use_cookies')) {
 
    // Obtiene los parámetros actuales de la cookie de sesión
    // Devuelve un arreglo con path, domain, secure, httponly, etc.
    $params = session_get_cookie_params();
 
    // Elimina la cookie de sesión del navegador del usuario
    // Se hace enviando la misma cookie con una fecha de expiración en el pasado
    setcookie(
        session_name(),    // Nombre de la cookie de sesión (por defecto 'PHPSESSID')
        '',                // Valor vacío para limpiar la cookie
        time() - 42000,   // Tiempo en el pasado para que el navegador la elimine
        $params['path'],   // Ruta donde aplica la cookie
        $params['domain'], // Dominio donde aplica la cookie
        $params['secure'], // true si solo se envía por HTTPS
        $params['httponly'] // true para que no sea accesible desde JavaScript
    );
}
 
// Destruye completamente la sesión en el servidor
session_destroy();
 
// Redirige al login de administrador después de cerrar sesión
header('Location: admin/login.php');
 
// Detiene el script para que no ejecute nada más después del redirect
exit;