<?php
/* =========================================================
 *  admin/dashboard.php
 *  Orden: funciones → auth → db → lógica → header → HTML → footer
 * ========================================================= */

require_once __DIR__ . '/../includes/funciones.php';  // Funciones auxiliares (e(), obtenerIdTipo, etc.)
require_once __DIR__ . '/../includes/auth_admin.php'; // Verifica sesión activa; redirige al login si no hay
require_once __DIR__ . '/../config/db.php';           // Clase Database para la conexión PDO

$db  = new Database();
$pdo = $db->conectar(); // Establece la conexión con la base de datos

$idEmpleado     = obtenerIdTipo($pdo, 'Empleado'); // Obtiene el ID del tipo 'Empleado'
$totalEmpleados = 0;
$asistenciasHoy = 0;

if ($idEmpleado !== null) {
    // Cuenta cuántos usuarios tienen el tipo Empleado
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE id_tipo = ?');
    $stmt->execute([$idEmpleado]);
    $totalEmpleados = (int) $stmt->fetch()['total'];
}

// Cuenta los registros de asistencia del día actual
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM asistencias WHERE DATE(fecha_entrada) = CURDATE()');
$asistenciasHoy = (int) $stmt->fetch()['total'];

$nombreAdmin  = $_SESSION['admin_nombre'] ?? $_SESSION['admin_id']; // Nombre del admin para mostrar en pantalla
$tituloPagina = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">

    <nav class="navbar navbar-light bg-white shadow-sm mb-4 px-4">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-clock-history me-2 text-primary"></i>AsistenciaApp
        </span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </nav>

    <div class="mb-4">
        <h1 class="h3 fw-bold">Bienvenido, <?= e($nombreAdmin) ?></h1>
        <p class="text-muted mb-0">Panel de gestión de empleados y asistencias.</p>
    </div>

    <!-- Tarjetas de estadísticas -->
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

    <!-- Accesos rápidos -->
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <i class="bi bi-person-plus text-primary fs-3 mb-2"></i>
                    <h2 class="h5">Empleados</h2>
                    <p class="text-muted flex-grow-1">Crear, consultar y editar empleados. Cambio de PIN con validación.</p>
                    <a href="empleados_crud.php" class="btn btn-primary">Gestionar empleados</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <i class="bi bi-bar-chart-line text-success fs-3 mb-2"></i>
                    <h2 class="h5">Reportes</h2>
                    <p class="text-muted flex-grow-1">Consultar asistencias por rango de fechas y exportar CSV.</p>
                    <a href="reportes.php" class="btn btn-primary">Ver reportes</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>