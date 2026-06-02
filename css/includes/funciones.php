<?php
// includes/funciones.php

/**
 * Valida el formato del documento (solo números, longitud máxima permitida)
 */
function validarDocumento(string $documento): bool {
    // Documento numérico, entre 6 y 20 dígitos (ajustar según necesidad)
    return preg_match('/^\d{6,20}$/', $documento) === 1;
}

/**
 * Valida el formato del PIN (exactamente 4 dígitos numéricos)
 */
function validarPin(string $pin): bool {
    return preg_match('/^\d{4}$/', $pin) === 1;
}

/**
 * Verifica que el empleado existe y está activo
 * Retorna array de datos usuario o false si no existe o inactivo
 */
function obtenerEmpleadoActivo(PDO $pdo, string $documento, string $pin) {
    $sql = "SELECT * FROM users WHERE documento = ? AND pin = ? AND estado = 'ACTIVO' LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$documento, $pin]);
    $usuario = $stmt->fetch();
    return $usuario ?: false;
}

/**
 * Verifica si existe registro de asistencia abierto (sin salida hoy)
 * Retorna asistencia o false
 */
function obtenerAsistenciaAbierta(PDO $pdo, string $documento) {
    $sql = "SELECT * FROM asistencias WHERE documento = ? AND fecha_salida IS NULL AND DATE(fecha_entrada) = CURDATE() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$documento]);
    $asistencia = $stmt->fetch();
    return $asistencia ?: false;
}

/**
 * Registra la hora de entrada en la tabla asistencias
 */
function registrarEntrada(PDO $pdo, string $documento): bool {
    $sql = "INSERT INTO asistencias (documento, fecha_entrada) VALUES (?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$documento]);
}

/**
 * Registra la hora de salida y calcula horas trabajadas
 */
function registrarSalida(PDO $pdo, array $asistencia): bool {
    $fechaEntrada = new DateTime($asistencia['fecha_entrada']);
    $fechaSalida = new DateTime(); // hora actual
    $intervalo = $fechaEntrada->diff($fechaSalida);

    // Calcular horas trabajadas con decimales
    $horas = $intervalo->h + ($intervalo->i / 60) + ($intervalo->s / 3600);
    $horas = round($horas, 2);

    $sql = "UPDATE asistencias SET fecha_salida = NOW(), horas_trabajadas = ? WHERE id_asistencia = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$horas, $asistencia['id_asistencia']]);
}

/**
 * Función principal para procesar registro de asistencia
 * Retorna array con 'exito' => bool y 'mensaje' => string
 */
function procesarAsistencia(PDO $pdo, string $documento, string $pin): array {
    // Validar formato
    if (!validarDocumento($documento)) {
        return ['exito' => false, 'mensaje' => 'Documento inválido.'];
    }
    if (!validarPin($pin)) {
        return ['exito' => false, 'mensaje' => 'PIN inválido.'];
    }

    // Validar usuario activo
    $usuario = obtenerEmpleadoActivo($pdo, $documento, $pin);
    if (!$usuario) {
        return ['exito' => false, 'mensaje' => 'Documento o PIN incorrectos, o usuario inactivo.'];
    }

    // Verificar asistencia abierta para hoy
    $asistencia = obtenerAsistenciaAbierta($pdo, $documento);
    if ($asistencia) {
        // Registrar salida
        $exito = registrarSalida($pdo, $asistencia);
        if ($exito) {
            return ['exito' => true, 'mensaje' => 'Salida registrada con éxito.'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar salida.'];
        }
    } else {
        // Registrar entrada
        $exito = registrarEntrada($pdo, $documento);
        if ($exito) {
            return ['exito' => true, 'mensaje' => 'Entrada registrada con éxito.'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar entrada.'];
        }
    }
}
?>