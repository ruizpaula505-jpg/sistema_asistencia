<?php
session_start();

$mensaje      = $_SESSION['mensaje']      ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia — Empleados</title>

    <!-- Google Fonts: Syne (títulos) + DM Sans (cuerpo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <!-- Hoja de estilos externa -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="top-header">
        <div class="brand">
            <div class="brand-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <span class="brand-name">AsistenciaApp</span>
        </div>

        <a href="admin/login.php" class="btn-admin-header">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Iniciar sesión como administrador</span>
        </a>
    </header>

    <div class="contenido">
        <main class="card" role="main">
            <div class="card-header">
                <h1>Registro de Asistencia</h1>
                <p>Ingresa tu documento y PIN para registrar tu entrada o salida.</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alerta alerta-<?= htmlspecialchars($tipo_mensaje, ENT_QUOTES, 'UTF-8') ?>" role="alert">
                    <?php if ($tipo_mensaje === 'exito'): ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    <?php elseif ($tipo_mensaje === 'error'): ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    <?php else: ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    <?php endif; ?>
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="includes/procesar_asistencia.php" novalidate>

                <div class="campo-grupo">
                    <label for="documento">Documento de identidad</label>
                    <input
                        type="text"
                        id="documento"
                        name="documento"
                        placeholder="Ej: 1234567890"
                        maxlength="15"
                        autocomplete="off"
                        required
                        <?php
                        if (!empty($_SESSION['last_documento'])):
                            echo 'value="' . htmlspecialchars($_SESSION['last_documento'], ENT_QUOTES, 'UTF-8') . '"';
                            unset($_SESSION['last_documento']);
                        endif;
                        ?>
                    >
                    <p class="campo-ayuda">Cédula o documento asignado por la empresa.</p>
                </div>

                <div class="campo-grupo">
                    <label for="pin">PIN de acceso</label>
                    <input
                        type="password"
                        id="pin"
                        name="pin"
                        placeholder="••••"
                        maxlength="4"
                        minlength="4"
                        pattern="\d{4}"
                        autocomplete="off"
                        inputmode="numeric"
                        required
                    >
                    <p class="campo-ayuda">4 dígitos numéricos. Asignado por el administrador.</p>
                </div>

                <!-- Nuevo campo fecha y hora -->
                <div class="campo-grupo">
                    <label for="fecha_hora">Fecha y hora para registrar</label>
                    <input
                        type="datetime-local"
                        id="fecha_hora"
                        name="fecha_hora"
                        required
                        value="<?= date('Y-m-d\TH:i') ?>"
                    >
                    <p class="campo-ayuda">Selecciona la fecha y hora que quieres registrar.</p>
                </div>

                <button type="submit" class="btn-registrar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Registrar Asistencia
                </button>

            </form>

        </main>

        <footer>
            <p>Sistema de Control de Asistencia &mdash; <?= date('Y') ?></p>
        </footer>

    </div>

    <script>
        const pinInput = document.getElementById('pin');
        pinInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });

        document.getElementById('documento').focus();
    </script>

</body>
</html>