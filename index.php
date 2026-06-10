
<?php
/* =========================================================
 *  LÓGICA PHP — index.php
 *  Solo el bloque de procesamiento POST y lectura de sesión.
 *  La lógica de BD va en includes/funciones.php (próximo paso).
 * ========================================================= */

// Inicia la sesión para poder leer y escribir variables de sesión
session_start();

// Importa la clase Database para la conexión a la BD
require_once 'config/db.php';

// Importa funciones como procesarAsistencia(), etc.
require_once 'includes/funciones.php';

// Solo entra aquí si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lee y limpia el documento y el PIN enviados por el formulario
    // trim() elimina espacios al inicio y al final
    // ?? '' evita error si el campo no viene en el POST
    $documento = trim($_POST['documento'] ?? '');
    $pin       = trim($_POST['pin']       ?? '');

    // Crea la instancia de conexión a la base de datos
    $db  = new Database();

    // Obtiene el objeto PDO listo para hacer consultas
    $pdo = $db->conectar();

    // Llama a la función que decide si registrar entrada o salida
    // Retorna un arreglo con 'exito' (bool) y 'mensaje' (string)
    $resultado = procesarAsistencia($pdo, $documento, $pin);

    // Guarda el mensaje en sesión para mostrarlo después del redirect
    $_SESSION['mensaje']        = $resultado['mensaje'];

    // Si exito es true guarda 'exito', si no guarda 'error'
    $_SESSION['tipo_mensaje']   = $resultado['exito'] ? 'exito' : 'error';

    // Guarda el documento para precargarlo en el campo después del redirect
    $_SESSION['last_documento'] = $documento;

    // Redirige al mismo index.php (patrón PRG: evita reenvío al recargar)
    header('Location: index.php');
    exit;
}

// Lee los datos de sesión guardados antes del redirect
// ?? null devuelve null si la variable de sesión no existe
$mensaje      = $_SESSION['mensaje']        ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje']   ?? null;
$lastDoc      = $_SESSION['last_documento'] ?? null;

// Obtiene el año actual para mostrarlo en el footer
$anio = date('Y');

// Elimina las variables de sesión después de leerlas (son de un solo uso)
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['last_documento']);

/* =========================================================
 *  VISTA HTML — index.php
 * ========================================================= */
