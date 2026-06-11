<?php
/* =========================================================
 *  admin/empleados_crud.php
 * ========================================================= */
 
session_start(); // Inicia o reanuda la sesión del administrador
 
require_once __DIR__ . '/../includes/funciones.php';  // Funciones auxiliares (e(), validarDocumento(), etc.)
require_once __DIR__ . '/../includes/auth_admin.php'; // Verifica sesión activa; redirige al login si no hay
require_once __DIR__ . '/../config/db.php';           // Clase Database para la conexión PDO
 
$db             = new Database();
$pdo            = $db->conectar();                     // Establece la conexión con la base de datos
$idTipoEmpleado = obtenerIdTipo($pdo, 'Empleado');     // Obtiene el ID del tipo 'Empleado'
$areas          = obtenerAreas($pdo);                  // Trae todas las áreas para los selectores
$mensaje        = '';
$tipoMensaje    = '';
$modo           = $_GET['modo']            ?? 'listar'; // Modo actual: listar, crear, editar o pin
$documentoEditar = trim($_GET['documento'] ?? '');       // Documento del empleado a editar o cambiar PIN
 
// Si no existe el tipo Empleado en la BD detiene la ejecución
if ($idTipoEmpleado === null) {
    die('No existe el tipo de usuario Empleado en la base de datos.');
}
 
// ── Procesamiento POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? ''; // Acción enviada por el formulario: crear, actualizar, cambiar_pin, eliminar
 
    // ── CREAR EMPLEADO ─────────────────────────────────────
    if ($accion === 'crear') {
        $documento = trim($_POST['documento']       ?? '');
        $nombre    = trim($_POST['nombre_completo'] ?? '');
        $pin       = trim($_POST['pin']             ?? '');
        $idArea    = (int) ($_POST['id_area']       ?? 0);
        $estado    = $_POST['estado']               ?? 'ACTIVO';
 
        // Valida formato de todos los campos antes de tocar la BD
        if (!validarDocumento($documento) || $nombre === '' || !validarPin($pin)
            || $idArea <= 0 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $mensaje     = 'Complete correctamente todos los campos.';
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
                // Contraseña inicial fija encriptada con bcrypt; el empleado puede cambiarla luego
                $password = password_hash('123456', PASSWORD_DEFAULT);
                $pdo->prepare(
                    'INSERT INTO users (documento, pin, password, nombre_completo, id_tipo, id_area, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                )->execute([$documento, $pin, $password, $nombre, $idTipoEmpleado, $idArea, $estado]);
                $mensaje     = 'Empleado creado correctamente.';
                $tipoMensaje = 'success';
                $modo        = 'listar';
            }
        }
    }
 
    // ── ACTUALIZAR EMPLEADO ────────────────────────────────
    if ($accion === 'actualizar') {
        $documento = trim($_POST['documento']       ?? '');
        $nombre    = trim($_POST['nombre_completo'] ?? '');
        $idArea    = (int) ($_POST['id_area']       ?? 0);
        $estado    = $_POST['estado']               ?? 'ACTIVO';
 
        if (!validarDocumento($documento) || $nombre === '' || $idArea <= 0
            || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $mensaje         = 'Datos de actualización inválidos.';
            $tipoMensaje     = 'danger';
            $modo            = 'editar';
            $documentoEditar = $documento;
        } else {
            // Actualiza nombre, área y estado; el documento no se puede cambiar
            $stmt = $pdo->prepare(
                'UPDATE users SET nombre_completo = ?, id_area = ?, estado = ?
                 WHERE documento = ? AND id_tipo = ?'
            );
            $stmt->execute([$nombre, $idArea, $estado, $documento, $idTipoEmpleado]);
            // rowCount() indica si realmente hubo cambios en la BD
            $mensaje     = $stmt->rowCount() > 0 ? 'Empleado actualizado.' : 'Sin cambios.';
            $tipoMensaje = $stmt->rowCount() > 0 ? 'success' : 'warning';
            $modo        = 'listar';
        }
    }
 
    // ── CAMBIAR PIN ────────────────────────────────────────
    if ($accion === 'cambiar_pin') {
        $documento    = trim($_POST['documento']     ?? '');
        $pinActual    = trim($_POST['pin_actual']    ?? '');
        $pinNuevo     = trim($_POST['pin_nuevo']     ?? '');
        $pinConfirmar = trim($_POST['pin_confirmar'] ?? '');
 
        // Valida que ambos PIN sean de 4 dígitos y que el nuevo coincida con la confirmación
        if (!validarDocumento($documento) || !validarPin($pinActual)
            || !validarPin($pinNuevo) || $pinNuevo !== $pinConfirmar) {
            $mensaje         = 'Verifique el PIN actual y el nuevo (4 dígitos).';
            $tipoMensaje     = 'danger';
            $modo            = 'pin';
            $documentoEditar = $documento;
        } else {
            // Busca el PIN actual del empleado en la BD
            $stmt = $pdo->prepare('SELECT pin FROM users WHERE documento = ? AND id_tipo = ? LIMIT 1');
            $stmt->execute([$documento, $idTipoEmpleado]);
            $empleado = $stmt->fetch();
 
            // Compara el PIN ingresado con el almacenado en texto plano
            if (!$empleado || $pinActual !== $empleado['pin']) {
                $mensaje         = 'El PIN actual no es correcto.';
                $tipoMensaje     = 'danger';
                $modo            = 'pin';
                $documentoEditar = $documento;
            } else {
                // Si el PIN actual es correcto, actualiza al nuevo
                $pdo->prepare('UPDATE users SET pin = ? WHERE documento = ? AND id_tipo = ?')
                    ->execute([$pinNuevo, $documento, $idTipoEmpleado]);
                $mensaje     = 'PIN actualizado correctamente.';
                $tipoMensaje = 'success';
                $modo        = 'listar';
            }
        }
    }
 
    // ── ELIMINAR EMPLEADO ──────────────────────────────────
    if ($accion === 'eliminar') {
        $documento = trim($_POST['documento'] ?? '');
        if (!validarDocumento($documento)) {
            $mensaje     = 'Documento inválido.';
            $tipoMensaje = 'danger';
        } else {
            // Elimina solo si el documento pertenece a un Empleado (no admin)
            $stmt = $pdo->prepare('DELETE FROM users WHERE documento = ? AND id_tipo = ?');
            $stmt->execute([$documento, $idTipoEmpleado]);
            $mensaje     = $stmt->rowCount() > 0 ? 'Empleado eliminado.' : 'No se encontró el empleado.';
            $tipoMensaje = $stmt->rowCount() > 0 ? 'success' : 'warning';
        }
        $modo = 'listar';
    }
}
 
