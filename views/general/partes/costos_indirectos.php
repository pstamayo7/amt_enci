<?php
// /views/general/partes/costos_indirectos.php

// --- INCLUDES Y LÓGICA INICIAL ---
require_once __DIR__ . '/../../../models/empresa.model.php';
require_once __DIR__ . '/../../../models/cif.model.php';

$empresaModel = new Empresa($pdo);
$cifModel = new CifModel($pdo);

$usuario_id = $_SESSION['usuario']['id'];
$empresas = $empresaModel->listarPorUsuario($usuario_id);

// --- LÓGICA PARA CÁLCULO DE TASA ---
$tasa_predeterminada = 0;
$cif_presupuestados_anual = 0;
$base_presupuestada_anual = 0;
$empresa_id_tasa = $_POST['empresa_id_tasa'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calcular_tasa'])) {
    // ... (la lógica de cálculo de tasa que ya teníamos)
    $materiales_indirectos = (float)($_POST['materiales_indirectos'] ?? 0);
    $mano_obra_indirecta = (float)($_POST['mano_obra_indirecta'] ?? 0);
    $total_otros_cif = (float)($_POST['depreciacion'] ?? 0) + (float)($_POST['seguros'] ?? 0) + (float)($_POST['combustibles'] ?? 0) + (float)($_POST['servicios_basicos'] ?? 0) + (float)($_POST['arriendo'] ?? 0) + (float)($_POST['otros_cif'] ?? 0);
    $base_presupuestada_anual = (float)($_POST['produccion_anual'] ?? 0);
    $cif_presupuestados_mensual = $materiales_indirectos + $mano_obra_indirecta + $total_otros_cif;
    $cif_presupuestados_anual = $cif_presupuestados_mensual * 12;
    if ($base_presupuestada_anual > 0) {
        $tasa_predeterminada = $cif_presupuestados_anual / $base_presupuestada_anual;
    }
}

// --- LÓGICA PARA VER HISTORIAL DE CIF APLICADOS ---
$empresa_id_filtro = $_GET['empresa_id'] ?? ($empresas[0]['emp_id'] ?? null);
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$cif_aplicados = [];
if($empresa_id_filtro) {
    $cif_aplicados = $cifModel->listarPorFechaYEmpresa($empresa_id_filtro, $fecha_inicio, $fecha_fin, $usuario_id);
}

?>

<style>
    .layout-grid { display: grid; grid-template-columns: 1fr 350px; gap: 25px; }
    .main-content { grid-column: 1 / 2; }
    .sidebar-right { grid-column: 2 / 3; }
    .ci-panel { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
    .ci-panel h3, .ci-panel h4 { margin-top: 0; color: #00796b; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
    .btn-calcular { background-color: #00796b; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 10px; }
    .resultado-tasa { background-color: #e0f2f1; border-left: 5px solid #00796b; padding: 15px; margin-top: 15px; border-radius: 5px; }
    #cif-aplicado-form label { margin-top: 10px; }
</style>

<div class="layout-grid">
    <main class="main-content">
        <div class="ci-panel">
            <h4>1. Calcular Tasa Predeterminada de CIF</h4>
            <form method="POST" action="dashboard.php?vista=costos_indirectos">
                <p>Completa los costos mensuales y la producción anual para obtener la tasa.</p>
                <button type="submit" name="calcular_tasa" class="btn-calcular">Calcular Tasa</button>
            </form>
             <?php if ($tasa_predeterminada > 0): ?>
            <div class="resultado-tasa">
                <strong>Tasa Calculada:</strong> <?= number_format($tasa_predeterminada, 4); ?> de CIF por unidad.
            </div>
            <?php endif; ?>
        </div>

        <div class="ci-panel">
            <h4>Historial de CIF Aplicados</h4>
            <form method="GET" class="filter-form">
                <input type="hidden" name="vista" value="costos_indirectos">
                <select name="empresa_id" onchange="this.form.submit()">
                    <?php foreach ($empresas as $emp): ?>
                    <option value="<?= $emp['emp_id']; ?>" <?= ($emp['emp_id'] == $empresa_id_filtro) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($emp['emp_nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>">
                <button type="submit">Filtrar</button>
            </form>
            <div class="tabla-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tasa Usada</th>
                            <th>Unidades</th>
                            <th>Total CIF Aplicado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cif_aplicados)): ?>
                        <tr><td colspan="5">No hay registros para el período y empresa seleccionados.</td></tr>
                        <?php else: ?>
                        <?php foreach ($cif_aplicados as $cif): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($cif['cif_fecha_generacion'])) ?></td>
                            <td><?= htmlspecialchars($cif['prod_nombre']) ?></td>
                            <td><?= number_format($cif['cif_tasa_utilizada'], 4) ?></td>
                            <td><?= $cif['cif_unidades_producidas'] ?></td>
                            <td>$<?= number_format($cif['cif_total_aplicado'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <aside class="sidebar-right">
        <div class="ci-panel">
            <h4>2. Generar CIF Aplicado</h4>
            <form id="cif-aplicado-form" method="POST" action="../controllers/cif.controller.php">
                <div class="form-group">
                    <label for="empresa_id_aside">Empresa</label>
                    <select id="empresa_id_aside" name="empresa_id" required>
                        <option value="">-- Seleccione --</option>
                         <?php foreach ($empresas as $emp): ?>
                        <option value="<?= $emp['emp_id']; ?>"><?= htmlspecialchars($emp['emp_nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="producto_id">Producto</label>
                    <select id="producto_id" name="producto_id" required>
                        <option value="">-- Primero seleccione una empresa --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tasa_predeterminada">Tasa Predeterminada a Usar</label>
                    <input type="number" step="0.0001" id="tasa_predeterminada" name="tasa_predeterminada" placeholder="Calcular primero la tasa" readonly required>
                </div>
                 <div class="form-group">
                    <label for="unidades_producidas">Unidades Producidas</o>
                    <input type="number" id="unidades_producidas" name="unidades_producidas" required>
                </div>
                <button type="submit" class="btn-calcular">Generar y Guardar CIF</button>
            </form>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const empresaSelect = document.getElementById('empresa_id_aside');
    const productoSelect = document.getElementById('producto_id');
    const tasaInput = document.getElementById('tasa_predeterminada');
    
    const tasaCalculada = <?= $tasa_predeterminada ?? 0 ?>;
    if (tasaCalculada > 0) {
        tasaInput.value = tasaCalculada.toFixed(4);
    }

    empresaSelect.addEventListener('change', function() {
        const empresaId = this.value;
        productoSelect.innerHTML = '<option value="">Cargando...</option>';

        if (!empresaId) {
            productoSelect.innerHTML = '<option value="">-- Primero seleccione una empresa --</option>';
            return;
        }

        // --- ATENCIÓN: LA CORRECCIÓN ESTÁ AQUÍ ---
        // Se usa una ruta absoluta desde la raíz de tu proyecto ('/amt_enci/')
        // para asegurar que el archivo siempre se encuentre.
        fetch(`/amt_enci/views/general/ajax/get_productos.php?empresa_id=${empresaId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error del servidor: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                productoSelect.innerHTML = '<option value="">-- Seleccione un producto --</option>';
                if (data.length === 0) {
                     productoSelect.innerHTML = '<option value="">-- No hay productos en esta empresa --</option>';
                } else {
                    data.forEach(producto => {
                        const option = document.createElement('option');
                        option.value = producto.pro_id;
                        option.textContent = producto.pro_nombre;
                        productoSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar productos:', error);
                productoSelect.innerHTML = '<option value="">Error al cargar productos</option>';
            });
    });
});
</script>