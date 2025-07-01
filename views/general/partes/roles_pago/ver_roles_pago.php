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

// Obtener filtros desde GET
$empresa_id_seleccionada = $_GET['empresa_id'] ?? '';
$filtro_mes = $_GET['filtro_mes'] ?? '';

// Generar condiciones dinámicamente
$condiciones = [];
$params = [];

if (!empty($empresa_id_seleccionada)) {
    $condiciones[] = "r.emp_id = ?";
    $params[] = $empresa_id_seleccionada;
}

if (!empty($filtro_mes)) {
    $condiciones[] = "r.rol_mes = ?";
    $params[] = $filtro_mes;
}

$whereSQL = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// Consulta final de roles de pago
$stmt_roles = $pdo->prepare("
    SELECT r.*, e.emp_nombre
    FROM tbl_rol_pago r
    JOIN tbl_empresa e ON r.emp_id = e.emp_id
    $whereSQL
    ORDER BY r.rol_mes DESC
");
$stmt_roles->execute($params);
$roles_pago = $stmt_roles->fetchAll();

// Obtener todos los meses únicos
$meses_stmt = $pdo->query("SELECT DISTINCT rol_mes FROM tbl_rol_pago ORDER BY rol_mes DESC");
$meses_disponibles = $meses_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
   

    .form-filtros-rol {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        margin-bottom: 2rem;
        background-color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .form-filtros-rol label {
        font-weight: bold;
    }

    .form-filtros-rol select {
        padding: 0.4rem 0.7rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        min-width: 180px;
    }

    h3, h4 {
        color: #2c3e50;
        margin-top: 2rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    th, td {
        text-align: center;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
    }

    th {
        background-color: #2c3e50;
        color: #fff;
        font-weight: 600;
    }

    tr:nth-child(even) td {
        background-color: #f4f6f9;
    }

    tr.totales {
        background-color: #dfe6e9;
        font-weight: bold;
    }

    hr {
        border: none;
        border-top: 1px solid #ccc;
        margin: 3rem 0;
    }

        .titulo-rol {
        font-size: 24px; /* Puedes ajustar a 28px, 32px, etc. */
        font-weight: bold;
        margin-top: 30px;
        color: #2c3e50;
        text-transform: uppercase;
        border-bottom: 2px solid #3498db;
        padding-bottom: 5px;
    }

    
    .tabla-nomina {
        width: 80%;
        border-collapse: collapse;
        margin: 20px auto;
        font-size: 14px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    .tabla-nomina th,
    .tabla-nomina td {
        padding: 6px 10px;
        text-align: center;
        border: 1px solid #ccc;
    }

    .tabla-nomina thead {
        background-color: #f4f4f4;
    }

    .tabla-nomina tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    .tabla-nomina tfoot {
        background-color: #eaeaea;
        font-weight: bold;
    }

    /* Colores por columnas */
    .col-iess { background-color: #e8f6f3; }
    .col-reserva { background-color: #fcf3cf; }
    .col-xiii { background-color: #fdebd0; }
    .col-xiv { background-color: #f9ebea; }
    .col-vacaciones { background-color: #ebf5fb; }
    .col-total { background-color: #e8daef; }


.tabla-nomina-personalizada {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px; /* Más pequeña que la tabla anterior */
    text-align: center;
}

.tabla-nomina-personalizada th, .tabla-nomina-personalizada td {
    border: 1px solid #ccc;
    padding: 4px;
}

/* Estilo para encabezado */
.tabla-nomina-personalizada th {
    background-color: #264653;
    color: white;
    font-size: 14px;
}

/* Colores por columna */
.tabla-nomina-personalizada td:nth-child(1) { background-color: #f1faee; } /* N° */
.tabla-nomina-personalizada td:nth-child(2) { background-color: #d8f3dc; } /* NOMBRE */
.tabla-nomina-personalizada td:nth-child(3) { background-color: #d8f3dc; } /* CARGO */
.tabla-nomina-personalizada td:nth-child(4) { background-color: #fefae0; } /* SUELDO */
.tabla-nomina-personalizada td:nth-child(5) { background-color: #ffe5b4; } /* BONO */
.tabla-nomina-personalizada td:nth-child(6) { background-color: #ffe5b4; } /* COMISIONES */
.tabla-nomina-personalizada td:nth-child(7) { background-color: #e0f7fa; } /* HORAS EXTRAS */
.tabla-nomina-personalizada td:nth-child(8) { background-color: #d9ed92; } /* TOTAL INGRESOS */
.tabla-nomina-personalizada td:nth-child(9) { background-color: #fcd5ce; } /* APORTE */
.tabla-nomina-personalizada td:nth-child(10) { background-color: #fcd5ce; } /* ANTICIPO */
.tabla-nomina-personalizada td:nth-child(11) { background-color: #fcd5ce; } /* TOTAL DEDUCCIONES */
.tabla-nomina-personalizada td:nth-child(12) { background-color: #caffbf; } /* LIQUIDO */
.tabla-nomina-personalizada td:nth-child(13) { background-color: #d8f3dc; } /* FIRMAS */

/* Fila de totales */
.tabla-nomina-personalizada tr.totales td {
    font-weight: bold;
    background-color: #a8dadc !important;
    color: #000;
}

/* Quitar colores alternos de filas */
.tabla-nomina-personalizada tbody tr:nth-child(even) {
    background-color: transparent;
}


</style>


<form method="GET" action="dashboard.php" class="form-filtros-rol">
    <input type="hidden" name="vista" value="mano_de_obra">
    <input type="hidden" name="view" value="ver_roles_pago">

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



<?php
foreach ($roles_pago as $rol) {
   echo "<h3 class='titulo-rol'>ROL DE PAGO: {$rol['emp_nombre']} - {$rol['rol_mes']}</h3>";


    // Cargar detalle empleados
    $stmt_det = $pdo->prepare("
        SELECT d.*, e.empl_nombre
        FROM tbl_rol_pago_detalle d
        JOIN tbl_empleado e ON d.empl_id = e.empl_id
        WHERE d.rol_id = ?
    ");
    $stmt_det->execute([$rol['rol_id']]);
    $detalles = $stmt_det->fetchAll();

    // Tabla de empleados
   echo "<table class='tabla-nomina-personalizada'>";

    echo "<tr>
    <th>N°</th><th>NOMBRE</th><th>CARGO</th>
    <th>SUELDO</th><th>BONO</th><th>COMISIONES</th><th>HORAS EXTRAS</th><th>TOTAL INGRESOS</th>
    <th>9.45% AP. PERS.</th><th>ANTICIPO</th><th>TOTAL DEDUCCIONES</th>
    <th>LIQUIDO A RECIBIR</th><th>FIRMAS</th>
</tr>";


    $total_sueldo = $total_bono =$total_comisiones=$total_horas_extra =$total_ingresos = 0;
    $total_aporte = $total_anticipo = $total_deducciones = $total_liquido = 0;

    foreach ($detalles as $i => $d) {
        echo "<tr>
            <td>" . ($i + 1) . "</td>
            <td>{$d['empl_nombre']}</td>
            <td>OBREROS</td> <!-- si es un valor fijo, o puedes dejarlo vacío o con otro texto -->
            <td>{$d['rpd_sueldo']}</td>
            <td>{$d['rpd_bono']}</td>
             <td>{$d['rpd_comisiones']}</td>
              <td>{$d['rpd_horas_extra']}</td>
            <td>{$d['rpd_total_ingresos']}</td>
            <td>{$d['rpd_aporte_personal']}</td>
            <td>{$d['rpd_anticipo']}</td>
            <td>{$d['rpd_total_deducciones']}</td>
            <td>{$d['rpd_liquido_recibir']}</td>
            <td></td>
        </tr>";

        $total_sueldo += $d['rpd_sueldo'];
        $total_bono += $d['rpd_bono'];
        $total_comisiones += $d['rpd_comisiones'];
        $total_horas_extra += $d['rpd_horas_extra'];
        $total_ingresos += $d['rpd_total_ingresos'];
        $total_aporte += $d['rpd_aporte_personal'];
        $total_anticipo += $d['rpd_anticipo'];
        $total_deducciones += $d['rpd_total_deducciones'];
        $total_liquido += $d['rpd_liquido_recibir'];
    }
    echo "<tr class='totales'>
    <td colspan='3'>TOTALES</td>
    <td>$total_sueldo</td>
    <td>$total_bono</td>
    <td>$total_comisiones</td>
    <td>$total_horas_extra</td>
    <td>$total_ingresos</td>
    <td>$total_aporte</td>
    <td>$total_anticipo</td>
    <td>$total_deducciones</td>
    <td>$total_liquido</td>
    <td></td>
</tr>";
    echo "</table>";

    // === NÓMINA DE PROVISIONES ===
    $stmt_prov = $pdo->prepare("
        SELECT np.prov_id, np.prov_total
        FROM tbl_nomina_provisiones np
        WHERE np.emp_id = ? AND np.prov_mes = ?
        LIMIT 1
    ");
    $stmt_prov->execute([$rol['emp_id'], $rol['rol_mes']]);
    $provision = $stmt_prov->fetch();

    if ($provision) {
        echo "<br><h4>NÓMINA DE PROVISIONES: {$rol['rol_mes']}</h4>";

        // Detalle
        $stmt_prov_det = $pdo->prepare("
            SELECT pd.*, e.empl_nombre
            FROM tbl_nomina_provisiones_detalle pd
            JOIN tbl_empleado e ON pd.empl_id = e.empl_id
            WHERE pd.prov_id = ?
        ");
        $stmt_prov_det->execute([$provision['prov_id']]);
        $prov_detalles = $stmt_prov_det->fetchAll();

      echo "<table class='tabla-nomina'>";
echo "<thead><tr>
    <th>NOMBRE</th>
    <th class='col-iess'>12.15% IESS</th>
    <th class='col-reserva'>FONDO RESERVA</th>
    <th class='col-xiii'>XIII SUELDO</th>
    <th class='col-xiv'>XIV SUELDO</th>
    <th class='col-vacaciones'>VACACIONES</th>
    <th class='col-total'>TOTAL</th>
</tr></thead>";
echo "<tbody>";

        $iess = $reserva = $xiii = $xiv = $vac = $prov_total = 0;

       foreach ($prov_detalles as $p) {
    echo "<tr>
        <td>{$p['empl_nombre']}</td>
        <td class='col-iess'>{$p['iess_patronal']}</td>
        <td class='col-reserva'>{$p['fondo_reserva']}</td>
        <td class='col-xiii'>{$p['decimo_tercero']}</td>
        <td class='col-xiv'>{$p['decimo_cuarto']}</td>
        <td class='col-vacaciones'>{$p['vacaciones']}</td>
        <td class='col-total'>{$p['total_provisiones']}</td>
    </tr>";

      $iess += $p['iess_patronal'];
            $reserva += $p['fondo_reserva'];
            $xiii += $p['decimo_tercero'];
            $xiv += $p['decimo_cuarto'];
            $vac += $p['vacaciones'];
            $prov_total += $p['total_provisiones'];
        }
}
echo "</tbody><tfoot><tr>
    <td>TOTALES</td>
    <td class='col-iess'>$iess</td>
    <td class='col-reserva'>$reserva</td>
    <td class='col-xiii'>$xiii</td>
    <td class='col-xiv'>$xiv</td>
    <td class='col-vacaciones'>$vac</td>
    <td class='col-total'>$prov_total</td>
</tr></tfoot>";
echo "</table>";

    }

    echo "<hr>";