// ── Cargar empleado para editar o cambiar PIN ──────────────
$empleadoSeleccionado = null;
 
if (in_array($modo, ['editar', 'pin'], true) && validarDocumento($documentoEditar)) {
    // Trae los datos del empleado junto con el nombre de su área
    $stmt = $pdo->prepare(
        'SELECT u.documento, u.nombre_completo, u.id_area, u.estado, a.nombre AS nombre_area
         FROM users u
         INNER JOIN area a ON u.id_area = a.id_area
         WHERE u.documento = ? AND u.id_tipo = ?
         LIMIT 1'
    );
    $stmt->execute([$documentoEditar, $idTipoEmpleado]);
    $empleadoSeleccionado = $stmt->fetch();
 
    // Si no existe el empleado vuelve al listado con mensaje de error
    if (!$empleadoSeleccionado) {
        $mensaje     = 'Empleado no encontrado.';
        $tipoMensaje = 'danger';
        $modo        = 'listar';
    }
}
 
// ── Listado completo ───────────────────────────────────────
// Se carga siempre para tenerlo disponible en modo listar
$stmt = $pdo->prepare(
    'SELECT u.documento, u.nombre_completo, u.estado, a.nombre AS nombre_area
     FROM users u
     INNER JOIN area a ON u.id_area = a.id_area
     WHERE u.id_tipo = ?
     ORDER BY u.nombre_completo ASC'
);
$stmt->execute([$idTipoEmpleado]);
$empleados = $stmt->fetchAll(); // Guarda todos los empleados en un arreglo
 
