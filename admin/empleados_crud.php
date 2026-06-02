<?php

require_once '../includes/auth_admin.php';
require_once '../config/database.php';
require_once '../includes/empleados_model.php';

$empleados = obtenerTodosLosEmpleados($pdo);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Gestión de Empleados</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a href="dashboard.php" class="navbar-brand">
            Sistema Asistencia
        </a>

        <a href="../logout.php" class="btn btn-danger">
            Salir
        </a>

    </div>

</nav>

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">

        <h2>Gestión de Empleados</h2>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#crearEmpleado">

            Nuevo Empleado

        </button>

    </div>

    <div class="card shadow">

        <div class="card-body">

            <table class="table table-striped table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th>Tipo</th>
                        <th>Estado</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach($empleados as $empleado): ?>

                    <tr>

                        <td><?= $empleado['documento']; ?></td>

                        <td><?= $empleado['nombre_completo']; ?></td>

                        <td><?= $empleado['area_nombre']; ?></td>

                        <td><?= $empleado['tipo_nombre']; ?></td>

                        <td>

                            <?php if($empleado['estado'] == 'ACTIVO'): ?>

                                <span class="badge bg-success">

                                    ACTIVO

                                </span>

                            <?php else: ?>

                                <span class="badge bg-danger">

                                    INACTIVO

                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- MODAL -->

<div
class="modal fade"
id="crearEmpleado"
tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>Nuevo Empleado</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<form method="POST">

<div class="modal-body">

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
type="text"
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

<div class="mb-3">

<label>Nombre Completo</label>

<input
type="text"
name="nombre"
class="form-control"
required>

</div>

<div class="mb-3">

<label>ID Tipo Usuario</label>

<input
type="number"
name="id_tipo"
class="form-control">

</div>

<div class="mb-3">

<label>ID Área</label>

<input
type="number"
name="id_area"
class="form-control">

</div>

<div class="mb-3">

<label>Estado</label>

<select
name="estado"
class="form-select">

<option value="ACTIVO">
ACTIVO
</option>

<option value="INACTIVO">
INACTIVO
</option>

</select>

</div>

</div>

<div class="modal-footer">

<button
type="submit"
name="guardar"
class="btn btn-success">

Guardar

</button>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php

if(isset($_POST['guardar'])){

    insertarEmpleado(
        $pdo,
        $_POST['documento'],
        $_POST['pin'],
        $_POST['password'],
        $_POST['nombre'],
        $_POST['id_tipo'],
        $_POST['id_area'],
        $_POST['estado']
    );

    echo "<script>
            location='empleados_crud.php';
          </script>";
}
?>