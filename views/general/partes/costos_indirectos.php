<?php
// --- INCLUDES Y LÓGICA INICIAL --- (Sin cambios)
require_once __DIR__ . '/../../../models/empresa.model.php';
require_once __DIR__ . '/../../../models/cif.model.php';
$empresaModel = new Empresa($pdo);
$cifModel = new CifModel($pdo);
$usuario_id = $_SESSION['usuario']['id'];
$empresas = $empresaModel->listarPorUsuario($usuario_id);

// --- LÓGICA PARA VER HISTORIAL ---
$empresa_id_filtro = $_GET['empresa_id'] ?? ($empresas[0]['emp_id'] ?? null);
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$cif_aplicados = [];
if ($empresa_id_filtro) {
    $cif_aplicados = $cifModel->listarPorFechaYEmpresa($empresa_id_filtro, $fecha_inicio, $fecha_fin, $usuario_id);
}
?>

<style>
    /* ----- FUENTE Y COLORES BASE ----- */
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

    :root {
        --primary-color: #00796B; /* Verde principal */
        --primary-dark: #004D40;
        --secondary-color: #009688;
        --background-light: #F5F7FA;
        --panel-bg: #FFFFFF;
        --text-dark: #2c3e50;
        --text-light: #596e79;
        --border-color: #E0E0E0;
        --shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
        --shadow-hover: 0 6px 25px rgba(0, 0, 0, 0.1);
    }

    /* ----- ESTRUCTURA GENERAL ----- */
    .layout-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        align-items: start;
        font-family: 'Roboto', sans-serif;
    }

    .main-content { grid-column: 1 / 2; }
    .sidebar-right { 
        grid-column: 2 / 3; 
        position: sticky; 
        top: 20px; 
    }

    /* ----- PANELES PRINCIPALES (TARJETAS) ----- */
    .ci-panel {
        background: var(--panel-bg);
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 30px;
        margin-bottom: 30px;
        transition: box-shadow 0.3s ease;
    }

    .ci-panel:hover {
        box-shadow: var(--shadow-hover);
    }

    .ci-panel h4 {
        color: var(--primary-dark);
        font-size: 1.5em;
        margin-top: 0;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--primary-color);
        display: inline-block;
    }
    
    /* ----- ESTILOS DE FORMULARIO ----- */
    .form-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 0.9em;
    }

    .form-group input[type="number"],
    .form-group input[type="date"],
    .form-group select {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: #fdfdfd;
        color: var(--text-dark);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.2);
    }

    fieldset {
        border: 1px solid var(--border-color);
        padding: 20px;
        border-radius: 8px;
        margin-top: 15px;
        background-color: var(--background-light);
    }

    fieldset legend {
        font-weight: 700;
        color: var(--primary-color);
        padding: 0 10px;
    }

    hr {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 25px 0;
    }
    
    /* ----- SECCIÓN DE RESULTADOS (DENTRO DEL FORMULARIO) ----- */
    .data-pair {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 0.95em;
    }
    .data-pair span { color: var(--text-light); }
    .data-pair strong { color: var(--text-dark); font-weight: 500; }
    
    /* ----- HISTORIAL DE CIF ----- */
    .historial-container {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .cif-card {
        border: 1px solid var(--border-color);
        border-left: 5px solid var(--secondary-color);
        border-radius: 12px;
        overflow: hidden;
        background: var(--panel-bg);
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .cif-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow);
    }

    .cif-card-header {
        background: linear-gradient(to right, #e0f2f1, #b2dfdb);
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        color: var(--primary-dark);
    }

    .cif-card-body {
        padding: 25px;
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 35px;
    }

    .cif-card-body h5 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.1em;
        color: var(--primary-dark);
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }

    .desglose-otros-cif {
        font-size: 0.85em;
        color: #777;
        padding: 10px 15px;
        border-left: 3px solid #e0f2f1;
        margin: 10px 0 10px 5px;
        background-color: #fafafa;
    }

    .tasa-pred-box {
        background-color: #e8f5e9;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 20px;
        border: 1px solid #c8e6c9;
    }
    .tasa-pred-box .tasa-formula {
        font-size: 1.1em;
        font-weight: 500;
        color: #388e3c;
    }
    .tasa-pred-box .tasa-valor {
        font-size: 1.4em;
        font-weight: 700;
        color: #1b5e20;
        margin-top: 5px;
    }

    .total-cif-aplicado {
        color: #004D40;
        background-color: #dcedc8;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
    }
    
    /* ----- BOTONES ----- */
    button, .btn-generar {
        background: linear-gradient(145deg, var(--secondary-color), var(--primary-color));
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 1em;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 121, 107, 0.2);
    }

    button:hover, .btn-generar:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 121, 107, 0.3);
    }
    
