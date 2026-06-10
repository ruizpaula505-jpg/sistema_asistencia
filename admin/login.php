<?php
/* =========================================================
 *  LÓGICA PHP — login.php
 * ========================================================= */

// Inicia la sesión para poder leer y escribir variables de sesión
session_start();

// Si ya hay un admin autenticado, redirige directo a reportes
// isset() verifica que la variable de sesión exista
if (isset($_SESSION['admin_id'])) {
    header('Location: reportes.php');
    exit;
}

// Importa la clase Database para la conexión
require_once __DIR__ . '/../config/db.php';

// Importa funciones como validarDocumento(), validarPin(), loginAdmin(), etc.
require_once __DIR__ . '/../includes/funciones.php';

// Inicializa el mensaje y su tipo vacíos
$mensaje     = '';
$tipoMensaje = '';

// Solo procesa si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lee y limpia cada campo recibido del formulario
    // trim() elimina espacios al inicio y al final
    // ?? '' evita error si el campo no viene en el POST
    $documento = trim($_POST['documento'] ?? '');
    $pin       = trim($_POST['pin']       ?? '');
    $password  = trim($_POST['password']  ?? '');

    // Valida el formato de cada campo antes de consultar la BD
    if (!validarDocumento($documento) || !validarPin($pin) || !validarPasswordAdmin($password)) {
        $mensaje     = 'Credenciales inválidas.';
        $tipoMensaje = 'danger';
    } else {
        // Crea la conexión solo si las validaciones pasaron
        $db  = new Database();
        $pdo = $db->conectar();

        // Busca el admin en la BD y verifica documento, PIN y contraseña
        // Devuelve el array del admin si todo coincide, o null si falla
        $admin = loginAdmin($pdo, $documento, $pin, $password);

        if (!$admin) {
            // loginAdmin() devolvió null: credenciales incorrectas o usuario inactivo
            $mensaje     = 'Credenciales incorrectas o usuario inactivo.';
            $tipoMensaje = 'danger';
        } else {
            // session_regenerate_id(true) genera un nuevo ID de sesión
            // El 'true' elimina la sesión anterior para prevenir session fixation
            session_regenerate_id(true);

            // Guarda los datos del admin en sesión para usarlos en otras páginas
            $_SESSION['admin_id']     = $admin['documento'];
            $_SESSION['admin_nombre'] = $admin['nombre_completo'];

            // Redirige a reportes tras login exitoso
            header('Location: reportes.php');
            exit;
        }
    }
}

// Define el título que mostrará el <title>
// DEBE definirse ANTES del require_once de header.php
$tituloPagina = 'Login Administrador';

/* =========================================================
 *  VISTA HTML — login.php
 *  Usa header.php propio sin auth_admin.php para evitar
 *  loop infinito (el login no puede verificar sesión).
 * ========================================================= */

// Incluye el <head> con Bootstrap, admin.css y apertura del <body>
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">

                <!-- Cabecera de la tarjeta con degradado azul -->
                <div class="card-header-sa text-center">
                    <!-- fs-2: ícono grande, d-block: en su propia línea -->
                    <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>
                    <h1 class="h4 mb-0">Acceso Administrador</h1>
                </div>

                <div class="card-body p-4">

                    <!-- Muestra el mensaje de error si existe -->
                    <!-- mensajeAlerta() genera el HTML del alert de Bootstrap -->
                    <?php if ($mensaje !== ''): ?>
                        <?= mensajeAlerta($tipoMensaje, $mensaje) ?>
                    <?php endif; ?>

                    <!-- autocomplete="off" evita que el navegador autocomplete credenciales -->
                    <!-- novalidate desactiva la validación nativa del navegador -->
                    <form method="POST" action="login.php" autocomplete="off" novalidate>

                        <!-- Campo: Documento -->
                        <div class="mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <input
                                type="text"
                                class="form-control"
                                id="documento"
                                name="documento"
                                maxlength="20"
                                required
                                value="<?= e($_POST['documento'] ?? '') ?>"
                            >
                        </div>

                        <!-- Campo: PIN de 4 dígitos -->
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN (4 dígitos)</label>
                            <input
                                type="password"
                                class="form-control"
                                id="pin"
                                name="pin"
                                maxlength="4"
                                inputmode="numeric"
                            >
                        </div>

                        <!-- Campo: Contraseña alfanumérica -->
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                Contraseña (mínimo 8 caracteres alfanuméricos)
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                maxlength="20"
                                required
                            >
                        </div>

                        <!-- w-100: el botón ocupa todo el ancho del formulario -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>

                    </form>

                    <!-- Enlace para volver a la vista pública del empleado -->
                    <!-- ../index.php: sube un nivel desde admin/ hasta la raíz -->
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Volver al registro de empleados
                        </a>
                    </div>

                </div><!-- /card-body -->
            </div><!-- /card -->
        </div>
    </div>
</div>

<?php
// Incluye el cierre del </body> y </html> con los scripts de Bootstrap
require_once __DIR__ . '/../includes/footer.php';
?>
