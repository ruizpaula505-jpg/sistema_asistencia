<?php
/* =========================================================
 *  admin/dashboard.php
 * =========================================================
 *  ORDEN CORRECTO DE INCLUDES:
 *  1. funciones.php  → define e() y demás funciones
 *  2. auth_admin.php → verifica sesión (ya puede usar e() si lo necesitara)
 *  3. db.php         → conexión BD
 *  4. lógica PHP     → consultas, variables
 *  5. header.php     → abre <html>, <head>, <body>, navbar
 *  6. contenido HTML
 *  7. footer.php     → cierra </body> </html>
 * ========================================================= */

// 1. Funciones PRIMERO — e() debe existir antes que header.php
require_once __DIR__ . '/../includes/funciones.php';

// 2. Verificar sesión (no llama a header.php adentro)
require_once __DIR__ . '/../includes/auth_admin.php';

// 3. Conexión BD
require_once __DIR__ . '/../config/db.php';

// 4. Lógica y consultas
$db  = new Database();
$pdo = $db->conectar();

$idEmpleado     = obtenerIdTipo($pdo, 'Empleado');
$totalEmpleados = 0;
$asistenciasHoy = 0;

if ($idEmpleado !== null) {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE id_tipo = ?');
    $stmt->execute([$idEmpleado]);
    $totalEmpleados = (int) $stmt->fetch()['total'];
}

$stmt = $pdo->query(
    'SELECT COUNT(*) AS total FROM asistencias WHERE DATE(fecha_entrada) = CURDATE()'
);
$asistenciasHoy = (int) $stmt->fetch()['total'];

$nombreAdmin  = $_SESSION['admin_nombre'] ?? $_SESSION['admin_id'];
$tituloPagina = 'Dashboard';

// 5. Header — ahora sí e() ya existe cuando header.php se ejecuta
require_once __DIR__ . '/../includes/header.php';
?>

<!-- 6. Contenido -->
<div class="container pb-5">

    <div class="mb-4">
        <h1 class="h3 fw-bold">Bienvenido, <?= e($nombreAdmin) ?></h1>
        <p class="text-muted mb-0">Panel de gestión de empleados y asistencias.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Empleados registrados</p>
                            <p class="display-6 mb-0 fw-bold"><?= e((string) $totalEmpleados) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-calendar-check text-success fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Asistencias hoy</p>
                            <p class="display-6 mb-0 fw-bold"><?= e((string) $asistenciasHoy) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <i class="bi bi-person-plus text-primary fs-3 mb-2"></i>
                    <h2 class="h5">Empleados</h2>
                    <p class="text-muted flex-grow-1">
                        Crear, consultar y editar empleados. Cambio de PIN con validación.
                    </p>
                    <a href="empleados_crud.php" class="btn btn-primary">Gestionar empleados</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <i class="bi bi-bar-chart-line text-success fs-3 mb-2"></i>
                    <h2 class="h5">Reportes</h2>
                    <p class="text-muted flex-grow-1">
                        Consultar asistencias por rango de fechas y exportar CSV.
                    </p>
                    <a href="reportes.php" class="btn btn-primary">Ver reportes</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// 7. Footer
require_once __DIR__ . '/../includes/footer.php';
?>