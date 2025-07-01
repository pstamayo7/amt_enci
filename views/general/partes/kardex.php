<?php
require_once __DIR__ . '/../../../models/empresa.model.php';
require_once __DIR__ . '/../../../models/empleado.model.php';
require_once __DIR__ . '/../../../models/producto.model.php';
require_once __DIR__ . '/../../../models/kardex.model.php';

$empresaModel = new Empresa($pdo);
$empleadoModel = new Empleado($pdo);
$productoModel = new Producto($pdo);
$kardexModel   = new Kardex($pdo);

$usuario_id = $_SESSION['usuario']['id'];
$empresas   = $empresaModel->listarPorUsuario($usuario_id);

// Registro de movimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    try {
        $kardexModel->registrarMovimiento([
            'empresa_id'     => $_POST['empresa_id'],
            'producto_id'    => $_POST['pro_id'],
            'empleado_id'    => $_POST['empl_id'],
            'tipo_movimiento' => $_POST['tipo_movimiento'],
            'cantidad'       => $_POST['cantidad'],
            'valor_unitario' => $_POST['valor_unitario']
        ]);
        header("Location: dashboard.php?vista=kardex&empresa_id={$_POST['empresa_id']}&pro_id={$_POST['pro_id']}");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Mostrarlo luego si deseas
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anular_movimiento'])) {
    $kardexModel->anularMovimiento($_POST['anular_movimiento']);
    header("Location: dashboard.php?vista=kardex&empresa_id={$_POST['empresa_id']}&pro_id={$_POST['pro_id']}");
    exit;
}



// Parámetros
$empresa_id = $_GET['empresa_id'] ?? null;
$pro_id     = $_GET['pro_id'] ?? null;

// Datos condicionales
$productos  = $empresa_id ? $productoModel->listarPorEmpresa($empresa_id) : [];
$empleados  = $empresa_id ? $empleadoModel->listarPorUsuario($usuario_id) : [];
$historial  = $pro_id ? $kardexModel->historialPorProducto($pro_id) : [];
$ultimo_valor_unitario = null;
foreach (array_reverse($historial) as $mov) {
    if ($mov['kar_ecantidad'] > 0) {
        $ultimo_valor_unitario = $mov['kar_evunitario'];
        break;
    }
}
$ultimo_valor_salida = null;
foreach (array_reverse($historial) as $mov) {
    if ($mov['kar_scantidad'] > 0) {
        $ultimo_valor_salida = $mov['kar_svunitario'];
        break;
    }
}

$valor_existencia_unitario = $historial ? end($historial)['kar_exvunitario'] : null;

?>
<?php if (!empty($error)): ?>
    <div style="color: red; font-weight: bold; margin-top: 10px;">
        ⚠ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<style>
    .kardex-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 0px 0 5px 0;
        /* top, right, bottom, left */
    }

    .panel-registro {
        flex: 1 1 3%;
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        min-width: 300px;
    }

    .panel-historial {
        flex: 1 1 70%;
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        min-width: 400px;
        overflow-x: auto;
    }

    h3 {
        margin-top: 0;
        color: #2e7d32;
    }

    select,
    input,
    button {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
        font-size: 1em;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    button {
        background-color: #388e3c;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: #2e7d32;
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
        border: 1px solid #ccc;
        text-align: left;
    }

    th {
        background-color: #e0f2f1;
    }

    .subtle {
        color: #777;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .kardex-layout {
            flex-direction: column;
        }
    }

    /* Tabla general */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9em;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    /* Encabezados */
    thead tr:first-child {
        background-color: #004d40;
        /* verde oscuro */
        color: #fff;
    }

    thead tr:nth-child(2) {
        background-color: #b2dfdb;
        /* verde agua */
        font-weight: bold;
    }

    thead th,
    tfoot td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
    }

    /* Filas */
    tbody td {
        padding: 10px;
        text-align: center;
        border: 1px solid #eee;
    }

    /* Totales */
    tfoot {
        background-color: #e8f5e9;
        /* verde muy claro */
        font-weight: bold;
        color: #2e7d32;
        border-top: 2px solid #c8e6c9;
    }

    /* Filas alternas */
    tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    /* Secciones diferenciadas */
    td.entrada {
        background-color: #e0f7fa;
        /* azul claro */
    }

    td.salida {
        background-color: #fff3e0;
        /* naranja claro */
    }

    td.existencia {
        background-color: #e3f2fd;
        /* celeste claro */
    }

    /* Tipo de afectación */
    td.afecta-Incrementa {
        background-color: #d0f0c0;
    }

    td.afecta-Disminuye {
        background-color: #ffe0e0;
    }

    td.afecta-Inicial {
        background-color: #cce5ff;
    }

    .panel-historial {
        position: relative;
        max-height: 600px;
        /* Ajustable */
        overflow: auto;
    }

    /* Encabezados fijos */
    table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #00695c;
        /* verde más intenso */
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    table thead tr:nth-child(2) th {
        background-color: #26a69a;
        /* fila subencabezado más visible */
        color: #ffffff;
    }

    input.bloqueado {
        background-color: #eee;
        color: #555;
        cursor: not-allowed;
    }
