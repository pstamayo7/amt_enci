<?php
require_once __DIR__ . '/../../../../config/db.php';

// Estilos CSS mejorados y más compactos
?>
<style>
    * {
        box-sizing: border-box;
    }

    .tarjeta-container {
        margin-bottom: 40px;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .tarjeta-header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 20px;
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
    }

    .tarjeta-content {
        padding: 25px;
    }

    .seccion-titulo {
        color: #2c3e50;
        margin: 25px 0 15px 0;
        font-size: 16px;
        font-weight: 600;
        border-left: 4px solid #3498db;
        padding-left: 12px;
    }

    /* Grid Layout para organizar secciones */
    .grid-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .grid-full {
        grid-column: 1 / -1;
    }

    .grid-item {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Estilos de tablas más compactos */
    .tabla-contenedor {
        width: 100%;
        overflow-x: auto;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin: 0;
    }

    th,
    td {
        padding: 8px 10px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    th {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    tr:hover {
        background-color: #e3f2fd;
        transition: background-color 0.2s ease;
    }

    .total-row {
        font-weight: bold;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        color: #1565c0;
    }

    .porcentaje-cell {
        font-style: italic;
        color: #6c757d;
        font-weight: 500;
    }

    .distribucion-aporte {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%) !important;
        color: #2e7d32;
    }

    .total-general-row {
        background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%) !important;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    /* Tablas pequeñas para resumen */
    .tabla-pequena {
        font-size: 12px;
    }

    .tabla-pequena th,
    .tabla-pequena td {
        padding: 6px 8px;
    }

    /* Cards para métricas importantes */
    .metricas-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .metrica-card {
        background: linear-gradient(135deg, #ffffff 0%, #f1f3f4 100%);
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease;
    }

    .metrica-card:hover {
        transform: translateY(-2px);
    }

    .metrica-valor {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .metrica-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: 1fr;
        }

        .metricas-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        table {
            font-size: 11px;
        }

        th,
        td {
            padding: 6px 8px;
        }
    }

.form-filtros-tarjetas {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-filtros-tarjetas label {
    font-weight: bold;
    color: #333;
}

.form-filtros-tarjetas select {
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
    cursor: pointer;
}

.form-filtros-tarjetas select:hover {
    border-color: #007bff;
}
    
</style>

<?php

$usuario_id = $_SESSION['usuario']['id']; 

// Obtener lista de empresas
$stmt_empresas = $pdo->prepare("SELECT emp_id, emp_nombre FROM tbl_empresa WHERE emp_usuario_id = ?");
$stmt_empresas->execute([$usuario_id]);
$empresas = $stmt_empresas->fetchAll();

// Obtener filtros desde GET
$empresa_id_seleccionada = $_GET['empresa_id'] ?? '';
$filtro_mes = $_GET['filtro_mes'] ?? '';

$condiciones = [];
$params = [];

if (!empty($empresa_id_seleccionada)) {
    $condiciones[] = "tt.emp_id = ?";
    $params[] = $empresa_id_seleccionada;
}

if (!empty($filtro_mes)) {
    $condiciones[] = "tt.tarj_mes = ?";
    $params[] = $filtro_mes;
}

$whereSQL = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// Consulta de tarjetas de tiempo con filtro aplicado
$stmt_tarjetas = $pdo->prepare("
    SELECT tt.tarj_id, tt.emp_id, e.emp_nombre, tt.tarj_mes, tt.tarj_total_mod, tt.tarj_total_moi,
           tt.tarj_total_provisiones, tt.tarj_total_aporte_patronal, tt.tarj_total_general
    FROM tbl_tarjeta_tiempo tt
    JOIN tbl_empresa e ON tt.emp_id = e.emp_id
    $whereSQL
    ORDER BY tt.tarj_mes DESC
");
$stmt_tarjetas->execute($params);
$tarjetas = $stmt_tarjetas->fetchAll();

// Obtener lista de meses disponibles
$meses_stmt = $pdo->query("SELECT DISTINCT tarj_mes FROM tbl_tarjeta_tiempo ORDER BY tarj_mes DESC");
$meses_disponibles = $meses_stmt->fetchAll(PDO::FETCH_COLUMN);
$usuario_id = $_SESSION['usuario']['id'] ?? ''; 

// Obtener lista de empresas asociadas al usuario
$stmt_empresas = $pdo->prepare("SELECT emp_id, emp_nombre FROM tbl_empresa WHERE emp_usuario_id = ?");
$stmt_empresas->execute([$usuario_id]);
$empresas = $stmt_empresas->fetchAll();

// Obtener filtros desde GET
$empresa_id_seleccionada = $_GET['empresa_id'] ?? '';
$filtro_mes = $_GET['filtro_mes'] ?? '';

$condiciones = [];
$params = [];

if (!empty($empresa_id_seleccionada)) {
    $condiciones[] = "tt.emp_id = ?";
    $params[] = $empresa_id_seleccionada;
}

if (!empty($filtro_mes)) {
    $condiciones[] = "tt.tarj_mes = ?";
    $params[] = $filtro_mes;
}

$whereSQL = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// Consulta de tarjetas de tiempo con filtro aplicado
$stmt_tarjetas = $pdo->prepare("
    SELECT tt.tarj_id, tt.emp_id, e.emp_nombre, tt.tarj_mes, tt.tarj_total_mod, tt.tarj_total_moi,
           tt.tarj_total_provisiones, tt.tarj_total_aporte_patronal, tt.tarj_total_general
    FROM tbl_tarjeta_tiempo tt
    JOIN tbl_empresa e ON tt.emp_id = e.emp_id
    $whereSQL
    ORDER BY tt.tarj_mes DESC
");
$stmt_tarjetas->execute($params);
$tarjetas = $stmt_tarjetas->fetchAll();

// Obtener lista de meses disponibles
$meses_stmt = $pdo->query("SELECT DISTINCT tarj_mes FROM tbl_tarjeta_tiempo ORDER BY tarj_mes DESC");
$meses_disponibles = $meses_stmt->fetchAll(PDO::FETCH_COLUMN);
?>



<form method="GET" action="dashboard.php" class="form-filtros-tarjetas">
    <input type="hidden" name="vista" value="mano_de_obra">
    <input type="hidden" name="view" value="tarjetas_de_tiempo">

    <label for="empresa_id">Empresa:</label>
    <select name="empresa_id" id="empresa_id" onchange="this.form.submit()">
        <option value="">-- Todas --</option>
        <?php foreach ($empresas as $emp): ?>
            <option value="<?= $emp['emp_id']; ?>" <?= ($empresa_id_seleccionada == $emp['emp_id']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($emp['emp_nombre']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="filtro_mes">Mes:</label>
    <select name="filtro_mes" id="filtro_mes" onchange="this.form.submit()">
        <option value="">-- Todos --</option>
        <?php foreach ($meses_disponibles as $mes): ?>
            <option value="<?= $mes ?>" <?= ($filtro_mes == $mes) ? 'selected' : '' ?>>
                <?= $mes ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php foreach ($tarjetas as $tarjeta): ?>
    <div class="tarjeta-container">
        <h3 class="tarjeta-header">
            <?= htmlspecialchars($tarjeta['emp_nombre']) ?> | <?= htmlspecialchars($tarjeta['tarj_mes']) ?>
        </h3>

        <div class="tarjeta-content">
            <?php
            // Obtener detalles para cálculos
            $stmt_det = $pdo->prepare("
                SELECT td.*, e.empl_nombre
                FROM tbl_tarjeta_tiempo_detalle td
                JOIN tbl_empleado e ON td.empl_id = e.empl_id
                WHERE td.tarj_id = ?
            ");
            $stmt_det->execute([$tarjeta['tarj_id']]);
            $detalles = $stmt_det->fetchAll();

            $total_horas_mod = 0;
            $total_horas_moi = 0;
            $total_horas = 0;
            $total_salario_hora = 0;

            foreach ($detalles as $detalle) {
                $horas_mod = $detalle['tdet_horas_mod'];
                $horas_moi = $detalle['tdet_horas_moi'] > 0 ? $detalle['tdet_horas_moi'] : 0;
                $total_horas_empl = $horas_mod + $horas_moi;

                $total_horas_mod += $horas_mod;
                $total_horas_moi += $horas_moi;
                $total_horas += $total_horas_empl;
                $total_salario_hora += $detalle['tdet_valor_hora'];
            }

            $total_horas_distribucion = $total_horas_mod + $total_horas_moi;
            $porc_mod = $total_horas_distribucion > 0 ? $total_horas_mod / $total_horas_distribucion : 0;
            $porc_moi = $total_horas_distribucion > 0 ? $total_horas_moi / $total_horas_distribucion : 0;
            ?>

            <!-- CARDS DE MÉTRICAS -->
            <div class="metricas-cards">
                <div class="metrica-card">
                    <div class="metrica-valor"><?= $total_horas ?></div>
                    <div class="metrica-label">Total Horas</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-valor"><?= round($porc_mod * 100, 1) ?>%</div>
                    <div class="metrica-label">MOD</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-valor"><?= round($porc_moi * 100, 1) ?>%</div>
                    <div class="metrica-label">MOI</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-valor">$<?= number_format(
                        $tarjeta['tarj_total_general'] +
                        $tarjeta['tarj_total_aporte_patronal'] +
                        $tarjeta['tarj_total_provisiones'],
                        0
                    ) ?></div>
                    <div class="metrica-label">Total General(excluido bonos)</div>
                </div>
            </div>
            <!-- GRID LAYOUT PRINCIPAL -->
            <div class="grid-container">
                <!-- DISTRIBUCIÓN PORCENTUAL -->
                <div class="grid-item">
                    <h4 class="seccion-titulo">Distribución Porcentual</h4>
                    <div class="tabla-contenedor">
                        <table class="tabla-pequena">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Horas</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>MOD</td>
                                    <td><?= $total_horas_mod ?></td>
                                    <td class="porcentaje-cell"><?= round($porc_mod * 100, 1) ?>%</td>
                                </tr>
                                <tr>
                                    <td>MOI</td>
                                    <td><?= $total_horas_moi ?></td>
                                    <td class="porcentaje-cell"><?= round($porc_moi * 100, 1) ?>%</td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL</td>
                                    <td><?= $total_horas_distribucion ?></td>
                                    <td class="porcentaje-cell">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RESÚMENES FINANCIEROS SEPARADOS (MOD y MOI) -->
                <div class="grid-container">
                    <!-- RESUMEN FINANCIERO MOD -->
                    <div class="grid-item">
                        <h4 class="seccion-titulo">Resumen Financiero MOD</h4>
                        <div class="tabla-contenedor">
                            <table class="tabla-pequena">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor ($)</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total MOD</td>
                                        <td><?= number_format($tarjeta['tarj_total_mod'], 2) ?></td>
                                        <td><?= round($porc_mod * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Provisiones MOD</td>
                                        <td><?= number_format($tarjeta['tarj_total_provisiones'] * $porc_mod, 2) ?></td>
                                        <td><?= round($porc_mod * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Aporte Patronal MOD</td>
                                        <td><?= number_format($tarjeta['tarj_total_aporte_patronal'] * $porc_mod, 2) ?></td>
                                        <td><?= round($porc_mod * 100, 1) ?>%</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Subtotal MOD</strong></td>
                                        <td><strong>
                                                <?= number_format(
                                                    $tarjeta['tarj_total_mod'] +
                                                    ($tarjeta['tarj_total_provisiones'] * $porc_mod) +
                                                    ($tarjeta['tarj_total_aporte_patronal'] * $porc_mod),
                                                    2
                                                )
                                                    ?>
                                            </strong></td>
                                        <td><strong><?= round($porc_mod * 100, 1) ?>%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RESUMEN FINANCIERO MOI -->
                    <div class="grid-item">
                        <h4 class="seccion-titulo">Resumen Financiero MOI</h4>
                        <div class="tabla-contenedor">
                            <table class="tabla-pequena">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Valor ($)</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total MOI</td>
                                        <td><?= number_format($tarjeta['tarj_total_moi'], 2) ?></td>
                                        <td><?= round($porc_moi * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Provisiones MOI</td>
                                        <td><?= number_format($tarjeta['tarj_total_provisiones'] * $porc_moi, 2) ?></td>
                                        <td><?= round($porc_moi * 100, 1) ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Aporte Patronal MOI</td>
                                        <td><?= number_format($tarjeta['tarj_total_aporte_patronal'] * $porc_moi, 2) ?></td>
                                        <td><?= round($porc_moi * 100, 1) ?>%</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Subtotal MOI</strong></td>
                                        <td><strong>
                                                <?= number_format(
                                                    $tarjeta['tarj_total_moi'] +
                                                    ($tarjeta['tarj_total_provisiones'] * $porc_moi) +
                                                    ($tarjeta['tarj_total_aporte_patronal'] * $porc_moi),
                                                    2
                                                )
                                                    ?>
                                            </strong></td>
                                        <td><strong><?= round($porc_moi * 100, 1) ?>%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>





                <!-- DISTRIBUCIÓN DE HORAS - TABLA COMPLETA -->
                <div class="grid-item grid-full">
                    <h4 class="seccion-titulo">Distribución de Horas por Empleado</h4>
                    <div class="tabla-contenedor">
                        <table>
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Total H.</th>
                                    <th>H. MOD</th>
                                    <th>H. MOI</th>
                                    <th>$/Hora</th>
                                    <th>Total MOD ($)</th>
                                    <th>Total MOI ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_mod = 0;
                                $total_moi = 0;

                                foreach ($detalles as $detalle):
                                    $horas_mod = $detalle['tdet_horas_mod'];
                                    $horas_moi = $detalle['tdet_horas_moi'] > 0 ? $detalle['tdet_horas_moi'] : 0;
                                    $total_horas_empl = $horas_mod + $horas_moi;

                                    $total_mod += $detalle['tdet_total_mod'];
                                    $total_moi += $detalle['tdet_total_moi'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detalle['empl_nombre']) ?></td>
                                        <td><?= $total_horas_empl ?></td>
                                        <td><?= $horas_mod ?></td>
                                        <td><?= $horas_moi ?></td>
                                        <td><?= number_format($detalle['tdet_valor_hora'], 2) ?></td>
                                        <td><?= number_format($detalle['tdet_total_mod'], 2) ?></td>
                                        <td><?= number_format($detalle['tdet_total_moi'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td>TOTALES</td>
                                    <td><?= $total_horas ?></td>
                                    <td><?= $total_horas_mod ?></td>
                                    <td><?= $total_horas_moi ?></td>
                                    <td><?= count($detalles) > 0 ? number_format($total_salario_hora / count($detalles), 2) : '0.00' ?>
                                    </td>
                                    <td><?= number_format($total_mod, 2) ?></td>
                                    <td><?= number_format($total_moi, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    <?php endforeach; ?>