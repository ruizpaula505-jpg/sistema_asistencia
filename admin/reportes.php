<?php

require_once '../includes/auth_admin.php';
require_once '../config/database.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin    = $_GET['fecha_fin'] ?? '';

$sql = "SELECT
            a.id_asistencia,
            u.documento,
            u.nombre_completo,
            a.fecha_entrada,
            a.fecha_salida,
            a.horas_trabajadas
        FROM asistencias a
        INNER JOIN users u
        ON a.documento = u.documento";

$params = [];

if(!empty($fechaInicio) && !empty($fechaFin)){

    $sql .= " WHERE DATE(a.fecha_entrada)
              BETWEEN ? AND ?";

    $params[] = $fechaInicio;
    $params[] = $fechaFin;
}

$sql .= " ORDER BY a.fecha_entrada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$reportes = $stmt->fetchAll();

$totalHoras = 0;

foreach($reportes as $fila){

    $totalHoras += (float)$fila['horas_trabajadas'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reporte de Asistencias</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
}

.titulo{
    font-weight:bold;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

<div class="container">

<a class="navbar-brand" href="dashboard.php">

Sistema Asistencia

</a>

<div>

<a href="dashboard.php" class="btn btn-outline-light me-2">

Dashboard

</a>

<a href="../logout.php" class="btn btn-danger">

Cerrar Sesión

</a>

</div>

</div>

</nav>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3 class="mb-0">

Reporte de Asistencias

</h3>

</div>

<div class="card-body">

<form method="GET" class="row g-3 mb-4">

<div class="col-md-4">

<label class="form-label">

Fecha Inicio

</label>

<input
type="date"
name="fecha_inicio"
value="<?= htmlspecialchars($fechaInicio) ?>"
class="form-control">

</div>

<div class="col-md-4">

<label class="form-label">

Fecha Fin

</label>

<input
type="date"
name="fecha_fin"
value="<?= htmlspecialchars($fechaFin) ?>"
class="form-control">

</div>

<div class="col-md-4 d-flex align-items-end">

<button
type="submit"
class="btn btn-primary me-2">

Filtrar

</button>

<a
href="reportes.php"
class="btn btn-secondary">

Limpiar

</a>

</div>

</form>

<div class="row mb-4">

<div class="col-md-4">

<div class="card border-success">

<div class="card-body text-center">

<h5>Total Horas</h5>

<h2 class="text-success">

<?= number_format($totalHoras,2) ?>

</h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-primary">

<div class="card-body text-center">

<h5>Registros</h5>

<h2 class="text-primary">

<?= count($reportes) ?>

</h2>

</div>

</div>

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Documento</th>
<th>Empleado</th>
<th>Entrada</th>
<th>Salida</th>
<th>Horas Trabajadas</th>

</tr>

</thead>

<tbody>

<?php if(count($reportes) > 0): ?>

<?php foreach($reportes as $fila): ?>

<tr>

<td><?= $fila['id_asistencia'] ?></td>

<td><?= $fila['documento'] ?></td>

<td><?= htmlspecialchars($fila['nombre_completo']) ?></td>

<td><?= $fila['fecha_entrada'] ?></td>

<td>

<?= $fila['fecha_salida']
    ? $fila['fecha_salida']
    : '<span class="badge bg-warning">Pendiente</span>' ?>

</td>

<td>

<?= $fila['horas_trabajadas']
    ? $fila['horas_trabajadas'].' h'
    : '0 h' ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="6" class="text-center">

No existen registros para mostrar

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</body>

</html>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<title>Reportes</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

    <h2>Reporte de Asistencias</h2>

    <hr>

    <form method="GET" class="row mb-4">

<div class="col-md-4">

<label>Fecha Inicio</label>

<input
type="date"
name="fecha_inicio"
class="form-control">

</div>

<div class="col-md-4">

<label>Fecha Fin</label>

<input
type="date"
name="fecha_fin"
class="form-control">

</div>

<div class="col-md-4 d-flex align-items-end">

<button
class="btn btn-primary">

Filtrar

</button>

</div>

</form>

    <hr>

    <table class="table table-striped">

        <thead class="table-dark">

            <tr>
                <th>Empleado</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Horas</th>
            </tr>

        </thead>

        <tbody>

            <tr>
                <td>Juan Pérez</td>
                <td>07:00</td>
                <td>17:00</td>
                <td>10</td>
            </tr>

        </tbody>

    </table>

</div>

</body>
</html>