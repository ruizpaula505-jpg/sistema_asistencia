<?php
/* =========================================================
 *  LÓGICA PHP — index.php
 * ========================================================= */

session_start();

require_once 'config/db.php';
require_once 'includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $documento = trim($_POST['documento'] ?? '');
    $pin       = trim($_POST['pin']       ?? '');

    $db  = new Database();
    $pdo = $db->conectar();

    $resultado = procesarAsistencia($pdo, $documento, $pin);

    $_SESSION['mensaje']        = $resultado['mensaje'];
    $_SESSION['tipo_mensaje']   = $resultado['exito'] ? 'exito' : 'error';
    $_SESSION['last_documento'] = $documento;

    header('Location: index.php');
    exit;
}

$mensaje      = $_SESSION['mensaje']        ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje']   ?? null;
$lastDoc      = $_SESSION['last_documento'] ?? null;
$anio         = date('Y');

unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['last_documento']);

$tituloPagina = 'Control de Asistencia — Empleados';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --sa-primary:      #2563eb;
        --sa-primary-dark: #1d4ed8;
        --sa-glass:        rgba(255,255,255,0.08);
        --sa-glass-border: rgba(255,255,255,0.15);
    }
    body {
        background: linear-gradient(160deg, #1e3a5f 0%, #2563eb 55%, #3b82f6 100%);
        min-height: 100vh;
    }
    .card { border: none; border-radius: 1.25rem; }
    .btn-primary { background: var(--sa-primary); border-color: var(--sa-primary); }
    .btn-primary:hover { background: var(--sa-primary-dark); border-color: var(--sa-primary-dark); }
    .form-control:read-only { background-color: #e9ecef; cursor: not-allowed; }
    .info-panel {
        background: var(--sa-glass);
        border: 1px solid var(--sa-glass-border);
        border-radius: 1.25rem;
        padding: 1.25rem;
        height: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .info-panel h2 { font-size: 1.2rem; font-weight: 700; color: #ffffff; margin: 0; line-height: 1.3; }
    .info-desc { font-size: 0.9rem; color: rgba(255,255,255,0.75); line-height: 1.65; margin: 0; }
    .info-divider { border: none; border-top: 1px solid var(--sa-glass-border); margin: 0; }
    .feature-item { display: flex; align-items: flex-start; gap: 1rem; }
    .feature-icon {
        width: 32px; height: 32px; min-width: 32px; border-radius: 8px;
        background: rgba(255,255,255,0.12); display: flex; align-items: center;
        justify-content: center; font-size: 1rem; color: #93c5fd;
    }
    .feature-title { font-size: 0.9rem; font-weight: 600; color: #ffffff; margin: 0 0 0.2rem; }
    .feature-text { font-size: 0.82rem; color: rgba(255,255,255,0.65); margin: 0; line-height: 1.5; }
    .tech-badge {
        font-size: 0.73rem; font-weight: 600; padding: 0.25rem 0.6rem;
        border-radius: 20px; background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.18);
    }
    #reloj { font-variant-numeric: tabular-nums; }
    @media (max-width: 991.98px) { .info-panel { height: auto; margin-top: 0; } }
</style>

<nav class="navbar navbar-dark px-4 py-3" style="background: rgba(0,0,0,.2);">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="#">
        <i class="bi bi-clock-history fs-5"></i>
        AsistenciaApp
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white opacity-75 small d-flex align-items-center gap-1">
            <i class="bi bi-calendar-event"></i>
            <span id="reloj"><?= date('d/m/Y H:i:s') ?></span>
        </span>
        <a href="admin/login.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock"></i>
            <span class="d-none d-md-inline">Iniciar sesión como administrador</span>
            <span class="d-md-none">Admin</span>
        </a>
    </div>
</nav>

<main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <div class="container-xl px-4">
        <div class="row g-4 align-items-start justify-content-center">

            <div class="col-12 col-lg-5">
                <div class="card shadow-lg">
                    <div class="text-white text-center py-4 px-4 rounded-top"
                         style="background: linear-gradient(135deg, #1e3a5f, #2563eb);">
                        <i class="bi bi-person-badge fs-1 mb-2 d-block"></i>
                        <h1 class="h4 fw-bold mb-1">Registro de Asistencia</h1>
                        <p class="mb-0 opacity-75 small">
                            Ingresa tu documento y PIN para registrar tu entrada o salida.
                        </p>
                    </div>

                    <div class="card-body p-4">
                        <?php if ($mensaje): ?>
                            <?php
                            $bsClase = match($tipo_mensaje) {
                                'exito' => 'success',
                                'error' => 'danger',
                                default => 'info',
                            };
                            $icono = match($tipo_mensaje) {
                                'exito' => 'bi-check-circle-fill',
                                'error' => 'bi-x-circle-fill',
                                default => 'bi-info-circle-fill',
                            };
                            ?>
                            <div class="alert alert-<?= $bsClase ?> alert-dismissible d-flex align-items-center gap-2 fade show" role="alert">
                                <i class="bi <?= $icono ?>"></i>
                                <span><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="index.php" novalidate>
                            <div class="mb-3">
                                <label for="documento" class="form-label fw-medium">
                                    <i class="bi bi-person-vcard me-1 text-primary"></i>
                                    Documento de identidad
                                </label>
                                <input type="text" class="form-control" id="documento" name="documento"
                                    placeholder="Ej: 1234567890" maxlength="15" autocomplete="off"
                                    value="<?= htmlspecialchars($lastDoc ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="form-text">Cédula o documento asignado por la empresa.</div>
                            </div>

                            <div class="mb-4">
                                <label for="pin" class="form-label fw-medium">
                                    <i class="bi bi-key me-1 text-primary"></i>
                                    PIN de acceso
                                </label>
                                <input type="password" class="form-control" id="pin" name="pin"
                                    placeholder="••••" maxlength="4" minlength="4"
                                    inputmode="numeric" autocomplete="off">
                                <div class="form-text">4 dígitos numéricos. Asignado por el administrador.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                                    Registrar Asistencia
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="info-panel">
                    <div>
                        <span class="badge mb-2"
                              style="background:rgba(255,255,255,0.15);color:#bfdbfe;font-size:0.72rem;letter-spacing:0.06em;font-weight:600;">
                            SISTEMA ACTIVO
                        </span>
                        <h2>Sistema de Control de Asistencia</h2>
                        <p class="info-desc mt-2">
                            Plataforma web que registra automáticamente la entrada y salida
                            de empleados usando la hora exacta del servidor.
                        </p>
                    </div>

                    <hr class="info-divider">

                    <div>
                        <p class="info-desc mb-3" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.07em;opacity:0.6;">
                            ¿Qué hace este sistema?
                        </p>

                        <div class="feature-item mb-3">
                            <div class="feature-icon"><i class="bi bi-clock-fill"></i></div>
                            <div>
                                <p class="feature-title">Hora automática del servidor</p>
                                <p class="feature-text">
                                    Captura el timestamp exacto del servidor (<code style="color:#93c5fd;">NOW()</code> en MySQL).
                                </p>
                            </div>
                        </div>

                        <div class="feature-item mb-3">
                            <div class="feature-icon"><i class="bi bi-arrow-left-right"></i></div>
                            <div>
                                <p class="feature-title">Entrada y salida inteligente</p>
                                <p class="feature-text">
                                    Detecta automáticamente si es entrada o salida según el estado del día.
                                </p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-shield-lock-fill"></i></div>
                            <div>
                                <p class="feature-title">Seguridad de credenciales</p>
                                <p class="feature-text">
                                    PINs protegidos con (<code style="color:#93c5fd;">password_hash</code>).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<footer class="text-center text-white py-3 opacity-50 small">
    Sistema de Control de Asistencia &mdash; <?= $anio ?>
</footer>

<script>
    document.getElementById('pin').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
    setInterval(() => {
        const ahora = new Date();
        const fecha = ahora.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const hora  = ahora.toLocaleTimeString('es-CO');
        document.getElementById('reloj').textContent = fecha + ' ' + hora;
    }, 1000);
    document.getElementById('documento').focus();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>