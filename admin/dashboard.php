<?php

require_once '../includes/auth_admin.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <span class="navbar-brand">
            Sistema de Asistencia
        </span>

        <a href="../logout.php" class="btn btn-danger">
            Cerrar Sesión
        </a>

    </div>

</nav>

<div class="container mt-5">

    <h2>

        Bienvenido

        <?= $_SESSION['nombre']; ?>

    </h2>

    <div class="row mt-4">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-body text-center">

                    <h4>Empleados</h4>

                    <a
                        href="empleados_crud.php"
                        class="btn btn-success">

                        Gestionar

                    </a>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-body text-center">

                    <h4>Reportes</h4>

                    <a
                        href="reportes.php"
                        class="btn btn-warning">

                        Reportes

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>