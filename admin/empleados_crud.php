<?php
/* =========================================================
 *  LÓGICA PHP — empleados_crud.php
 * ========================================================= */

// $tituloPagina DEBE definirse ANTES del require_once de auth
// porque auth_admin.php llama a header.php que ya imprime el <title>
$tituloPagina = 'Gestión de Empleados';

// Verifica que haya sesión activa de administrador, si no redirige al login
require_once __DIR__ . '/../includes/auth_admin.php';

// Importa la clase Database para la conexión
require_once __DIR__ . '/../config/db.php';

// Importa funciones reutilizables
require_once __DIR__ . '/../includes/funciones.php';

// Crea la instancia de conexión a la base de datos
$db  = new Database();

// Obtiene el objeto PDO listo para hacer consultas
$pdo = $db->conectar();

// Busca el id del tipo Empleado en tipo_usuario
$idTipoEmpleado  = obtenerIdTipo($pdo, 'Empleado');

// Trae todas las áreas disponibles para los selects
$areas           = obtenerAreas($pdo);

// Inicializa variables de mensaje y modo de vista
$mensaje         = '';
$tipoMensaje     = '';
$modo            = $_GET['modo']       ?? 'listar';
$documentoEditar = trim($_GET['documento'] ?? '');

// Si no existe el tipo Empleado en la BD no puede continuar
if ($idTipoEmpleado === null) {
    die('No existe el tipo de usuario Empleado en la base de datos.');
}

// ── Procesamiento POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    // -- Crear empleado --
    if ($accion === 'crear') {

        $documento = trim($_POST['documento']       ?? '');
        $nombre    = trim($_POST['nombre_completo'] ?? '');
        $pin       = trim($_POST['pin']             ?? '');
        $idArea    = (int) ($_POST['id_area']       ?? 0);
        $estado    = $_POST['estado']               ?? 'ACTIVO';

        if (
            !validarDocumento($documento)
            || $nombre === ''
            || !validarPin($pin)
            || $idArea <= 0
            || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)
        ) {
            $mensaje     = 'Complete correctamente todos los campos del empleado.';
            $tipoMensaje = 'danger';
            $modo        = 'crear';
        } else {
            // Verifica que el documento no esté ya registrado
            $existe = $pdo->prepare('SELECT documento FROM users WHERE documento = ? LIMIT 1');
            $existe->execute([$documento]);

            if ($existe->fetch()) {
                $mensaje     = 'Ya existe un usuario con ese documento.';
                $tipoMensaje = 'danger';
                $modo        = 'crear';
            } else {
                // password_hash() cifra la contraseña por defecto con bcrypt
                $password = password_hash('123456', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users (documento, pin, password, nombre_completo, id_tipo, id_area, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$documento, $pin, $password, $nombre, $idTipoEmpleado, $idArea, $estado]);
                $mensaje     = 'Empleado creado correctamente.';
                $tipoMensaje = 'success';
                $modo        = 'listar';
            }
        }
    }

    // -- Actualizar empleado --
    if ($accion === 'actualizar') {

        $documento = trim($_POST['documento']       ?? '');
        $nombre    = trim($_POST['nombre_completo'] ?? '');
        $idArea    = (int) ($_POST['id_area']       ?? 0);
        $estado    = $_POST['estado']               ?? 'ACTIVO';

        if (
            !validarDocumento($documento)
            || $nombre === ''
            || $idArea <= 0
            || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)
        ) {
            $mensaje         = 'Datos de actualización inválidos.';
            $tipoMensaje     = 'danger';
            $modo            = 'editar';
            $documentoEditar = $documento;
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET nombre_completo = ?, id_area = ?, estado = ?
                 WHERE documento = ? AND id_tipo = ?'
            );
            $stmt->execute([$nombre, $idArea, $estado, $documento, $idTipoEmpleado]);

            $mensaje     = $stmt->rowCount() > 0
                ? 'Empleado actualizado correctamente.'
                : 'No se encontró el empleado o no hubo cambios.';
            $tipoMensaje = $stmt->rowCount() > 0 ? 'success' : 'warning';
            $modo        = 'listar';
        }
    }

    // -- Cambiar PIN --
    if ($accion === 'cambiar_pin') {

        $documento    = trim($_POST['documento']     ?? '');
        $pinActual    = trim($_POST['pin_actual']    ?? '');
        $pinNuevo     = trim($_POST['pin_nuevo']     ?? '');
        $pinConfirmar = trim($_POST['pin_confirmar'] ?? '');

        if (
            !validarDocumento($documento)
            || !validarPin($pinActual)
            || !validarPin($pinNuevo)
            || $pinNuevo !== $pinConfirmar
        ) {
            $mensaje         = 'Verifique el PIN actual y el nuevo PIN (4 dígitos).';
            $tipoMensaje     = 'danger';
            $modo            = 'pin';
            $documentoEditar = $documento;
        } else {
            $stmt = $pdo->prepare(
                'SELECT pin FROM users WHERE documento = ? AND id_tipo = ? LIMIT 1'
            );
            $stmt->execute([$documento, $idTipoEmpleado]);
            $empleado = $stmt->fetch();

            if (!$empleado || $pinActual !== $empleado['pin']) {
                $mensaje         = 'El PIN actual no es correcto.';
                $tipoMensaje     = 'danger';
                $modo            = 'pin';
                $documentoEditar = $documento;
            } else {
                $update = $pdo->prepare(
                    'UPDATE users SET pin = ? WHERE documento = ? AND id_tipo = ?'
                );
                $update->execute([$pinNuevo, $documento, $idTipoEmpleado]);
                $mensaje     = 'PIN actualizado correctamente.';
                $tipoMensaje = 'success';
                $modo        = 'listar';
            }
        }
    }

    // -- Eliminar empleado --
    if ($accion === 'eliminar') {

        $documento = trim($_POST['documento'] ?? '');

        if (!validarDocumento($documento)) {
            $mensaje     = 'Documento inválido.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = $pdo->prepare(
                'DELETE FROM users WHERE documento = ? AND id_tipo = ?'
            );
            $stmt->execute([$documento, $idTipoEmpleado]);

            $mensaje     = $stmt->rowCount() > 0
                ? 'Empleado eliminado correctamente.'
                : 'No se encontró el empleado.';
            $tipoMensaje = $stmt->rowCount() > 0 ? 'success' : 'warning';
        }

        $modo = 'listar';
    }
}