$tituloPagina = 'Gestión de Empleados'; // Variable usada por header.php en el <title>
require_once __DIR__ . '/../includes/header.php';
?>
 
<div class="container py-5">
 
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Empleados</h1>
            <p class="text-muted mb-0">Crear, consultar y editar empleados.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($modo === 'listar'): ?>
                <a href="empleados_crud.php?modo=crear" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo empleado
                </a>
            <?php else: ?>
                <a href="empleados_crud.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                <i class="bi bi-house me-1"></i>Menú
            </a>
        </div>
    </div>
 
    <?php if ($mensaje !== ''): ?>
        <?= mensajeAlerta($tipoMensaje, $mensaje) ?> <!-- Muestra alerta Bootstrap si hubo operación -->
    <?php endif; ?>
 
    <!-- ── FORMULARIO CREAR ── -->
    <?php if ($modo === 'crear'): ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Registrar empleado</h2>
                <form method="POST" action="empleados_crud.php">
                    <input type="hidden" name="accion" value="crear"> <!-- Indica al POST que es una creación -->
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
                            <!-- Corregido: max debe ser 9999, no 4 -->
                            <input type="number" class="form-control" id="pin" name="pin" min="0" max="9999" maxlength="4" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="id_area">Área</label>
                            <select class="form-select" id="id_area" name="id_area" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= e((string) $area['id_area']) ?>"><?= e($area['nombre']) ?></option>
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
 
    <!-- ── FORMULARIO EDITAR ── -->
    <?php elseif ($modo === 'editar' && $empleadoSeleccionado): ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Editar empleado</h2>
                <form method="POST" action="empleados_crud.php">
                    <input type="hidden" name="accion"    value="actualizar">
                    <input type="hidden" name="documento" value="<?= e($empleadoSeleccionado['documento']) ?>"> <!-- Documento en hidden para no mostrarlo editable -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" value="<?= e($empleadoSeleccionado['documento']) ?>" disabled> <!-- disabled: solo lectura, no se envía -->
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
                                    <option value="<?= e((string) $area['id_area']) ?>"
                                        <?= (int) $area['id_area'] === (int) $empleadoSeleccionado['id_area'] ? 'selected' : '' ?>>
                                        <?= e($area['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <!-- selected se agrega dinámicamente según el estado actual del empleado -->
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
 
    <!-- ── FORMULARIO CAMBIAR PIN ── -->
    <?php elseif ($modo === 'pin' && $empleadoSeleccionado): ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Cambiar PIN</h2>
                <p class="text-muted">
                    Empleado: <strong><?= e($empleadoSeleccionado['nombre_completo']) ?></strong>
                    (<?= e($empleadoSeleccionado['documento']) ?>)
                </p>
                <form method="POST" action="empleados_crud.php">
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
 
    <!-- ── TABLA LISTADO ── -->
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
                                    <td colspan="5" class="text-center text-muted py-4">No hay empleados registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($empleados as $emp): ?>
                                    <tr>
                                        <td><?= e($emp['documento']) ?></td>
                                        <td><?= e($emp['nombre_completo']) ?></td>
                                        <td><?= e($emp['nombre_area']) ?></td>
                                        <td>
                                            <!-- Badge verde si ACTIVO, gris si INACTIVO -->
                                            <span class="badge <?= $emp['estado'] === 'ACTIVO' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                                <?= e($emp['estado']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="empleados_crud.php?modo=editar&documento=<?= urlencode($emp['documento']) ?>"
                                               class="btn btn-sm btn-outline-primary">Editar</a>
                                            <a href="empleados_crud.php?modo=pin&documento=<?= urlencode($emp['documento']) ?>"
                                               class="btn btn-sm btn-outline-warning">PIN</a>
                                            <!-- Formulario inline para eliminar con confirmación -->
                                            <form method="POST" action="empleados_crud.php" style="display:inline"
                                                  onsubmit="return confirm('¿Eliminar a <?= e($emp['nombre_completo']) ?>?')">
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
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>