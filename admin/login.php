<?php

session_start();

require_once '../config/database.php';
require_once '../includes/empleados_model.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $documento = (int) $_POST['documento'];
    $pin = trim($_POST['pin']);
    $password = $_POST['password'];

    $usuario = loginAdmin(
        $pdo,
        $documento,
        $pin,
        $password
    );

    if ($usuario) {

        $_SESSION['admin_id'] = $usuario['documento'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];

        header('Location: dashboard.php');
        exit;

    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Login Administrador</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f6fa;
}

.login-box{
    margin-top:100px;
}

</style>

</head>

<body>

<div class="container login-box">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header bg-primary text-white text-center">

                    <h3>Administrador</h3>

                </div>

                <div class="card-body">

                    <?php if($error): ?>

                        <div class="alert alert-danger">

                            <?= $error ?>

                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label>Documento</label>

                            <input
                                type="number"
                                name="documento"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>PIN</label>

                            <input
                                type="password"
                                name="pin"
                                maxlength="4"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Contraseña</label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Ingresar

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>