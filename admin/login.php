<?php
/* =========================================================
 *  admin/login.php — Autenticación del administrador
 * ========================================================= */

session_start(); // Inicia o reanuda la sesión PHP

// Si ya hay sesión activa redirige al dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/funciones.php'; // Funciones de validación y autenticación
require_once __DIR__ . '/../config/db.php';           // Clase Database para la conexión PDO

$mensaje     = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Solo procesa si el formulario fue enviado
    $documento = trim($_POST['documento'] ?? ''); // Número de documento del admin
    $pin       = trim($_POST['pin']       ?? ''); // PIN de 4 dígitos
    $password  = trim($_POST['password']  ?? ''); // Contraseña larga

    // Valida formato de cada campo antes de consultar la BD
    if (!validarDocumento($documento) || !validarPin($pin) || !validarPasswordAdmin($password)) {
        $mensaje     = 'Credenciales inválidas.';
        $tipoMensaje = 'danger';
    } else {
        $db    = new Database();
        $pdo   = $db->conectar();                          // Obtiene la conexión PDO
        $admin = loginAdmin($pdo, $documento, $pin, $password); // Busca al admin en la BD

        if (!$admin) {
            $mensaje     = 'Credenciales incorrectas o usuario inactivo.';
            $tipoMensaje = 'danger';
        } else {
            session_regenerate_id(true);                      // Previene fijación de sesión
            $_SESSION['admin_id']     = $admin['documento'];  // Guarda identificador en sesión
            $_SESSION['admin_nombre'] = $admin['nombre_completo']; // Guarda nombre para mostrar
            header('Location: dashboard.php');
            exit;
        }
    }
}

$tituloPagina = 'Login Administrador'; // Variable que usa header.php para el <title>
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">

                <div class="card-header-sa text-center">
                    <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>
                    <h1 class="h4 mb-0">Acceso Administrador</h1>
                </div>

                <div class="card-body p-4">

                    <?php if ($mensaje !== ''): ?>
                        <?= mensajeAlerta($tipoMensaje, $mensaje) ?> <!-- Muestra alerta Bootstrap si hubo error -->
                    <?php endif; ?>

                    <form method="POST" action="login.php" autocomplete="off" novalidate>
                    <!-- novalidate: desactiva validación nativa del navegador; la hace PHP -->

                        <div class="mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="number" class="form-control" id="documento" name="documento"
                                        placeholder="Ej: 1234567890" min="0" autocomplete="off"
                                        value="<?= e($lastDoc ?? '') ?>"> <!-- e() escapa para evitar XSS -->
                        </div>

                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN (4 dígitos)</label>
                            <input type="password" class="form-control" id="pin" name="pin"
                                maxlength="4" inputmode="numeric"> <!-- inputmode: teclado numérico en móvil -->
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña (mínimo 8 caracteres)</label>
                            <input type="password" class="form-control" id="password" name="password"
                                maxlength="20" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>

                    </form>

                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Volver al registro de empleados
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>