?>
<!DOCTYPE html>
<!-- lang="es" indica al navegador que el contenido está en español -->
<html lang="es">
<head>
    <!-- UTF-8 permite mostrar tildes, ñ y caracteres especiales -->
    <meta charset="UTF-8">
    <!-- viewport hace que el diseño sea responsive en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia — Empleados</title>

    <!-- Bootstrap CSS: sistema de grillas, componentes y utilidades -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons: librería de íconos usados con clases bi-* -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* Variables CSS globales reutilizadas en todo el archivo */
        :root {
            --sa-primary:      #2563eb;              /* Azul principal */
            --sa-primary-dark: #1d4ed8;              /* Azul oscuro para hover */
            --sa-glass:        rgba(255,255,255,0.08); /* Fondo glass del panel derecho */
            --sa-glass-border: rgba(255,255,255,0.15); /* Borde del panel glass */
        }

        /* Fondo degradado azul oscuro que cubre toda la página */
        body {
            background: linear-gradient(160deg, #1e3a5f 0%, #2563eb 55%, #3b82f6 100%);
            min-height: 100vh;
        }

        /* Tarjeta del formulario: sin borde, esquinas muy redondeadas */
        .card {
            border: none;
            border-radius: 1.25rem;
        }

        /* Sobreescribe el color del botón primario con el azul del proyecto */
        .btn-primary {
            background: var(--sa-primary);
            border-color: var(--sa-primary);
        }

        /* Azul más oscuro al pasar el mouse sobre el botón */
        .btn-primary:hover {
            background: var(--sa-primary-dark);
            border-color: var(--sa-primary-dark);
        }

        /* Campo de solo lectura: fondo gris y cursor bloqueado */
        .form-control:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        /* ── Panel informativo derecho con efecto glass ── */
        /* Superficie semitransparente sobre el degradado de fondo */
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

        /* Título principal del panel informativo */
        .info-panel h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            line-height: 1.3;
        }

        /* Subtítulo y textos descriptivos del panel */
        .info-desc {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.65;
            margin: 0;
        }

        /* Separador visual entre bloques del panel */
        .info-divider {
            border: none;
            border-top: 1px solid var(--sa-glass-border);
            margin: 0;
        }

        /* ── Items de características (features) ── */
        /* Cada feature: ícono a la izquierda + texto a la derecha */
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        /* Cuadrado redondeado de fondo del ícono */
        .feature-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #93c5fd;
        }

        /* Título de cada feature */
        .feature-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0 0 0.2rem;
        }

        /* Descripción de cada feature */
        .feature-text {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.65);
            margin: 0;
            line-height: 1.5;
        }

        /* ── Diagrama de flujo de 3 pasos ── */
        .flow-steps {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* Cada fila del flujo */
        .flow-step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        /* Columna del número + línea conectora */
        .flow-step-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 28px;
        }

        /* Círculo con el número del paso */
        .flow-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--sa-primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Línea vertical que une los pasos entre sí */
        .flow-connector {
            width: 2px;
            flex: 1;
            min-height: 24px;
            background: rgba(255,255,255,0.2);
            margin: 2px 0;
        }

        /* Bloque de texto de cada paso */
        .flow-content {
            padding-bottom: 0.75rem;
        }

        /* Título del paso en negrita */
        .flow-content strong {
            display: block;
            font-size: 0.85rem;
            color: #fff;
            margin-bottom: 0.15rem;
        }

        /* Descripción breve del paso */
        .flow-content span {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.6);
        }

        /* El último paso no necesita padding inferior extra */
        .flow-step:last-child .flow-content {
            padding-bottom: 0;
        }

        /* ── Badges de tecnologías ── */
        .tech-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        /* Cada badge de tecnología (PHP, MySQL, Bootstrap, etc.) */
        .tech-badge {
            font-size: 0.73rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            background: rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.85);
            border: 1px solid rgba(255,255,255,0.18);
            letter-spacing: 0.02em;
        }

        /* Fuente tabular para el reloj: evita que los dígitos "salten" al cambiar */
        #reloj {
            font-variant-numeric: tabular-nums;
        }

        /* ── Responsive: en pantallas pequeñas el panel va debajo del formulario ── */
        @media (max-width: 991.98px) {
            .info-panel {
                height: auto;
                margin-top: 0;
            }
        }
    </style>
</head>