</style>


<div class="layout-grid">
    <main class="main-content">
        <div class="ci-panel">
            <h4>1. Calcular Tasa Predeterminada de CIF</h4>
            <form id="tasa-form">
                <div class="form-group" style="max-width: 300px; margin-bottom: 20px;">
                    <label>Producción Anual (Base en Unidades)</label>
                    <input type="number" id="produccion_anual" name="produccion_anual" class="manual-input" required>
                </div>
                <hr>

                <div style="display: flex; gap: 40px;">
                    <div style="flex: 1;">
                        <div class="form-group">
                            <label>Materiales Indirectos</label>
                            <input type="number" step="0.01" id="materiales_indirectos" name="materiales_indirectos"
                                class="manual-input" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Mano de Obra Indirecta</label>
                            <input type="number" step="0.01" id="mano_obra_indirecta" name="mano_obra_indirecta"
                                class="manual-input"value="0" required>
                        </div>

                        <fieldset
                            style="border: 1px solid #e0e0e0; padding: 15px; border-radius: 5px; margin-top: 15px;">
                            <legend style="font-weight: bold; font-size: 0.9em;">Componentes de "Otros Costos Indirectos
                                de Fabricación"</legend>
                            <div class="form-grid">
                                <div class="form-group"><label>Depreciación</label><input type="number" step="0.01"
                                        name="depreciacion" class="manual-input otros-cif-input" value="0"></div>
                                <div class="form-group"><label>Seguros de Planta</label><input type="number" step="0.01"
                                        name="seguros" class="manual-input otros-cif-input" value="0"></div>
                                <div class="form-group"><label>Combustibles</label><input type="number" step="0.01"
                                        name="combustibles" class="manual-input otros-cif-input" value="0"></div>
                                <div class="form-group"><label>Servicios básicos</label><input type="number" step="0.01"
                                        name="servicios_basicos" class="manual-input otros-cif-input" value="0"></div>
                                <div class="form-group"><label>Arriendo de fábrica</label><input type="number"
                                        step="0.01" name="arriendo" class="manual-input otros-cif-input" value="0"></div>
                                <div class="form-group"><label>Otros CIF</label><input type="number" step="0.01"
                                        name="otros_cif" class="manual-input otros-cif-input" value="0"></div>
                            </div>
                        </fieldset>
                    </div>

                    <div style="flex: 1; border-left: 2px solid #f0f0f0; padding-left: 30px;">
                        <h5 style="margin-top:0;">Presupuesto Mensual</h5>
                        <div class="data-pair" style="margin-top: 25px;">
                            <span>Materiales Indirectos:</span>
                            <strong id="res_mat_indirectos">$0.00</strong>
                        </div>
                        <div class="data-pair">
                            <span>Mano de Obra Indirecta:</span>
                            <strong id="res_mo_indirecta">$0.00</strong>
                        </div>
                        <div class="data-pair">
                            <span>Otros Costos Indirectos de Fabricación:</span>
                            <strong id="res_otros_cif">$0.00</strong>
                        </div>
                        <hr>
                        <div class="data-pair" style="font-size: 1.1em;">
                            <strong>PRESUPUESTO TOTAL (Mensual):</strong>
                            <strong id="res_total_mensual" style="color: #00796b;">$0.00</strong>
                        </div>
                        <div class="data-pair" style="font-size: 1.1em; margin-top: 20px;">
                            <strong>PRESUPUESTO TOTAL (Anual):</strong>
                            <strong id="res_total_anual" style="color: #004d40;">$0.00</strong>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="ci-panel">
            <h4>Historial de CIF Aplicados</h4>
            <form method="GET" class="filter-form" style="display: flex; gap: 10px; margin-bottom: 20px;">
                <input type="hidden" name="vista" value="costos_indirectos">
                <select name="empresa_id" onchange="this.form.submit()">
                    <option value="">-- Todas las Empresas --</option>
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

            <div class="historial-container" style="display: flex; flex-direction: column; gap: 20px;">
                <?php if (empty($cif_aplicados)): ?>
                    <p>No hay registros para mostrar.</p>
                <?php else:
                    foreach ($cif_aplicados as $cif):
                        // Calculamos los totales para la visualización
                        $total_otros_cif_historial = $cif['costo_depreciacion'] + $cif['costo_seguros'] + $cif['costo_combustibles'] + $cif['costo_servicios_basicos'] + $cif['costo_arriendo'] + $cif['costo_otros_cif'];
                        $total_mensual_historial = $cif['costo_mat_indirectos'] + $cif['costo_mo_indirecta'] + $total_otros_cif_historial;
                        $total_anual_historial = $total_mensual_historial * 12;
                        ?>
                        <div class="cif-card" style="border: 1px solid #e0e0e0; border-radius: 8px;">
                            <div class="cif-card-header"
                                style="background-color: #f7f7f7; padding: 10px 15px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; font-weight: bold;">
                                <span>Producto: <?= htmlspecialchars($cif['pro_nombre']) ?></span>
                                <span>Fecha Aplicación: <?= date('d/m/Y', strtotime($cif['cif_fecha_aplicada'])) ?></span>
                            </div>
                            <div class="cif-card-body"
                                style="padding: 20px; display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px;">
                                <div>
                                    <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px;">Presupuesto
                                        Mensual Utilizado</h5>
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; padding: 5px 0;">
                                        <span>Materiales Indirectos:</span>
                                        <strong>$<?= number_format($cif['costo_mat_indirectos'], 2) ?></strong>
                                    </div>
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; padding: 5px 0;">
                                        <span>Mano de Obra Indirecta:</span>
                                        <strong>$<?= number_format($cif['costo_mo_indirecta'], 2) ?></strong>
                                    </div>
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; padding: 5px 0;">
                                        <span>Otros Costos Indirectos de Fabricación:</span>
                                        <strong>$<?= number_format($total_otros_cif_historial, 2) ?></strong>
                                    </div>
                                    <div class="desglose-otros-cif"
                                        style="font-size: 0.8em; color: #666; padding: 5px 0 5px 20px; border-left: 2px solid #f0f0f0; margin-left: 10px;">
                                        <div style="display:flex; justify-content: space-between;"><span>Depreciación:</span>
                                            <span>$<?= number_format($cif['costo_depreciacion'], 2) ?></span></div>
                                        <div style="display:flex; justify-content: space-between;"><span>Seguros:</span>
                                            <span>$<?= number_format($cif['costo_seguros'], 2) ?></span></div>
                                        <div style="display:flex; justify-content: space-between;"><span>Combustibles:</span>
                                            <span>$<?= number_format($cif['costo_combustibles'], 2) ?></span></div>
                                        <div style="display:flex; justify-content: space-between;"><span>Serv. básicos:</span>
                                            <span>$<?= number_format($cif['costo_servicios_basicos'], 2) ?></span></div>
                                        <div style="display:flex; justify-content: space-between;"><span>Arriendo:</span>
                                            <span>$<?= number_format($cif['costo_arriendo'], 2) ?></span></div>
                                        <div style="display:flex; justify-content: space-between;"><span>Otros CIF:</span>
                                            <span>$<?= number_format($cif['costo_otros_cif'], 2) ?></span></div>
                                    </div>
                                    <hr style="margin: 10px 0;">
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em;">
                                        <span>TOTAL PRESUPUESTO MENSUAL:</span>
                                        <span style="color: #00796b;">$<?= number_format($total_mensual_historial, 2) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px;">Detalle de
                                        Aplicación</h5>
                                    <div
                                        style="background-color: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                                        <div style="font-size: 0.9em;">Tasa Predeterminada =</div>
                                        <div style="font-size: 1em; font-weight: bold;">
                                            <span>$<?= number_format($total_anual_historial, 2) ?></span> /
                                            <span><?= $cif['base_produccion_anual'] ?> Unds.</span>
                                        </div>
                                        <div style="font-size: 1.2em; font-weight: bold; color: #1b5e20; margin-top: 5px;">
                                            <?= number_format($cif['cif_tasa_utilizada'], 4) ?></div>
                                    </div>
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; padding: 5px 0;">
                                        <span>Unidades Producidas (Registro):</span>
                                        <strong><?= $cif['cif_unidades_producidas'] ?></strong>
                                    </div>
                                    <hr style="margin: 10px 0;">
                                    <div class="data-pair"
                                        style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em;">
                                        <span>TOTAL CIF APLICADO:</span>
                                        <span
                                            style="color: #004d40; background-color: #e0f2f1; padding: 3px 8px; border-radius: 5px;">$<?= number_format($cif['cif_total_aplicado'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>
        </div>

    </main>

    <aside class="sidebar-right">
        <div class="ci-panel">
            <h4>2. Generar CIF Aplicado</h4>
            <form id="cif-aplicado-form" method="POST" action="../../controllers/cif.controller.php">

                <div class="form-group">
                    <label for="fecha_aplicada">Fecha de Aplicación</label>
                    <input type="date" id="fecha_aplicada" name="fecha_aplicada" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Tasa Predeterminada a Usar</label>
                    <input type="text" id="tasa_predeterminada_display" placeholder="Se calcula a la izquierda"
                        disabled>
                </div>
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
                    <label for="unidades_producidas">Unidades Producidas</label>
                    <input type="number" id="unidades_producidas" name="unidades_producidas" required>
                </div>

                <input type="hidden" id="hidden_tasa_predeterminada" name="tasa_predeterminada">
                <input type="hidden" id="hidden_produccion_anual" name="produccion_anual">
                <input type="hidden" id="hidden_materiales_indirectos" name="materiales_indirectos">
                <input type="hidden" id="hidden_mano_obra_indirecta" name="mano_obra_indirecta">
                <input type="hidden" id="hidden_depreciacion" name="depreciacion">
                <input type="hidden" id="hidden_seguros" name="seguros">
                <input type="hidden" id="hidden_combustibles" name="combustibles">
                <input type="hidden" id="hidden_servicios_basicos" name="servicios_basicos">
                <input type="hidden" id="hidden_arriendo" name="arriendo">
                <input type="hidden" id="hidden_otros_cif" name="otros_cif">

                <button type="submit" class="btn-generar">Generar y Guardar CIF</button>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // --- LÓGICA PARA CÁLCULO DE TASA EN TIEMPO REAL ---
                const formInputs = document.querySelectorAll('#tasa-form .manual-input');

                function calcularYActualizar() {
                    // 1. CALCULAR LOS COSTOS
                    let totalOtrosCIF = 0;
                    document.querySelectorAll('.otros-cif-input').forEach(input => {
                        totalOtrosCIF += parseFloat(input.value) || 0;
                    });
                    const matIndirectos = parseFloat(document.getElementById('materiales_indirectos').value) || 0;
                    const moIndirecta = parseFloat(document.getElementById('mano_obra_indirecta').value) || 0;
                    const presupuestoMensual = matIndirectos + moIndirecta + totalOtrosCIF;
                    const presupuestoAnual = presupuestoMensual * 12;
                    const produccionAnual = parseFloat(document.getElementById('produccion_anual').value) || 0;
                    let tasa = produccionAnual > 0 ? presupuestoAnual / produccionAnual : 0;

                    // 2. ACTUALIZAR LA COLUMNA DE RESULTADOS VISIBLES
                    document.getElementById('res_mat_indirectos').textContent = '$' + matIndirectos.toFixed(2);
                    document.getElementById('res_mo_indirecta').textContent = '$' + moIndirecta.toFixed(2);
                    document.getElementById('res_otros_cif').textContent = '$' + totalOtrosCIF.toFixed(2);
                    document.getElementById('res_total_mensual').textContent = '$' + presupuestoMensual.toFixed(2);
                    document.getElementById('res_total_anual').textContent = '$' + presupuestoAnual.toFixed(2);

                    // Actualizar el display de la tasa en el ASIDE
                    document.getElementById('tasa_predeterminada_display').value = tasa.toFixed(4);

                    // 3. ACTUALIZAR LOS CAMPOS OCULTOS PARA GUARDAR
                    document.getElementById('hidden_tasa_predeterminada').value = tasa.toFixed(4);
                    document.getElementById('hidden_produccion_anual').value = produccionAnual;
                    document.getElementById('hidden_materiales_indirectos').value = matIndirectos;
                    document.getElementById('hidden_mano_obra_indirecta').value = moIndirecta;
                    document.querySelectorAll('#tasa-form .otros-cif-input').forEach(input => {
                        document.getElementById(`hidden_${input.name}`).value = parseFloat(input.value) || 0;
                    });
                }

                formInputs.forEach(input => input.addEventListener('input', calcularYActualizar));

                // --- LÓGICA PARA CARGAR PRODUCTOS EN EL ASIDE ---
                const empresaSelect = document.getElementById('empresa_id_aside');
                const productoSelect = document.getElementById('producto_id');

                empresaSelect.addEventListener('change', function () {
                    const empresaId = this.value;
                    productoSelect.innerHTML = '<option value="">Cargando...</option>';

                    if (!empresaId) {
                        productoSelect.innerHTML = '<option value="">-- Primero seleccione una empresa --</option>';
                        return;
                    }

                    fetch(`./ajax/get_productos.php?empresa_id=${empresaId}`)
                        .then(response => {
                            if (!response.ok) throw new Error(`Error: ${response.status}`);
                            return response.json();
                        })
                        .then(data => {
                            productoSelect.innerHTML = '<option value="">-- Seleccione un producto --</option>';
                            if (data.length === 0) {
                                productoSelect.innerHTML = '<option value="">-- No hay productos --</option>';
                            } else {
                                data.forEach(producto => {
                                    const option = new Option(producto.pro_nombre, producto.pro_id);
                                    productoSelect.add(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar productos:', error);
                            productoSelect.innerHTML = '<option value="">Error al cargar</option>';
                        });
                });
            });
        </script>