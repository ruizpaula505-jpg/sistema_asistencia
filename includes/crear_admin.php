<?php

// Verifica que el script se ejecute desde la terminal (CMD)
// php_sapi_name() devuelve 'cli' si se ejecuta desde terminal, 'apache2handler' si es desde el navegador
if (php_sapi_name() !== 'cli') {
    // die() detiene el script y muestra el mensaje
    die("Este script solo puede ejecutarse desde la terminal.\n");
}

// Importa la clase Database desde config/db.php
// __DIR__ es la ruta absoluta de la carpeta donde está este archivo
require_once __DIR__ . '/../config/db.php';

// $argc = número de argumentos recibidos al ejecutar el script
// $argv = arreglo con cada argumento
// Ejemplo de ejecución:
// php admin/crear_admin.php 1234567890 1234 admin1234 Administrador
//
// $argv[0] = admin/crear_admin.php  <- nombre del script
// $argv[1] = 1234567890             <- documento
// $argv[2] = 1234                   <- pin
// $argv[3] = admin1234              <- password
// $argv[4] = Administrador          <- nombre
//
// Si faltan argumentos ($argc < 5), muestra cómo usarlo y sale
if ($argc < 5) {
    echo "Uso: php includes/crear_admin.php <documento> <pin> <password> <nombre>\n";
    // exit(1) termina el script con código de error
    exit(1);
}

// Asigna cada argumento a una variable legible
// ?? '' evita error si el argumento no existe
$doc      = $argv[1] ?? '';
$pin      = $argv[2] ?? '';
$password = $argv[3] ?? '';
$name     = $argv[4] ?? '';

// strlen() cuenta los caracteres de la contraseña
// Si tiene menos de 8 caracteres, rechaza y sale
if (strlen($password) < 8) {
    echo "La contraseña debe tener mínimo 8 caracteres.\n";
    exit(1);
}

try {

    // Instancia la clase Database definida en config/db.php
    $db = new Database();

    // Llama al método conectar() que retorna un objeto PDO listo para usar
    $pdo = $db->conectar();

    // prepare() protege contra inyección SQL usando el ? como marcador de posición
    // Consulta cuántos usuarios tienen ese documento
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE documento = ?
    ");

    // execute() reemplaza el ? por el valor real y ejecuta la consulta
    $stmt->execute([$doc]);

    // fetchColumn() trae el primer valor de la primera columna (el COUNT)
    // Si es mayor a 0, el documento ya existe
    if ($stmt->fetchColumn() > 0) {
        echo "Error: El documento $doc ya existe.\n";
        exit(1);
    }

    // password_hash() encripta la contraseña de forma segura
    // PASSWORD_DEFAULT usa el algoritmo bcrypt
    // Nunca se guarda la contraseña en texto plano
    $hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Prepara el INSERT con 4 marcadores de posición (?)
    $insert = $pdo->prepare("
        INSERT INTO users
        (
            documento,
            pin,
            password,
            nombre_completo
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ");

    // Ejecuta el INSERT pasando los valores en el mismo orden que los ?
    $insert->execute([
        $doc,   // documento
        $pin,   // pin
        $hash,  // contraseña encriptada (no $password)
        $name   // nombre completo
    ]);

    // Muestra resumen del administrador creado
    echo "\n";
    echo "=====================================\n";
    echo "ADMINISTRADOR CREADO CORRECTAMENTE\n";
    echo "=====================================\n";
    echo "Documento: $doc\n";
    echo "PIN: $pin\n";
    echo "Nombre: $name\n";
    echo "Contraseña almacenada encriptada\n";

} catch (Exception $e) {
    // Si algo falla (conexión, SQL, etc.), captura el error y lo muestra
    // getMessage() devuelve el mensaje de error de la excepción
    echo "Error: " . $e->getMessage() . "\n";
}