// ── Cargar empleado para editar o cambiar PIN ──────────────
$empleadoSeleccionado = null;

if (in_array($modo, ['editar', 'pin'], true) && validarDocumento($documentoEditar)) {

    $stmt = $pdo->prepare(
        'SELECT u.documento, u.nombre_completo, u.id_area, u.estado, a.nombre AS nombre_area
         FROM users u
         INNER JOIN area a ON u.id_area = a.id_area
         WHERE u.documento = ? AND u.id_tipo = ?
         LIMIT 1'
    );
    $stmt->execute([$documentoEditar, $idTipoEmpleado]);
    $empleadoSeleccionado = $stmt->fetch();

    if (!$empleadoSeleccionado) {
        $mensaje     = 'Empleado no encontrado.';
        $tipoMensaje = 'danger';
        $modo        = 'listar';
    }
}

// ── Listado completo de empleados ──────────────────────────
$stmt = $pdo->prepare(
    'SELECT u.documento, u.nombre_completo, u.estado, a.nombre AS nombre_area
     FROM users u
     INNER JOIN area a ON u.id_area = a.id_area
     WHERE u.id_tipo = ?
     ORDER BY u.nombre_completo ASC'
);
$stmt->execute([$idTipoEmpleado]);
$empleados = $stmt->fetchAll();

/* =========================================================
 *  VISTA HTML — empleados_crud.php
 * ========================================================= */
