<?php
/* =========================================================
 *  LÓGICA PHP — reportes.php
 * ========================================================= */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Verificación de seguridad
require_once __DIR__ . '/../includes/auth_admin.php';

// 2. Configuración y funciones
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/funciones.php';
 
$db  = new Database();
$pdo = $db->conectar();

// 3. Lógica de filtros y consultas
$idTipoEmpleado  = obtenerIdTipo($pdo, 'Empleado');
$fechaDesde      = trim($_GET['fecha_desde']  ?? date('Y-m-01'));
$fechaHasta      = trim($_GET['fecha_hasta']  ?? date('Y-m-d'));
$documentoFiltro = trim($_GET['documento']    ?? '');
$exportar        = isset($_GET['exportar']) && $_GET['exportar'] === 'csv';

$registros  = [];
$totalHoras = 0.0;
$error      = '';

if ($idTipoEmpleado === null) {
    $error = 'No existe el tipo de usuario Empleado.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
    $error = 'Formato de fecha inválido.';
} elseif ($fechaDesde > $fechaHasta) {
    $error = 'La fecha inicial es mayor a la final.';
} else {
    $sql = 'SELECT ast.*, u.nombre_completo, ar.nombre AS nombre_area 
            FROM asistencias ast
            INNER JOIN users u ON ast.documento = u.documento
            INNER JOIN area ar ON u.id_area = ar.id_area
            WHERE u.id_tipo = ?
              AND DATE(ast.fecha_entrada) BETWEEN ? AND ?';

    $params = [$idTipoEmpleado, $fechaDesde, $fechaHasta];

    if ($documentoFiltro !== '') {
        $sql .= ' AND u.documento = ?';
        $params[] = $documentoFiltro;
    }

    $sql .= ' ORDER BY ast.fecha_entrada DESC';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();
        foreach ($registros as $reg) {
            if ($reg['horas_trabajadas'] !== null) {
                $totalHoras += (float) $reg['horas_trabajadas'];
            }
        }
    } catch (PDOException $e) {
        $error = "Error en la consulta: " . $e->getMessage();
    }

    if ($exportar && empty($error)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_asistencia.csv"');
        $salida = fopen('php://output', 'w');
        fputcsv($salida, ['Documento', 'Nombre', 'Area', 'Entrada', 'Salida', 'Horas']);
        foreach ($registros as $reg) {
            fputcsv($salida, [$reg['documento'], $reg['nombre_completo'], $reg['nombre_area'], $reg['fecha_entrada'], $reg['fecha_salida'] ?? '', $reg['horas_trabajadas'] ?? '']);
        }
        fclose($salida);
        exit;
    }
}

// 4. CARGA VISUAL (Header y Navbar)
$tituloPagina = 'Reportes de Asistencia';
require_once __DIR__ . '/../includes/header.php'; 
?>

<style>
    /* Asegura que el footer se quede abajo */
    body { display: flex; flex-direction: column; min-height: 100vh; }
    main { flex: 1; }
</style>

<main class="py-5">
    <div class="container pb-5">

        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">Reportes de asistencia</h1>
            <p class="text-muted mb-0">Consulta por rango de fechas y exporta los resultados.</p>
        </div>

        <?php if ($error !== ''): ?>
            <?= mensajeAlerta('danger', $error) ?>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" action="reportes.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label fw-medium">Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?= e($fechaDesde) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label fw-medium">Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?= e($fechaHasta) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="documento" class="form-label fw-medium">Documento (opcional)</label>
                        <input type="text" class="form-control" id="documento" name="documento" value="<?= e($documentoFiltro) ?>" placeholder="Ej: 12345678">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-1"></i>Consultar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <p class="text-muted mb-0 small">Total de registros</p>
                        <p class="h3 mb-0 fw-bold"><?= e((string)count($registros)) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <p class="text-muted mb-0 small">Total horas trabajadas</p>
                        <p class="h3 mb-0 fw-bold text-success"><?= formatearHoras($totalHoras) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Área</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay registros para el filtro seleccionado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($registros as $reg): ?>
                                    <tr>
                                        <td><?= e($reg['documento']) ?></td>
                                        <td><?= e($reg['nombre_completo']) ?></td>
                                        <td><?= e($reg['nombre_area']) ?></td>
                                        <td><?= formatearFechaHora($reg['fecha_entrada']) ?></td>
                                        <td>
                                            <?php if ($reg['fecha_salida']): ?>
                                                <?= formatearFechaHora($reg['fecha_salida']) ?>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatearHoras($reg['horas_trabajadas']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
