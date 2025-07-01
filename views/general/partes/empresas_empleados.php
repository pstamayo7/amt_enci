<?php
require_once __DIR__ . '/../../../models/empresa.model.php';
require_once __DIR__ . '/../../../models/empleado.model.php';
require_once __DIR__ . '/../../../models/sueldo.model.php';


$empresaModel = new Empresa($pdo);
$empleadoModel = new Empleado($pdo);
$sueldoModel = new Sueldo($pdo);


$usuario_id = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_empresa'])) {
        $empresaModel->crear($_POST['nombre'], $_POST['ruc'], $_POST['actividad'], $usuario_id);
        header("Location: dashboard.php?vista=gestion");
        exit;
    }

    if (isset($_POST['crear_empleado'])) {
        $empleadoModel->crear($_POST['empl_nombre'], $_POST['empl_cedula'], $usuario_id);
        header("Location: dashboard.php?vista=gestion");
        exit;
    }

    if (isset($_POST['asignar_empresa'])) {
        $empleadoModel->asignarEmpresaYActivar($_POST['empl_id'], $_POST['emp_id'], $usuario_id);
        header("Location: dashboard.php?vista=gestion");
        exit;
    }
    if (isset($_POST['cambiar_sueldo'])) {
        $empleado_id = $_POST['empl_id'];
        $nuevo_sueldo_id = $_POST['nuevo_sueldo'];

        $sql = "UPDATE tbl_empleado SET sue_id = ? WHERE empl_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_sueldo_id, $empleado_id]);

        header("Location: dashboard.php?vista=gestion");
        exit;
    }
}
if (isset($_POST['crear_sueldo'])) {
    $emp_id = $_POST['emp_id'] ?? null;
    $sue_valor = $_POST['sue_valor'] ?? null;

    if ($emp_id && is_numeric($sue_valor)) {
        if ($sueldoModel->crear($emp_id, $sue_valor)) {
            echo "<script>alert('Sueldo registrado correctamente.');</script>";
            echo "<script>location.href='dashboard.php?vista=gestion&filtro_empresa={$emp_id}';</script>";
        } else {
            echo "<script>alert('Error al guardar el sueldo.');</script>";
        }
    } else {
        echo "<script>alert('Datos inválidos.');</script>";
    }
}

if (isset($_GET['eliminar_empresa'])) {
    $empresaModel->eliminarLogicamente($_GET['eliminar_empresa'], $usuario_id);
    header("Location: dashboard.php?vista=gestion");
    exit;
}

if (isset($_GET['eliminar_empleado'])) {
    $empleadoModel->eliminarLogicamente($_GET['eliminar_empleado'], $usuario_id);
    header("Location: dashboard.php?vista=gestion");
    exit;
}











//SUELDOS
$emp_id = $_GET['filtro_empresa'] ?? null;

if ($emp_id) {
    $sueldos = $sueldoModel->listarPorEmpresa($emp_id);
} else {
    $sueldos = []; // o alguna lista por defecto, si quieres
}
// Método que traiga todos los sueldos
//$sueldos = $sueldoModel->listarPorEmpresa($empl['emp_id']);


// Límite común para paginación
$limite = 5;

// Paginación empresas
$pagina_emp = isset($_GET['pagina_emp']) ? (int) $_GET['pagina_emp'] : 1;
$offset_emp = ($pagina_emp - 1) * $limite;
$total_empresas = count($empresaModel->listarPorUsuario($usuario_id));
$empresas = array_slice($empresaModel->listarPorUsuario($usuario_id), $offset_emp, $limite);

// Empleados
$filtroEmpresa = $_GET['filtro_empresa'] ?? null;

if ($filtroEmpresa) {
    // Cuando filtro activo, obtienes empleados sin paginar (o puedes paginar aparte)
    $empleados = $empleadoModel->listarPorEmpresa($filtroEmpresa);

    // Definir variables para que no haya warning en paginación empleados
    $total_empleados = count($empleados);  // total es lo que devuelve el filtro
    $pagina_empl = 1;  // Por defecto en la página 1 cuando hay filtro
} else {
    // Paginación empleados cuando no hay filtro
    $pagina_empl = isset($_GET['pagina_empl']) ? (int) $_GET['pagina_empl'] : 1;
    $offset_empl = ($pagina_empl - 1) * $limite;
    $empleados_completos = $empleadoModel->listarPorUsuario($usuario_id);
    $total_empleados = count($empleados_completos);
    $empleados = array_slice($empleados_completos, $offset_empl, $limite);
}

// Agrupar empleados por empresa (opcional, según tu lógica)
$empleadosAgrupados = [];
foreach ($empleadoModel->listarPorUsuario($usuario_id) as $empl) {
    if ($empl['empl_estado'] === 'activo' && $empl['emp_nombre']) {
        $empleadosAgrupados[$empl['emp_nombre']][] = $empl;
    }
}

?>

