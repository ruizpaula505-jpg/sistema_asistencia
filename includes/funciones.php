<?php

function obtenerTodosLosEmpleados(PDO $pdo): array {

    $sql = "SELECT u.*,
                   a.nombre AS area_nombre,
                   t.nombre AS tipo_nombre
            FROM users u
            LEFT JOIN area a ON u.id_area = a.id_area
            LEFT JOIN tipo_usuario t ON u.id_tipo = t.id_tipo
            ORDER BY u.nombre_completo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerEmpleadoPorDocumento(PDO $pdo, int $documento){

    $sql = "SELECT * FROM users WHERE documento = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$documento]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function insertarEmpleado(
    PDO $pdo,
    int $documento,
    string $pin,
    string $password,
    string $nombre,
    ?int $id_tipo,
    ?int $id_area,
    string $estado = 'ACTIVO'
): bool {

    $sql = "INSERT INTO users
            (
                documento,
                pin,
                password,
                nombre_completo,
                id_tipo,
                id_area,
                estado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    try {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt->execute([
            $documento,
            $pin,
            $hash,
            $nombre,
            $id_tipo,
            $id_area,
            $estado
        ]);

        return true;

    } catch(PDOException $e){

        error_log($e->getMessage());
        return false;
    }
}

function actualizarEmpleado(
    PDO $pdo,
    int $documento,
    string $nombre,
    ?int $id_tipo,
    ?int $id_area,
    string $estado
): bool {

    $sql = "UPDATE users
            SET nombre_completo = ?,
                id_tipo = ?,
                id_area = ?,
                estado = ?
            WHERE documento = ?";

    $stmt = $pdo->prepare($sql);

    try {

        $stmt->execute([
            $nombre,
            $id_tipo,
            $id_area,
            $estado,
            $documento
        ]);

        return $stmt->rowCount() > 0;

    } catch(PDOException $e){

        error_log($e->getMessage());
        return false;
    }
}

function eliminarEmpleado(PDO $pdo, int $documento): bool {

    $sql = "DELETE FROM users WHERE documento = ?";

    $stmt = $pdo->prepare($sql);

    try {

        $stmt->execute([$documento]);

        return $stmt->rowCount() > 0;

    } catch(PDOException $e){

        error_log($e->getMessage());
        return false;
    }
}

function obtenerTodasLasAreas(PDO $pdo): array {

    $sql = "SELECT * FROM area ORDER BY nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerTodosLosTipos(PDO $pdo): array {

    $sql = "SELECT * FROM tipo_usuario ORDER BY nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loginAdmin(
    PDO $pdo,
    int $documento,
    string $pin,
    string $password
){

    $sql = "SELECT *
            FROM users
            WHERE documento = ?
            AND estado = 'ACTIVO'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$documento]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$usuario){
        return false;
    }

    if($usuario['pin'] !== $pin){
        return false;
    }

    if(!password_verify($password, $usuario['password'])){
        return false;
    }

    return $usuario;
}