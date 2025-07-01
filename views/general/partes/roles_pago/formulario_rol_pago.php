<?php
require_once __DIR__ . '/../../../../models/empresa.model.php';
require_once __DIR__ . '/../../../../models/empleado.model.php';
require_once __DIR__ . '/../../../../models/sueldo.model.php';

$usuario_id = $_SESSION['usuario']['id'];
$empresa_modelo = new Empresa($pdo);
$empleado_modelo = new Empleado($pdo);
$sueldo_modelo = new Sueldo($pdo);

// Obtener empresas del usuario
$empresas = $empresa_modelo->listarPorUsuario($usuario_id);

// Obtener empresa seleccionada, si existe
$empresa_id_seleccionada = $_GET['empresa_id'] ?? '';
?>
<style>
    .form-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 30px auto;
        background-color: #ffffff;
        padding: 20px 25px;
        border-radius: 10px;
        max-width: 600px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .form-container label {
        margin-right: 15px;
        font-weight: bold;
        font-size: 16px;
        color: #2c3e50;
    }

    .form-container select {
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        background-color: #f9f9f9;
        transition: border 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
        min-width: 250px;
    }

    .form-container select:focus {
        outline: none;
        border-color: #0077cc;
        box-shadow: 0 0 0 3px rgba(0, 119, 204, 0.15);
        background-color: #ffffff;
    }

    .form-container option {
        font-size: 14px;
    }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        margin: 20px;
    }

    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 6px 8px;
        text-align: center;
        font-size: 14px;
    }

    th {
        background-color: #2c3e50;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    input[type="number"],
    input[type="text"] {
        width: 80px;
        padding: 4px 6px;
        font-size: 13px;
        text-align: right;
        border: 1px solid #ccc;
        border-radius: 3px;
        background-color: #fefefe;
    }

    input[readonly] {
        background-color: #f0f0f0;
        color: #555;
    }

    .btn-submit {
        display: block;
        margin: 30px auto 10px;
        padding: 10px 30px;
        background-color: #0077cc;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    .btn-submit:hover {
        background-color: #005fa3;
    }


    /* Estilos mejorados para el componente */
    .horas-extra-container {
        position: relative;
        display: inline-block;
    }

    .btn-horas-extra {
        background-color: #4a6fa5;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .btn-horas-extra:hover {
        background-color: #3a5a8f;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .badge-horas {
        background-color: #2c3e50;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
    }

    .modal-horas-extra {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
    }

    .modal-content {
        background-color: #ffffff;
        margin: 10% auto;
        padding: 25px;
        border-radius: 12px;
        width: 320px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-content h4 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 18px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .valor-hora-info {
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
    }

    .input-group {
        margin: 15px 0;
    }

    .input-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        color: #555;
        font-weight: 600;
    }

    .input-horas {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border 0.3s;
    }

    .input-horas:focus {
        border-color: #4a6fa5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
    }

    .total-group {
        margin: 20px 0;
        padding: 15px;
        background-color: #f1f7fe;
        border-radius: 8px;
    }

    .total-line {
        display: flex;
        justify-content: space-between;
        font-size: 15px;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 15px;
    }

    .btn-cerrar {
        background-color: #4a6fa5;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cerrar:hover {
        background-color: #3a5a8f;
    }

    .btn-cerrar i {
        font-size: 12px;
    }
</style>

<!-- FORMULARIO PARA SELECCIONAR EMPRESA -->
<form method="GET" action="dashboard.php" class="form-container">
    <input type="hidden" name="vista" value="mano_de_obra">
    <input type="hidden" name="view" value="formulario_rol_pago">

    <label for="empresa_id">Selecciona una empresa:</label>

    <select name="empresa_id" id="empresa_id" onchange="this.form.submit()" required>
        <option value="" disabled selected hidden>-- Elegir empresa --</option>
        <?php foreach ($empresas as $emp): ?>
            <option value="<?= $emp['emp_id']; ?>" <?= ($empresa_id_seleccionada == $emp['emp_id']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($emp['emp_nombre']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>
<?php
// Verificar si se ha seleccionado una empresa
if (empty($empresa_id_seleccionada)) {
    echo "<p style='font-size: 24px; font-weight: bold; color:rgb(29, 96, 66); text-align: center;'>
            🚨 <strong>SELECCIONA UNA DE TUS EMPRESAS</strong> 🚨<br>
            Para generar tu rol de pagos.
          </p>";

} else {
    // Si se seleccionó una empresa, mostrar tabla de empleados
    $empleados = $empleado_modelo->listarPorEmpresa($empresa_id_seleccionada);

    // Obtener parámetro de aporte personal
    $stmt = $pdo->prepare("SELECT par_valor FROM tbl_parametros WHERE par_nombre = 'aporte_personal'");
    $stmt->execute();
    $aporte_personal_pct = $stmt->fetchColumn() ?: 0;

    ?>


    <!-- FORMULARIO DE ROL DE PAGO -->
    <h2>Generar Rol de Pago</h2>

    <form method="POST" action="guardar_rol_pago.php">
        <input type="hidden" name="empresa_id" value="<?php echo $empresa_id_seleccionada; ?>">
        <label for="rol_mes">Mes del rol (YYYY-MM):</label>
        <input type="month" name="rol_mes" required>

        <label for="rol_fecha_emision">Fecha de emisión:</label>
        <input type="date" name="rol_fecha_emision" required>
        <table border="1" cellpadding="5" cellspacing="0"></table>

        <form action="guardar_rol_pago.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Sueldo</th>
                        <th>Bono</th>
                        <th>Horas Extra</th>
                        <th>Comisiones</th>
                        <th>Anticipo</th>
                        <th>Aporte Personal (<?php echo $aporte_personal_pct; ?>%)</th>
                        <th>Horas Mano de Obra Directa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $index => $empl):
                        $sueldo = $empl['sue_valor'];
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($empl['empl_nombre']) ?></td>
                            <td>
                                <?= number_format($sueldo, 2) ?>
                                <input type="hidden" name="empleados[<?= $empl['empl_id'] ?>][sueldo]" value="<?= $sueldo ?>">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="empleados[<?= $empl['empl_id'] ?>][bono]" value="0"
                                    oninput="calcularAporte(<?= $empl['empl_id'] ?>)">
                            </td>
                            <td>
                                <div class="horas-extra-container">
                                    <button type="button" class="btn-horas-extra"
                                        onclick="toggleHorasExtra(<?= $empl['empl_id'] ?>)">
                                        <span class="badge-horas">$<span
                                                id="badge-valor-<?= $empl['empl_id'] ?>">0.00</span></span>
                                        Configurar
                                    </button>

                                    <div id="horas-extra-modal-<?= $empl['empl_id'] ?>" class="modal-horas-extra">
                                        <div class="modal-content">
                                            <h4>Registro de Horas Extra - <?= htmlspecialchars($empl['empl_nombre']) ?></h4>
                                            <div class="valor-hora-info">
                                                <span>Sueldo hora:</span>
                                                <strong id="valor-hora-<?= $empl['empl_id'] ?>">
                                                    <?= number_format($sueldo / 160, 2) ?>
                                                </strong>
                                            </div>

                                            <div class="input-group">
                                                <label>Horas Diurnas (1.5x):</label>
                                                <input type="number" min="0" step="0.5"
                                                    oninput="calcularHorasExtra(<?= $empl['empl_id'] ?>)"
                                                    id="horas-diurnas-<?= $empl['empl_id'] ?>" class="input-horas" value="0">
                                            </div>

                                            <div class="input-group">
                                                <label>Horas Nocturnas/Fin Semana (2x):</label>
                                                <input type="number" min="0" step="0.5"
                                                    oninput="calcularHorasExtra(<?= $empl['empl_id'] ?>)"
                                                    id="horas-nocturnas-<?= $empl['empl_id'] ?>" class="input-horas" value="0">
                                            </div>

                                            <div class="total-group">
                                                <div class="total-line">
                                                    <span>Total Horas Extra:</span>
                                                    <strong id="total-horas-extra-<?= $empl['empl_id'] ?>">0.00</strong>
                                                </div>
                                            </div>

                                            <div class="modal-actions">
                                                <button type="button" onclick="toggleHorasExtra(<?= $empl['empl_id'] ?>)"
                                                    class="btn-cerrar">
                                                    <i class="fas fa-check"></i> Aplicar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="empleados[<?= $empl['empl_id'] ?>][horas_extra]"
                                        id="input-horas-extra-<?= $empl['empl_id'] ?>" value="0">
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="empleados[<?= $empl['empl_id'] ?>][comisiones]" value="0"
                                    oninput="calcularAporte(<?= $empl['empl_id'] ?>)">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="empleados[<?= $empl['empl_id'] ?>][anticipo]" value="0"
                                    oninput="calcularAporte(<?= $empl['empl_id'] ?>)">
                            </td>
                            <td>
                                <input type="text" readonly id="aporte_<?= $empl['empl_id'] ?>"
                                    name="empleados[<?= $empl['empl_id'] ?>][aporte]"
                                    value="<?= number_format(($sueldo * $aporte_personal_pct / 100), 2) ?>">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="empleados[<?= $empl['empl_id'] ?>][horas_mod]"
                                    value="160" min="0" max="160" required oninput="calcularHorasMoi(<?= $empl['empl_id'] ?>)">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn-submit">Guardar Rol de Pago</button>
        </form>


        <script>

            function calcularAporte(id) {
                const sueldo = parseFloat(document.querySelector(`input[name="empleados[${id}][sueldo]"]`).value) || 0;
                const bono = parseFloat(document.querySelector(`input[name="empleados[${id}][bono]"]`).value) || 0;
                const horas_extra = parseFloat(document.querySelector(`input[name="empleados[${id}][horas_extra]"]`).value) || 0;
                const comisiones = parseFloat(document.querySelector(`input[name="empleados[${id}][comisiones]"]`).value) || 0;

                const total = sueldo + bono + horas_extra + comisiones;
                const porcentaje = <?php echo $aporte_personal_pct; ?>;
                const aporte = (total * porcentaje / 100).toFixed(2);

                document.getElementById(`aporte_${id}`).value = aporte;
            }


            function toggleHorasExtra(emplId) {
                const modal = document.getElementById(`horas-extra-modal-${emplId}`);
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                } else {
                    modal.style.display = 'block';
                    // Enfocar el primer input al abrir
                    document.getElementById(`horas-diurnas-${emplId}`).focus();
                }
            }

            // Función para calcular el valor de las horas extra
            function calcularHorasExtra(emplId) {
                const sueldo = parseFloat(document.querySelector(`input[name="empleados[${emplId}][sueldo]"]`).value) || 0;
                const valorHora = sueldo / 160;

                const horasDiurnas = parseFloat(document.getElementById(`horas-diurnas-${emplId}`).value) || 0;
                const horasNocturnas = parseFloat(document.getElementById(`horas-nocturnas-${emplId}`).value) || 0;

                // Cálculo según normativa laboral
                const totalDiurnas = horasDiurnas * valorHora * 1.5; // 50% extra
                const totalNocturnas = horasNocturnas * valorHora * 2; // 100% extra

                const totalHorasExtra = totalDiurnas + totalNocturnas;

                // Actualizar visualización en el modal
                document.getElementById(`total-horas-extra-${emplId}`).textContent = totalHorasExtra.toFixed(2);

                // Actualizar badge en el botón
                document.getElementById(`badge-valor-${emplId}`).textContent = totalHorasExtra.toFixed(2);

                // Actualizar campo oculto que se enviará al formulario
                document.getElementById(`input-horas-extra-${emplId}`).value = totalHorasExtra.toFixed(2);

                // Recalcular aportes
                calcularAporte(emplId);
            }

            // Cerrar modal al hacer clic fuera del contenido
            window.onclick = function (event) {
                if (event.target.className === 'modal-horas-extra') {
                    event.target.style.display = 'none';
                }
            }

            // Inicializar badges con valor 0 para cada empleado
            document.addEventListener('DOMContentLoaded', function () {
                <?php foreach ($empleados as $empl): ?>
                    document.getElementById(`badge-valor-<?= $empl['empl_id'] ?>`).textContent = '0.00';
                <?php endforeach; ?>
            });
        </script>




    <?php } ?>