<style>
    .gestion-container {
        display: flex;
        gap: 20px;
        padding: 20px;
        flex-wrap: wrap;
    }

    .panel {
        flex: 1;
        min-width: 400px;
        background: #ffffff;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        box-sizing: border-box;
    }

    .panel h3 {
        margin-top: 0;
        font-size: 1.2em;
        color: #333;
        margin-bottom: 10px;
    }

    .form-box {
        margin-top: 15px;
        background: #f7f7f7;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
        display: none;
    }

    input,
    textarea,
    select,
    button {
        width: 100%;
        box-sizing: border-box;
        padding: 8px;
        margin-bottom: 10px;
        font-size: 0.95em;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-danger {
        background-color: #dc3545;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
        margin-top: 15px;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
        vertical-align: middle;
    }

    th {
        background-color: #f0f0f0;
    }
</style>

<div class="gestion-container">

    <!-- PANEL EMPRESAS -->
    <div class="panel">
        <h3>Empresas</h3>
        <button onclick="toggleForm('formEmpresa')">+ Crear Empresa</button>

        <div id="formEmpresa" class="form-box">
            <form method="POST">
                <input type="text" name="nombre" placeholder="Nombre de la empresa" required>
                <input type="text" name="ruc" placeholder="RUC" maxlength="13" required>
                <textarea name="actividad" placeholder="Actividad de la empresa..."></textarea>
                <button type="submit" name="crear_empresa">Guardar Empresa</button>
            </form>
        </div>

        <?php if (count($empresas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RUC</th>
                        <th>Actividad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['emp_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($emp['emp_ruc']); ?></td>
                            <td><?php echo htmlspecialchars($emp['emp_actividad']); ?></td>

                            <td>
                                <form method="GET" onsubmit="return confirm('¿Eliminar esta empresa?')">
                                    <input type="hidden" name="eliminar_empresa" value="<?php echo $emp['emp_id']; ?>">
                                    <button class="btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top: 10px; text-align: center;">
                <?php
                $total_pag_emp = ceil($total_empresas / $limite);
                for ($i = 1; $i <= $total_pag_emp; $i++): ?>
                    <a href="dashboard.php?vista=gestion&pagina_emp=<?php echo $i; ?>&pagina_empl=<?php echo $pagina_empl; ?>"
                        style="margin: 0 5px; text-decoration: none; font-weight: <?php echo ($i == $pagina_emp) ? 'bold' : 'normal'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

        <?php else: ?>
            <p>No tienes empresas registradas.</p>
        <?php endif; ?>
    </div>

    <!-- PANEL EMPLEADOS -->
    <div class="panel">
        <h3>Empleados</h3>
        <button onclick="toggleForm('formEmpleado')">+ Agregar Empleado</button>

        <div id="formEmpleado" class="form-box">
            <form method="POST">
                <input type="text" name="empl_nombre" placeholder="Nombre del empleado" required>
                <input type="text" name="empl_cedula" placeholder="Cédula" maxlength="10" required>
                <button type="submit" name="crear_empleado">Guardar Empleado</button>
            </form>
        </div>

        <?php
        $filtroEmpresaSeleccionado = $_GET['filtro_empresa'] ?? '';
        ?>

        <form method="GET" action="dashboard.php">
            <input type="hidden" name="vista" value="gestion">
            <select name="filtro_empresa" onchange="this.form.submit()">
                <option value="">Todas las empresas</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= $empresa['emp_id']; ?>" <?= ($empresa['emp_id'] == $filtroEmpresaSeleccionado) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($empresa['emp_nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (count($empleados) > 0): ?>
            <table>
                <thead>

                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Estado</th>
                    <th>Empresa</th>
                    <th>Sueldo</th>
                    <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $empl): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empl['empl_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($empl['empl_cedula']); ?></td>
                            <td><?php echo $empl['empl_estado']; ?></td>
                            <td><?php echo $empl['emp_nombre'] ?? 'Sin asignar'; ?></td>
                            <td>$<?php echo number_format($empl['sue_valor'], 2); ?></td>
                            <td>

                                <?php if ($empl['empl_estado'] === 'pendiente'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="empl_id" value="<?php echo $empl['empl_id']; ?>">

                                        <!-- Línea 1: selector empresa -->
                                        <div style="margin-bottom: 5px;">
                                            <select name="emp_id" required style="width: 100%;">
                                                <option value="">-- Seleccionar empresa --</option>
                                                <?php foreach ($empresas as $emp): ?>
                                                    <option value="<?php echo $emp['emp_id']; ?>"><?php echo $emp['emp_nombre']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Línea 2: botones -->
                                        <div style="display: flex; gap: 8px;">
                                            <button name="asignar_empresa" class="btn-secondary" style="flex: 1;">Activar</button>
                                            <button formaction="dashboard.php" name="eliminar_empleado"
                                                value="<?php echo $empl['empl_id']; ?>" class="btn-danger" style="flex: 1;"
                                                onclick="return confirm('¿Eliminar este empleado?')">Eliminar</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- Botón eliminar -->
                                    <form method="GET" onsubmit="return confirm('¿Eliminar este empleado?')">
                                        <input type="hidden" name="eliminar_empleado" value="<?php echo $empl['empl_id']; ?>">
                                        <button class="btn-danger">Eliminar</button>
                                    </form>

                                    <!-- Botón para mostrar el combo de sueldos -->
                                    <button type="button" onclick="mostrarCambioSueldo('<?php echo $empl['empl_id']; ?>')"
                                        class="btn-primary">Cambiar Sueldo</button>

                                    <!-- Contenedor oculto para el selector de sueldo -->
                                    <div id="form-sueldo-<?php echo $empl['empl_id']; ?>" style="display: none; margin-top: 10px;">
                                        <form method="POST">
                                            <input type="hidden" name="empl_id" value="<?php echo $empl['empl_id']; ?>">
                                            <input type="hidden" name="emp_id" value="<?php echo $empl['emp_id']; ?>">

                                            <select name="nuevo_sueldo" required>
                                                <option value="">-- Selecciona un sueldo --</option>
                                                <?php
                                                $sueldos = $sueldoModel->listarPorEmpresa($empl['emp_id']);
                                                foreach ($sueldos as $sueldo): ?>
                                                    <option value="<?php echo $sueldo['sue_id']; ?>">
                                                        <?php echo '$' . number_format($sueldo['sue_valor'], 2); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <div style="margin-top: 5px; display: flex; gap: 8px;">
                                                <button type="submit" name="cambiar_sueldo" class="btn-success">Aceptar</button>
                                                <button type="button"
                                                    onclick="ocultarCambioSueldo('<?php echo $empl['empl_id']; ?>')"
                                                    class="btn-secondary">Cancelar</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 10px; text-align: center;">
                <?php
                $total_pag_empl = ceil($total_empleados / $limite);
                for ($i = 1; $i <= $total_pag_empl; $i++): ?>
                    <a href="dashboard.php?vista=gestion&pagina_empl=<?php echo $i; ?>&pagina_emp=<?php echo $pagina_emp; ?>"
                        style="margin: 0 5px; text-decoration: none; font-weight: <?php echo ($i == $pagina_empl) ? 'bold' : 'normal'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

        <?php else: ?>
            <p>No tienes empleados registrados.</p>
        <?php endif; ?>

        <?php if (!empty($filtroEmpresaSeleccionado)): ?>
            <div style="margin-top: 30px;">
                <h4>Agregar nuevo sueldo para la empresa seleccionada</h4>
                <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="emp_id" value="<?= htmlspecialchars($filtroEmpresaSeleccionado); ?>">
                    <input type="number" step="0.01" name="sue_valor" placeholder="Valor del sueldo" required>
                    <button type="submit" name="crear_sueldo" class="btn-success">Guardar Sueldo</button>
                </form>
            </div>
        <?php endif; ?>

        <?php
        $sueldosEmpresa = [];
        if (!empty($filtroEmpresaSeleccionado) && is_numeric($filtroEmpresaSeleccionado)) {
            $sueldosEmpresa = $sueldoModel->listarPorEmpresa((int) $filtroEmpresaSeleccionado);
        }
        if (count($sueldosEmpresa) > 0): ?>
            <div style="margin-top: 20px;">
                <h4>Sueldos registrados</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sueldosEmpresa as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['sue_id']) ?></td>
                                <td>$<?= number_format($s['sue_valor'], 2) ?></td>
                                <td><?= isset($s['sue_tipo']) ? htmlspecialchars($s['sue_tipo']) : 'personalizado' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>

<div style="padding: 20px; margin-top: -10px;">
    <div
        style="max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #ccc; padding: 20px; border-radius: 10px;">
        <h3 style="margin-top: 0; text-align: center;">Resumen: Empresas y sus Empleados</h3>

        <?php if (count($empleadosAgrupados) > 0): ?>
            <?php foreach ($empleadosAgrupados as $empresaNombre => $empleadosDeEmpresa): ?>
                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 5px;">📦 <?php echo htmlspecialchars($empresaNombre); ?></h4>
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($empleadosDeEmpresa as $empl): ?>
                            <li><?php echo htmlspecialchars($empl['empl_nombre']); ?>
                                (<?php echo htmlspecialchars($empl['empl_cedula']); ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No hay empleados activos con empresa asignada aún.</p>
        <?php endif; ?>

        <div style="text-align: center; margin-bottom: 15px;">
            <a href="/amt_enci/export/export_empresas_empleados_pdf.php" target="_blank">
                <button style="margin-right: 10px;">📄 Exportar PDF</button>
            </a>
            <a href="/amt_enci/export/export_empresas_empleados_excel.php" target="_blank">
                <button>📊 Exportar Excel</button>
            </a>
        </div>

    </div>
</div>


<script>
    function toggleForm(id) {
        const form = document.getElementById(id);
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }


    function mostrarCambioSueldo(emplId) {
        document.getElementById('form-sueldo-' + emplId).style.display = 'block';
    }

    function ocultarCambioSueldo(emplId) {
        document.getElementById('form-sueldo-' + emplId).style.display = 'none';
    }


</script>