// se direcciona para el header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container pb-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Empleados</h1>
            <p class="text-muted mb-0">Crear, consultar y editar empleados.</p>
        </div>

        <?php if ($modo === 'listar'): ?>
            <a href="empleados_crud.php?modo=crear" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nuevo empleado
            </a>
        <?php else: ?>
            <a href="empleados_crud.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver al listado
            </a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje !== ''): ?>
        <?= mensajeAlerta($tipoMensaje, $mensaje) ?>
    <?php endif; ?>

    <!-- ══ MODO: CREAR ══════════════════════════════════════ -->
    <?php if ($modo === 'crear'): ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Registrar empleado</h2>

                <form method="post" action="empleados_crud.php">
                    <input type="hidden" name="accion" value="crear">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="documento">Documento</label>
                            <input type="text" class="form-control" id="documento" name="documento" maxlength="20" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="nombre_completo">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pin">PIN inicial (4 dígitos)</label>
                            <input type="password" class="form-control" id="pin" name="pin" maxlength="4" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="id_area">Área</label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= e((string) $area['id_area']) ?>">
                                        <?= e($area['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Guardar empleado</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- ══ MODO: EDITAR ═════════════════════════════════════ -->
    <?php elseif ($modo === 'editar' && $empleadoSeleccionado): ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Editar empleado</h2>

                <form method="post" action="empleados_crud.php">
                    <input type="hidden" name="accion"    value="actualizar">
                    <input type="hidden" name="documento" value="<?= e($empleadoSeleccionado['documento']) ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" value="<?= e($empleadoSeleccionado['documento']) ?>" disabled>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="nombre_completo">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo"
                                   value="<?= e($empleadoSeleccionado['nombre_completo']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="id_area">Área</label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <?php foreach ($areas as $area): ?>
                                    <option
                                        value="<?= e((string) $area['id_area']) ?>"
                                        <?= (int) $area['id_area'] === (int) $empleadoSeleccionado['id_area'] ? 'selected' : '' ?>
                                    >
                                        <?= e($area['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="ACTIVO"   <?= $empleadoSeleccionado['estado'] === 'ACTIVO'   ? 'selected' : '' ?>>Activo</option>
                                <option value="INACTIVO" <?= $empleadoSeleccionado['estado'] === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="empleados_crud.php?modo=pin&documento=<?= urlencode($empleadoSeleccionado['documento']) ?>"
                           class="btn btn-outline-warning">Cambiar PIN</a>
                    </div>
                </form>
            </div>
        </div>

    <!-- ══ MODO: CAMBIAR PIN ════════════════════════════════ -->
    <?php elseif ($modo === 'pin' && $empleadoSeleccionado): ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Cambiar PIN</h2>
                <p class="text-muted">
                    Empleado: <strong><?= e($empleadoSeleccionado['nombre_completo']) ?></strong>
                    (<?= e($empleadoSeleccionado['documento']) ?>)
                </p>

                <form method="post" action="empleados_crud.php">
                    <input type="hidden" name="accion"    value="cambiar_pin">
                    <input type="hidden" name="documento" value="<?= e($empleadoSeleccionado['documento']) ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="pin_actual">PIN actual</label>
                            <input type="password" class="form-control" id="pin_actual" name="pin_actual" maxlength="4" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pin_nuevo">PIN nuevo</label>
                            <input type="password" class="form-control" id="pin_nuevo" name="pin_nuevo" maxlength="4" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pin_confirmar">Confirmar PIN nuevo</label>
                            <input type="password" class="form-control" id="pin_confirmar" name="pin_confirmar" maxlength="4" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning">Actualizar PIN</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- ══ MODO: LISTAR ═════════════════════════════════════ -->
    <?php else: ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Área</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($empleados) === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hay empleados registrados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($empleados as $emp): ?>
                                    <tr>
                                        <td><?= e($emp['documento']) ?></td>
                                        <td><?= e($emp['nombre_completo']) ?></td>
                                        <td><?= e($emp['nombre_area']) ?></td>
                                        <td>
                                            <span class="badge <?= $emp['estado'] === 'ACTIVO' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                                <?= e($emp['estado']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="empleados_crud.php?modo=editar&documento=<?= urlencode($emp['documento']) ?>"
                                               class="btn btn-sm btn-outline-primary">Editar</a>
                                            <a href="empleados_crud.php?modo=pin&documento=<?= urlencode($emp['documento']) ?>"
                                               class="btn btn-sm btn-outline-warning">PIN</a>
                                            <form method="post" action="empleados_crud.php" style="display:inline"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar a <?= e($emp['nombre_completo']) ?>?')">
                                                <input type="hidden" name="accion"    value="eliminar">
                                                <input type="hidden" name="documento" value="<?= e($emp['documento']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php
// Incluye el cierre del </body> y </html> con los scripts de Bootstrap
require_once __DIR__ . '/../includes/footer.php';
?>