</style>

<div class="kardex-layout">
    <!-- REGISTRO DE MOVIMIENTO -->
    <div class="panel-registro">
        <h3>Registrar Movimiento</h3>

        <form method="GET">
            <input type="hidden" name="vista" value="kardex">
            <label>Empresa:</label>
            <select name="empresa_id" onchange="this.form.submit()" required>
                <option value="">-- Selecciona empresa --</option>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?= $emp['emp_id'] ?>" <?= ($empresa_id == $emp['emp_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['emp_nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($empresa_id): ?>
            <form method="GET">
                <input type="hidden" name="vista" value="kardex">
                <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                <label>Producto:</label>
                <select name="pro_id" onchange="this.form.submit()" required>
                    <option value="">-- Producto --</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?= $prod['pro_id'] ?>" <?= ($pro_id == $prod['pro_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod['pro_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>

        <?php if ($empresa_id && $pro_id): ?>
            <form method="POST">
                <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                <input type="hidden" name="pro_id" value="<?= $pro_id ?>">

                <label>Empleado:</label>
                <select name="empl_id" required>
                    <option value="">-- Empleado activo --</option>
                    <?php foreach ($empleados as $empl): ?>
                        <?php if ($empl['emp_id'] == $empresa_id && $empl['empl_estado'] == 'activo'): ?>
                            <option value="<?= $empl['empl_id'] ?>"><?= htmlspecialchars($empl['empl_nombre']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <label>Tipo de movimiento:</label>
                <select name="tipo_movimiento" id="tipo_movimiento" required>
                    <option value="">-- Tipo --</option>
                    <option value="Inventario Inicial">Inventario Inicial</option>
                    <option value="Compra de MPD">Compra de MPD</option>
                    <option value="Devolución en compras">Devolución en compras</option>
                    <option value="Envío a producción (OP)">Envío a producción (OP)</option>
                    <option value="Devolución de producción">Devolución de producción</option>
                </select>

                <input type="number" name="cantidad" step="0.01" placeholder="Cantidad" required>
                <input type="number"
                    id="valor_unitario"
                    name="valor_unitario"
                    step="0.01"
                    placeholder="Valor unitario"
                    required
                    data-ultimo="<?= $ultimo_valor_unitario ?? '' ?>"
                    data-existencia="<?= $valor_existencia_unitario ?? '' ?>"
                    data-salida="<?= $ultimo_valor_salida ?? '' ?>">


                <button name="registrar_movimiento" type="submit">Registrar</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- HISTORIAL -->
    <div class="panel-historial">
        <h3>📋 Historial del producto</h3>
        <?php if ($historial): ?>
            <?php
            $total_ent_cant = $total_ent_total = 0;
            $total_sal_cant = $total_sal_total = 0;
            $ultima_existencia = ['cant' => 0, 'unitario' => 0, 'total' => 0];
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th colspan="3">Entradas</th>
                        <th colspan="3">Salidas</th>
                        <th colspan="3">Existencia</th>
                        <th>Afecta</th>
                        <th>Acción</th>

                    </tr>
                    <tr>
                        <th colspan="3"></th>
                        <th>Cant.</th>
                        <th>V. Unit.</th>
                        <th>Total</th>
                        <th>Cant.</th>
                        <th>V. Unit.</th>
                        <th>Total</th>
                        <th>Cant.</th>
                        <th>V. Unit.</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $total_ent_cant = $total_ent_total = 0;
                    $total_sal_cant = $total_sal_total = 0;
                    $total_ex_cant  = $total_ex_total  = 0;
                    $ultima_existencia = ['cant' => 0, 'unitario' => 0, 'total' => 0];
                    ?>


                    <?php foreach ($historial as $mov): ?>
                        <?php
                        // Acumular totales
                        $total_ent_cant += abs(floatval($mov['kar_ecantidad']));
                        $total_ent_total += abs(floatval($mov['kar_evtotal']));
                        $total_sal_cant += abs(floatval($mov['kar_scantidad']));
                        $total_sal_total += abs(floatval($mov['kar_svtotal']));

                        $total_ex_cant += abs(floatval($mov['kar_excantidad']));
                        $total_ex_total += abs(floatval($mov['kar_extotal']));


                        $ultima_existencia = [
                            'cant' => $mov['kar_excantidad'],
                            'unitario' => $mov['kar_exvunitario'],
                            'total' => $mov['kar_extotal']
                        ];

                        // Lógica para columna de afectación
                        $afecta = '';
                        $afecta_color = '';

                        if (
                            $mov['kar_ecantidad'] > 0 &&
                            $mov['kar_excantidad'] == $mov['kar_ecantidad'] &&
                            $mov['kar_scantidad'] == 0
                        ) {
                            $afecta = 'Inventario Inicial';
                            $afecta_color = '#cce5ff';
                        } elseif ($mov['kar_ecantidad'] > 0 || $mov['kar_scantidad'] < 0) {
                            $afecta = 'Incrementa';
                            $afecta_color = '#d4edda';
                        } elseif ($mov['kar_ecantidad'] < 0 || $mov['kar_scantidad'] > 0) {
                            $afecta = 'Disminuye';
                            $afecta_color = '#f8d7da';
                        } else {
                            $afecta = '-';
                        }

                        ?>

                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($mov['kar_fecha'])) ?></td>
                            <td><?= htmlspecialchars($mov['empl_nombre']) ?></td>
                            <td><?= $mov['kar_ecantidad'] != 0 ? 'Entrada' : 'Salida' ?></td>

                            <!-- Entradas -->
                            <td class="entrada"><?= $mov['kar_ecantidad'] != 0 ? number_format($mov['kar_ecantidad'], 2) : '-' ?></td>
                            <td class="entrada"><?= $mov['kar_ecantidad'] != 0 ? '$' . number_format($mov['kar_evunitario'], 2) : '-' ?></td>
                            <td class="entrada"><?= $mov['kar_ecantidad'] != 0 ? '$' . number_format($mov['kar_evtotal'], 2) : '-' ?></td>

                            <!-- Salidas -->
                            <td class="salida"><?= $mov['kar_scantidad'] != 0 ? number_format($mov['kar_scantidad'], 2) : '-' ?></td>
                            <td class="salida"><?= $mov['kar_scantidad'] != 0 ? '$' . number_format($mov['kar_svunitario'], 2) : '-' ?></td>
                            <td class="salida"><?= $mov['kar_scantidad'] != 0 ? '$' . number_format($mov['kar_svtotal'], 2) : '-' ?></td>

                            <!-- Existencias -->
                            <td class="existencia"><?= number_format($mov['kar_excantidad'], 2) ?></td>
                            <td class="existencia">$<?= number_format($mov['kar_exvunitario'], 2) ?></td>
                            <td class="existencia">$<?= number_format($mov['kar_extotal'], 2) ?></td>

                            <!-- Tipo de afectación -->
                            <td class="afecta-<?= $afecta === 'Incrementa' ? 'Incrementa' : ($afecta === 'Disminuye' ? 'Disminuye' : 'Inicial') ?>">
                                <strong><?= $afecta ?></strong>
                            </td>
                            <td>
                                <?php if ($mov['kar_estado'] === 'activo'): ?>
                                    <form method="POST" onsubmit="return confirm('¿Seguro que deseas anular este movimiento?');">
                                        <input type="hidden" name="anular_movimiento" value="<?= $mov['kar_id'] ?>">
                                        <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                                        <input type="hidden" name="pro_id" value="<?= $pro_id ?>">
                                        <button style="background-color:#d32f2f; color:white; border:none; padding:5px 10px; border-radius:5px;">Anular</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #888; font-style: italic;">Anulado</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background-color: #e0f2f1;">
                        <td colspan="3" style="text-align: right;">Total entradas:</td>
                        <td><?= number_format($total_ent_cant, 2) ?></td>
                        <td>-</td>
                        <td>$<?= number_format($total_ent_total, 2) ?></td>

                        <td colspan="6"></td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #fff3e0;">
                        <td colspan="3" style="text-align: right;">Total salidas:</td>
                        <td colspan="3"></td>
                        <td><?= number_format($total_sal_cant, 2) ?></td>
                        <td>-</td>
                        <td>$<?= number_format($total_sal_total, 2) ?></td>

                        <td colspan="3"></td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #e3f2fd;">
                        <td colspan="3" style="text-align: right;">Suma absoluta de existencias:</td>
                        <td colspan="6"></td>
                        <td><?= number_format($total_ex_cant, 2) ?></td>
                        <td>-</td>
                        <td>$<?= number_format($total_ex_total, 2) ?></td>
                        <td></td>
                    </tr>

                </tfoot>

            </table>

        <?php else: ?>
            <p class="subtle">Aún no hay movimientos registrados para este producto.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoMovimiento = document.getElementById('tipo_movimiento');
        const valorUnitario = document.getElementById('valor_unitario');
        const salidaValor = valorUnitario.getAttribute('data-salida');

        if (tipoMovimiento && valorUnitario) {
            tipoMovimiento.addEventListener('change', function() {
                const ultimoValor = valorUnitario.getAttribute('data-ultimo');
                const existenciaValor = valorUnitario.getAttribute('data-existencia'); // 🔥 ESTA LÍNEA FALTABA


                if (this.value === 'Devolución en compras') {
                    if (ultimoValor) {
                        valorUnitario.value = parseFloat(ultimoValor).toFixed(2);
                        valorUnitario.readOnly = true;
                        valorUnitario.classList.add('bloqueado');
                    }
                } else if (this.value === 'Envío a producción (OP)') {
                    if (existenciaValor) {
                        valorUnitario.value = parseFloat(existenciaValor).toFixed(2);
                        valorUnitario.readOnly = true;
                        valorUnitario.classList.add('bloqueado');
                    }
                } else if (this.value === 'Devolución de producción') {
                    if (salidaValor) {
                        valorUnitario.value = parseFloat(salidaValor).toFixed(2);
                        valorUnitario.readOnly = true;
                        valorUnitario.classList.add('bloqueado');
                    }
                } else {
                    valorUnitario.readOnly = false;
                    valorUnitario.classList.remove('bloqueado');
                    valorUnitario.value = '';
                }
            });
        }
    });
</script>