<!-- d-flex flex-column min-vh-100: el footer siempre queda pegado al fondo -->
<body class="d-flex flex-column min-vh-100">

    <!-- ── Navbar superior ─────────────────────────────────────────── -->
    <!-- rgba(0,0,0,.2): negro semitransparente sobre el degradado azul -->
    <nav class="navbar navbar-dark px-4 py-3" style="background: rgba(0,0,0,.2);">

        <!-- Marca: logo + nombre del sistema -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="#">
            <i class="bi bi-clock-history fs-5"></i>
            AsistenciaApp
        </a>

        <!-- Derecha del navbar: reloj en tiempo real + botón admin -->
        <div class="d-flex align-items-center gap-3">

            <!-- Reloj inicializado con PHP; JS lo actualiza cada segundo -->
            <!-- opacity-75: texto tenue para no competir con el botón de admin -->
            <span class="text-white opacity-75 small d-flex align-items-center gap-1">
                <i class="bi bi-calendar-event"></i>
                <!-- id="reloj": el setInterval() de JS actualiza este contenido -->
                <span id="reloj"><?= date('d/m/Y H:i:s') ?></span>
            </span>

            <!-- Botón de acceso al panel de administración -->
            <!-- btn-outline-light: botón transparente con borde y texto blancos -->
            <a href="admin/login.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock"></i>
                <!-- d-none d-md-inline: en móvil solo el ícono, en pantallas medianas el texto -->
                <span class="d-none d-md-inline">Iniciar sesión como administrador</span>
                <span class="d-md-none">Admin</span>
            </a>
        </div>
    </nav>

    <!-- ── Contenido principal ─────────────────────────────────────── -->
    <!-- flex-grow-1: ocupa todo el espacio vertical entre navbar y footer -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">

        <!-- container-xl: ancho mayor para soportar el layout de dos columnas -->
        <div class="container-xl px-4">
            <div class="row g-4 align-items-start justify-content-center">


                <!-- ══════════════════════════════════════════════════
                     COLUMNA IZQUIERDA — Formulario del empleado
                     col-lg-5: ocupa 5/12 del ancho en pantallas grandes
                     ══════════════════════════════════════════════════ -->
                <div class="col-12 col-lg-5">

                    <!-- shadow-lg: sombra pronunciada que eleva la tarjeta; sin h-100 para que no se estire -->
                    <div class="card shadow-lg">

                        <!-- Cabecera de la tarjeta con degradado azul -->
                        <div class="text-white text-center py-4 px-4 rounded-top"
                             style="background: linear-gradient(135deg, #1e3a5f, #2563eb);">
                            <!-- fs-1: ícono muy grande, d-block: ocupa su propia línea -->
                            <i class="bi bi-person-badge fs-1 mb-2 d-block"></i>
                            <h1 class="h4 fw-bold mb-1">Registro de Asistencia</h1>
                            <!-- opacity-75: subtítulo semitransparente sobre el encabezado -->
                            <p class="mb-0 opacity-75 small">
                                Ingresa tu documento y PIN para registrar tu entrada o salida.
                            </p>
                        </div>

                        <div class="card-body p-4">

                            <!-- Alerta flash: solo se renderiza si existe $mensaje -->
                            <?php if ($mensaje): ?>
                                <?php
                                // match() asigna la clase Bootstrap según el tipo de mensaje
                                $bsClase = match($tipo_mensaje) {
                                    'exito' => 'success',  // Verde
                                    'error' => 'danger',   // Rojo
                                    default => 'info',     // Azul
                                };
                                // match() selecciona el ícono de Bootstrap Icons correspondiente
                                $icono = match($tipo_mensaje) {
                                    'exito' => 'bi-check-circle-fill',
                                    'error' => 'bi-x-circle-fill',
                                    default => 'bi-info-circle-fill',
                                };
                                ?>
                                <!-- fade show: animación de entrada del alert -->
                                <!-- alert-dismissible: botón X para cerrar el alert -->
                                <div class="alert alert-<?= $bsClase ?> alert-dismissible d-flex align-items-center gap-2 fade show"
                                     role="alert">
                                    <i class="bi <?= $icono ?>"></i>
                                    <!-- htmlspecialchars: protege contra inyección de HTML/XSS -->
                                    <span><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></span>
                                    <!-- data-bs-dismiss="alert": Bootstrap maneja el cierre -->
                                    <button type="button" class="btn-close"
                                            data-bs-dismiss="alert" aria-label="Cerrar"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario POST al mismo archivo (index.php) -->
                            <!-- novalidate: toda la validación la maneja PHP, no el browser -->
                            <form method="POST" action="index.php" novalidate>

                                <!-- Campo: Documento de identidad -->
                                <div class="mb-3">
                                    <label for="documento" class="form-label fw-medium">
                                        <i class="bi bi-person-vcard me-1 text-primary"></i>
                                        Documento de identidad
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="documento"
                                        name="documento"
                                        placeholder="Ej: 1234567890"
                                        maxlength="15"
                                        autocomplete="off"
                                        value="<?= htmlspecialchars($lastDoc ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                    <!-- Texto de ayuda debajo del input -->
                                    <div class="form-text">Cédula o documento asignado por la empresa.</div>
                                </div>

                                <!-- Campo: PIN de 4 dígitos -->
                                <div class="mb-4">
                                    <label for="pin" class="form-label fw-medium">
                                        <i class="bi bi-key me-1 text-primary"></i>
                                        PIN de acceso
                                    </label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="pin"
                                        name="pin"
                                        placeholder="••••"
                                        maxlength="4"
                                        minlength="4"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >
                                    <!-- inputmode="numeric": muestra teclado numérico en móviles -->
                                    <div class="form-text">4 dígitos numéricos. Asignado por el administrador.</div>
                                </div>

                                <!-- d-grid: el botón ocupa el 100% del ancho disponible -->
                                <div class="d-grid">
                                    <button type="submit"
                                            class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                                        Registrar Asistencia
                                    </button>
                                </div>

                            </form>



                        </div><!-- /card-body -->
                    </div><!-- /card -->
                </div><!-- /col izquierda -->


                <!-- ══════════════════════════════════════════════════
                     COLUMNA DERECHA — Panel informativo del sistema
                     col-lg-6: ocupa 6/12 del ancho en pantallas grandes
                     ══════════════════════════════════════════════════ -->
                <div class="col-12 col-lg-6">
                    <div class="info-panel">

                        <!-- Encabezado del panel: nombre y descripción general -->
                        <div>
                            <!-- Badge de estado activo del sistema -->
                            <span class="badge mb-2"
                                  style="background:rgba(255,255,255,0.15);color:#bfdbfe;font-size:0.72rem;letter-spacing:0.06em;font-weight:600;">
                                SISTEMA ACTIVO
                            </span>
                            <h2>Sistema de Control de Asistencia</h2>
                            <p class="info-desc mt-2">
                                Plataforma web que registra automáticamente la entrada y salida
                                de empleados usando la hora exacta del servidor. Sin marcadores
                                físicos, sin papeles — todo queda almacenado en la base de datos
                                y disponible en tiempo real para el administrador.
                            </p>
                        </div>

                        <hr class="info-divider">

                        <!-- Características principales del sistema -->
                        <div>
                            <p class="info-desc mb-3"
                               style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.07em;opacity:0.6;">
                                ¿Qué hace este sistema?
                            </p>

                            <!-- Feature 1: Hora del servidor -->
                            <div class="feature-item mb-3">
                                <div class="feature-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div>
                                    <p class="feature-title">Hora automática del servidor</p>
                                    <p class="feature-text">
                                        Al registrar, el sistema captura el timestamp exacto del
                                        servidor local (<code style="color:#93c5fd;">NOW()</code> en MySQL).
                                        No depende del reloj del dispositivo del empleado.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 2: Detección entrada/salida -->
                            <div class="feature-item mb-3">
                                <div class="feature-icon">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                                <div>
                                    <p class="feature-title">Entrada y salida inteligente</p>
                                    <p class="feature-text">
                                        Si no tienes registro de entrada hoy, se crea uno nuevo.
                                        Si ya entraste, el siguiente registro marca tu salida
                                        automáticamente.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 3: Seguridad -->
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </div>
                                <div>
                                    <p class="feature-title">Seguridad de credenciales</p>
                                    <p class="feature-text">
                                        Cada PIN de acceso está protegido mediante cifrado de alta seguridad 
                                        (<code style="color:#93c5fd;">password_hash</code>).
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div><!-- /info-panel -->
                </div><!-- /col derecha -->

            </div><!-- /row -->
        </div><!-- /container -->
    </main>

    <!-- ── Footer ──────────────────────────────────────────────────── -->
    <!-- opacity-50: texto tenue sobre el fondo azul -->
    <footer class="text-center text-white py-3 opacity-50 small">
        Sistema de Control de Asistencia &mdash; <?= $anio ?>
    </footer>

    <!-- Bootstrap JS: necesario para el comportamiento del alert dismissible -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Elimina cualquier carácter no numérico mientras el usuario escribe el PIN
        // /\D/g es una expresión regular que coincide con todos los no-dígitos
        document.getElementById('pin').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });

        // Actualiza el texto del reloj en el navbar cada 1 segundo (1000ms)
        setInterval(() => {
            const ahora = new Date();
            // toLocaleDateString: formato dd/mm/aaaa según locale colombiano
            const fecha = ahora.toLocaleDateString('es-CO', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });
            // toLocaleTimeString: formato HH:mm:ss según locale colombiano
            const hora = ahora.toLocaleTimeString('es-CO');
            // Reemplaza el contenido del span con la fecha y hora actuales
            document.getElementById('reloj').textContent = fecha + ' ' + hora;
        }, 1000);

        // Coloca el foco en el campo documento al cargar la página
        // Mejora usabilidad: el empleado puede escribir sin hacer clic primero
        document.getElementById('documento').focus();
    </script>

    <?php
    // Incluye el footer.php del proyecto si existe (puede tener scripts adicionales)
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>

</body>
</html>