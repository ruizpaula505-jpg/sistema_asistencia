<?php
 
// ── Función e() ───────────────────────────────────────────
// Escapa caracteres especiales HTML para evitar ataques XSS
// ?string acepta null además de string
// Devuelve siempre un string seguro para mostrar en HTML
function e(?string $valor): string
{
    // htmlspecialchars() convierte < > " ' & en sus entidades HTML
    // ENT_QUOTES escapa tanto comillas simples como dobles
    // 'UTF-8' indica la codificación de caracteres
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
 
// ── Función validarDocumento() ────────────────────────────
// Valida que el documento tenga entre 5 y 20 dígitos numéricos
function validarDocumento(string $documento): bool
{
    // preg_match() retorna 1 si coincide, 0 si no
    // ^ inicio, \d{5,20} entre 5 y 20 dígitos, $ fin
    // (bool) convierte el 1 o 0 a true o false
    return (bool) preg_match('/^\d{5,20}$/', $documento);
}
 
// ── Función validarPin() ──────────────────────────────────
// Valida que el PIN tenga exactamente 4 dígitos numéricos
function validarPin(string $pin): bool
{
    // \d{4} significa exactamente 4 dígitos
    return (bool) preg_match('/^\d{4}$/', $pin);
}
 
// ── Función validarPasswordAdmin() ───────────────────────
// Valida que la contraseña sea alfanumérica y tenga entre 8 y 20 caracteres
function validarPasswordAdmin(string $password): bool
{
    // [a-zA-Z0-9] solo letras y números, {8,20} entre 8 y 20 caracteres
    return (bool) preg_match('/^[a-zA-Z0-9]{8,20}$/', $password);
}
 
// ── Función mensajeAlerta() ───────────────────────────────
// Genera el HTML de un alert de Bootstrap con el tipo y texto indicados
function mensajeAlerta(string $tipo, string $texto): string
{
    // Mapea el tipo recibido a la clase CSS de Bootstrap correspondiente
    $clases = [
        'success' => 'alert-success', // verde
        'warning' => 'alert-warning', // amarillo
        'danger'  => 'alert-danger',  // rojo
        'info'    => 'alert-info',    // azul
    ];
 
    // ?? 'alert-danger' usa rojo por defecto si el tipo no está en el arreglo
    $clase = $clases[$tipo] ?? 'alert-danger';
 
    // Construye y retorna el HTML del alert con botón para cerrarlo
    // data-bs-dismiss="alert" es el atributo de Bootstrap para cerrar el alert
    return '<div class="alert ' . $clase . ' alert-dismissible fade show" role="alert">'
        . e($texto)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
        . '</div>';
}
 
// ── Función formatearFechaHora() ──────────────────────────
// Convierte una fecha del formato 'Y-m-d H:i:s' al formato 'd/m/Y H:i'
// ?string acepta null por si la fecha no existe (salida pendiente)
function formatearFechaHora(?string $fecha): string
{
    // Si la fecha es null o vacía retorna un guión largo como indicador
    if ($fecha === null || $fecha === '') {
        return '—';
    }
 
    // DateTime::createFromFormat() convierte el string a objeto DateTime
    // 'Y-m-d H:i:s' es el formato que viene de la base de datos
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
 
    // Si la conversión fue exitosa formatea a 'd/m/Y H:i', si no escapa el valor original
    return $dt ? $dt->format('d/m/Y H:i') : e($fecha);
}
 
// ── Función formatearHoras() ──────────────────────────────
// Convierte un número decimal de horas a formato legible "Xh YYmin"
function formatearHoras($horas)
{
    // empty() verifica si el valor es vacío, null, 0 o ''
    // Los últimos dos && excluyen el 0 y '0' para no mostrar '—' cuando son 0 horas
    if (empty($horas) && $horas !== 0 && $horas !== '0') {
        return '—';
    }
 
    // Convierte las horas decimales a minutos totales y redondea al entero más cercano
    // Ejemplo: 1.5 horas → 90 minutos
    $total = (int) round((float)$horas * 60);
 
    // intdiv() hace división entera para obtener las horas completas
    // Ejemplo: 90 / 60 = 1 hora
    $h = intdiv($total, 60);
 
    // % es el operador módulo, obtiene el resto de minutos
    // Ejemplo: 90 % 60 = 30 minutos
    $min = $total % 60;
 
    // str_pad() rellena con ceros a la izquierda para que los minutos siempre tengan 2 dígitos
    // Ejemplo: 5 → "05", 30 → "30"
    return $h . 'h ' . str_pad($min, 2, '0', STR_PAD_LEFT) . 'min';
}
 
// ── Función obtenerIdTipo() ───────────────────────────────
// Busca y retorna el id de un tipo de usuario por su nombre
// PDO $pdo recibe la conexión activa a la base de datos
// ?int puede retornar null si no encuentra el tipo
function obtenerIdTipo(PDO $pdo, string $nombre): ?int
{
    // Busca el id del tipo de usuario cuyo nombre coincida
    $stmt = $pdo->prepare('SELECT id_tipo FROM tipo_usuario WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
 
    // fetch() trae una sola fila; si no existe devuelve false
    $fila = $stmt->fetch();
 
    // Si encontró la fila retorna el id como entero, si no retorna null
    return $fila ? (int) $fila['id_tipo'] : null;
}
 
// ── Función obtenerAreas() ────────────────────────────────
// Retorna todas las áreas disponibles ordenadas alfabéticamente
function obtenerAreas(PDO $pdo): array
{
    // query() ejecuta la consulta sin parámetros variables
    $stmt = $pdo->query('SELECT id_area, nombre FROM area ORDER BY nombre ASC');
 
    // fetchAll() retorna todas las filas como un arreglo de arreglos
    return $stmt->fetchAll();
}
 
// ── Función loginAdmin() ──────────────────────────────────
// Verifica las credenciales del administrador y retorna sus datos si son correctas
// Retorna null si las credenciales son incorrectas o el usuario está inactivo
function loginAdmin(PDO $pdo, string $documento, string $pin, string $password): ?array
{
    // LEFT JOIN incluye usuarios aunque no tengan tipo asignado
    // Solo busca usuarios ACTIVO que sean Administrador o sin tipo definido
    $stmt = $pdo->prepare(
        "SELECT u.*
         FROM users u
         LEFT JOIN tipo_usuario t ON u.id_tipo = t.id_tipo
         WHERE u.documento = ?
           AND u.estado = 'ACTIVO'
           AND (t.nombre = 'Administrador' OR u.id_tipo IS NULL)
         LIMIT 1"
    );
    $stmt->execute([$documento]);
 
    // fetch() trae los datos del admin; false si no existe
    $admin = $stmt->fetch();
 
    if (
        // Si no encontró ningún usuario con ese documento
        !$admin
        // O el PIN no coincide (comparación directa en texto plano)
        || $admin['pin'] !== $pin
        // O la contraseña no coincide con el hash guardado en la BD
        // password_verify() compara el texto plano con el hash bcrypt
        || !password_verify($password, $admin['password'])
    ) {
        // Retorna null si cualquiera de las tres verificaciones falla
        return null;
    }
 
    // Si todo es correcto retorna el arreglo con los datos del admin
    return $admin;
}
 
// ── Función obtenerEmpleadoActivo() ──────────────────────
// Busca un empleado activo por documento y verifica su PIN
// Retorna sus datos si coincide, null si no existe o el PIN es incorrecto
function obtenerEmpleadoActivo(PDO $pdo, string $documento, string $pin): ?array
{
    // Primero obtiene el id del tipo Empleado para filtrar correctamente
    $idEmpleado = obtenerIdTipo($pdo, 'Empleado');
 
    // Si no existe el tipo Empleado en la BD no puede continuar
    if (!$idEmpleado) {
        return null;
    }
 
    // Busca el usuario que sea de tipo Empleado, con ese documento y estado ACTIVO
    // UPPER(estado) convierte el estado a mayúsculas antes de comparar
    $stmt = $pdo->prepare("
        SELECT documento, pin, nombre_completo, estado
        FROM users
        WHERE documento = ?
        AND id_tipo = ?
        AND UPPER(estado) = 'ACTIVO'
        LIMIT 1
    ");
 
    // trim() elimina espacios del documento antes de buscar
    $stmt->execute([
        trim($documento),
        $idEmpleado
    ]);
 
    // PDO::FETCH_ASSOC retorna la fila como arreglo asociativo (clave => valor)
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
 
    // Si no encontró ningún empleado con esos datos retorna null
    if (!$empleado) {
        return null;
    }
 
    // Verifica el PIN en texto plano (no está encriptado para empleados)
    if ($pin !== $empleado['pin']) {
        return null;
    }
 
    // Si el documento y PIN son correctos retorna los datos del empleado
    return $empleado;
}
 
// ── Función procesarAsistencia() ─────────────────────────
// Registra la entrada o salida de un empleado según el estado actual
// Retorna un arreglo con 'exito' (bool) y 'mensaje' (string)
function procesarAsistencia(PDO $pdo, string $documento, string $pin): array
{
    // Valida el formato del documento y PIN antes de consultar la BD
    if (!validarDocumento($documento) || !validarPin($pin)) {
        return [
            'exito'   => false,
            'mensaje' => 'Datos inválidos. Verifique documento y PIN.'
        ];
    }
 
    // Busca el empleado activo y verifica su PIN
    $empleado = obtenerEmpleadoActivo($pdo, $documento, $pin);
 
    // Si retorna null, el documento o PIN son incorrectos
    if ($empleado === null) {
        return [
            'exito'   => false,
            'mensaje' => 'Documento o PIN incorrectos.'
        ];
    }
 
    // Busca si ya existe una asistencia abierta hoy (entrada sin salida)
    // DATE(fecha_entrada)=CURDATE() filtra solo registros de hoy
    // fecha_salida IS NULL significa que aún no ha salido
    $stmt = $pdo->prepare(
        "SELECT id_asistencia
         FROM asistencias
         WHERE documento = ?
         AND DATE(fecha_entrada)=CURDATE()
         AND fecha_salida IS NULL
         LIMIT 1"
    );
 
    $stmt->execute([$documento]);
 
    // fetch() retorna la asistencia abierta o false si no existe
    $abierta = $stmt->fetch();
 
    // Si no hay asistencia abierta, registra la ENTRADA
    if (!$abierta) {

    // Cierra automáticamente cualquier registro pendiente de días anteriores
    $cerrarPendientes = $pdo->prepare(
        "UPDATE asistencias
         SET fecha_salida = DATE_ADD(fecha_entrada, INTERVAL 8 HOUR),
             horas_trabajadas = 8.00
         WHERE documento = ?
         AND DATE(fecha_entrada) < CURDATE()
         AND fecha_salida IS NULL"
    );
    $cerrarPendientes->execute([$documento]);

    // Registra la nueva entrada
    $insert = $pdo->prepare(
        "INSERT INTO asistencias
        (documento, fecha_entrada)
        VALUES (?, NOW())"
    );
    $insert->execute([$documento]);

    return [
        'exito'   => true,
        'mensaje' => 'Entrada registrada correctamente.'
    ];
}
 
    // Si ya hay asistencia abierta, registra la SALIDA
    // TIMESTAMPDIFF(SECOND,...) calcula la diferencia en segundos entre entrada y salida
    // Dividir entre 3600 convierte los segundos a horas
    // ROUND(..., 2) redondea a 2 decimales
    $update = $pdo->prepare(
        "UPDATE asistencias
         SET fecha_salida = NOW(),
             horas_trabajadas =
             ROUND(TIMESTAMPDIFF(SECOND, fecha_entrada, NOW()) / 3600, 2)
         WHERE id_asistencia = ?"
    );
 
    // Usa el id de la asistencia abierta encontrada anteriormente
    $update->execute([
        $abierta['id_asistencia']
    ]);
 
    return [
        'exito'   => true,
        'mensaje' => 'Salida registrada correctamente.'
    ];
}