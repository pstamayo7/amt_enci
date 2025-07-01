<?php
require_once __DIR__ . '/../../config/db.php';

/* ---------- 1. PARÁMETROS ---------- */
$parametros = [];
$stmt = $pdo->query("SELECT par_nombre, par_valor FROM tbl_parametros");
while ($row = $stmt->fetch()) {
    $parametros[$row['par_nombre']] = $row['par_valor'];
}

/* ---------- 2. DATOS BÁSICOS ---------- */
$empresa_id     = $_POST['empresa_id'];
$rol_mes        = $_POST['rol_mes'];          // ‘YYYY-MM’
$fecha_emision  = $_POST['rol_fecha_emision'];

$total_ingresos = $total_deducciones = $total_liquido = 0;

/* ---------- INICIAR TRANSACCIÓN ---------- */
$pdo->beginTransaction();

/* ---------- 3. INSERTAR ENCABEZADO ROL ---------- */
$stmt = $pdo->prepare(
    "INSERT INTO tbl_rol_pago
       (emp_id, rol_mes, rol_fecha_emision,
        rol_total_ingresos, rol_total_deducciones, rol_total_liquido)
     VALUES (?, ?, ?, 0, 0, 0)
     RETURNING rol_id"
);
$stmt->execute([$empresa_id, $rol_mes, $fecha_emision]);
$rol_id = $stmt->fetchColumn();



/* ---------- 4. RECORRER EMPLEADOS ---------- */
foreach ($_POST['empleados'] as $empl_id => $datos) {

    /* --- valores que vienen del formulario --- */
    $sueldo      = (float) $datos['sueldo'];
    $bono        = (float) $datos['bono'];
    $horas_extra = (float) ($datos['horas_extra'] ?? 0);
    $comisiones  = (float) ($datos['comisiones'] ?? 0);
    $anticipo    = (float) $datos['anticipo'];
    $horas_mod   = (float) $datos['horas_mod'];           // 🔹 NUEVO

    /* --- cálculos para el rol --- */
    $total_ing   = $sueldo + $bono + $horas_extra + $comisiones;
    $aporte_per  = $total_ing * ($parametros['aporte_personal'] / 100);
    $total_ded   = $aporte_per + $anticipo;
    $liquido     = $total_ing - $total_ded;

    $total_ingresos   += $total_ing;
    $total_deducciones += $total_ded;
    $total_liquido    += $liquido;

    /* --- insertar detalle del rol --- */
    $stmt = $pdo->prepare(
        "INSERT INTO tbl_rol_pago_detalle (
             rol_id, empl_id,
             rpd_sueldo, rpd_bono, rpd_horas_extra, rpd_comisiones,
             rpd_total_ingresos, rpd_aporte_personal, rpd_anticipo,
             rpd_total_deducciones, rpd_liquido_recibir
         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $rol_id, $empl_id,
        $sueldo, $bono, $horas_extra, $comisiones,
        $total_ing, $aporte_per, $anticipo,
        $total_ded, $liquido
    ]);

    /* ---------- 🔹 NUEVO: REGISTRO EN TARJETA DE TIEMPO DETALLE ---------- */
    $horas_totales = 160.0;
    $horas_moi     = $horas_totales - $horas_mod;
    $valor_hora    = $sueldo / 160.0;
    $total_mod     = $horas_mod * $valor_hora;
    $total_moi     = $horas_moi * $valor_hora;

    $tt_detalles[] = [            // guardo en un array para insertarlo después
        $empl_id, $horas_mod, $horas_moi,
        $valor_hora, $total_mod, $total_moi
    ];

    /* acumular totales por tarjeta */
   
}

/* ---------- 5. ACTUALIZAR TOTALES ROL ---------- */
$stmt = $pdo->prepare(
    "UPDATE tbl_rol_pago
        SET rol_total_ingresos=?, rol_total_deducciones=?, rol_total_liquido=?
      WHERE rol_id=?"
);
$stmt->execute([$total_ingresos, $total_deducciones, $total_liquido, $rol_id]);

/* ---------- 6. PROVISIONES (sin cambios) ---------- */

// 5. Insertar en tbl_nomina_provisiones
$stmt = $pdo->prepare("
    INSERT INTO tbl_nomina_provisiones (emp_id, prov_mes, prov_fecha_emision, prov_total)
    VALUES (?, ?, ?, 0) RETURNING prov_id
");
$stmt->execute([$empresa_id, $rol_mes, $fecha_emision]);
$prov_id = $stmt->fetchColumn();

$total_provisiones = 0;
$total_aporte_patronal = 0; // 🔹 NUEVO

// 6. Insertar en tbl_nomina_provisiones_detalle
foreach ($_POST['empleados'] as $empl_id => $datos) {
    $sueldo = $datos['sueldo'];
    $bono = $datos['bono'];
    $horas_extra = $datos['horas_extra'];
    $comisiones = $datos['comisiones'];
    $base = $sueldo + $bono + $horas_extra + $comisiones;
    $sueldoBase =$parametros['sueldo_basico'];

    $iess_patronal = $base * $parametros['aporte_patronal'] / 100;
    $fondo_reserva = $base * $parametros['fondo_reserva'] / 100;
    $decimo_tercero = $base * $parametros['decimo_tercero'] / 100;
    $decimo_cuarto = $sueldoBase * $parametros['decimo_cuarto'] / 100;
    $vacaciones = $base * $parametros['vacaciones'] / 100;

    $total =  $fondo_reserva + $decimo_tercero + $decimo_cuarto + $vacaciones;
    $total_provisiones += $total;
    $total_aporte_patronal += $iess_patronal; //

    $stmt = $pdo->prepare("
        INSERT INTO tbl_nomina_provisiones_detalle (prov_id, empl_id, iess_patronal, fondo_reserva, decimo_tercero, decimo_cuarto, vacaciones, total_provisiones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$prov_id, $empl_id, $iess_patronal, $fondo_reserva, $decimo_tercero, $decimo_cuarto, $vacaciones, $total]);
}

// 7. Actualizar total de provisiones
$stmt = $pdo->prepare("UPDATE tbl_nomina_provisiones SET prov_total=? WHERE prov_id=?");
$stmt->execute([$total_provisiones, $prov_id]);









/* ... tu mismo bloque que ya tenías ... */


$tarj_total_mod = 0.0;
$tarj_total_moi = 0.0;
$tarj_total_mod = isset($_POST['tarj_total_mod']) ? (float) $_POST['tarj_total_mod'] : 0.0;
$tarj_total_moi = isset($_POST['tarj_total_moi']) ? (float) $_POST['tarj_total_moi'] : 0.0;
$tarj_total_general = $tarj_total_mod + $tarj_total_moi;



/* ---------- 🔹 7. INSERTAR ENCABEZADO TARJETA DE TIEMPO ---------- */
$stmt = $pdo->prepare(
    "INSERT INTO tbl_tarjeta_tiempo (
         emp_id, tarj_mes, tarj_fecha_emision,
         tarj_total_mod, tarj_total_moi,
         tarj_total_provisiones, tarj_total_aporte_patronal, tarj_total_general
     ) VALUES (?, ?, ?, 0, 0, 0, 0, 0)
     RETURNING tarj_id"
);
$stmt->execute([$empresa_id, $rol_mes, $fecha_emision]);
$tarj_id = $stmt->fetchColumn();

/* ---------- 🔹 8. INSERTAR DETALLES TARJETA Y ACUMULAR TOTALES ---------- */
$stmtDet = $pdo->prepare(
    "INSERT INTO tbl_tarjeta_tiempo_detalle (
         tarj_id, empl_id, tdet_horas_totales, tdet_horas_mod, tdet_horas_moi,
         tdet_valor_hora, tdet_total_mod, tdet_total_moi
     ) VALUES (?, ?, 160, ?, ?, ?, ?, ?)"
);

foreach ($tt_detalles as $d) {
    [$empl_id, $hor_mod, $hor_moi, $v_hora, $tot_mod, $tot_moi] = $d;
    $stmtDet->execute([$tarj_id, $empl_id, $hor_mod, $hor_moi, $v_hora, $tot_mod, $tot_moi]);

    $tarj_total_mod += $tot_mod;
    $tarj_total_moi += $tot_moi;
}

$tarj_total_general = $tarj_total_mod + $tarj_total_moi;

/* ---------- 🔹 9. ACTUALIZAR TOTALES DE LA TARJETA ---------- */
$stmt = $pdo->prepare(
    "UPDATE tbl_tarjeta_tiempo
        SET tarj_total_mod = ?, 
            tarj_total_moi = ?, 
            tarj_total_provisiones = ?,         -- 🔹 NUEVO
            tarj_total_aporte_patronal = ?,     -- 🔹 NUEVO
            tarj_total_general = ?
     WHERE tarj_id = ?"
);
$stmt->execute([
    $tarj_total_mod, 
    $tarj_total_moi, 
    $total_provisiones,             // 🔹
    $total_aporte_patronal,        // 🔹
    $tarj_total_general, 
    $tarj_id
]);

/* ---------- 🔹 10. FIN DE TRANSACCIÓN ---------- */
$pdo->commit();
echo "Rol, provisiones y tarjeta de tiempo guardados correctamente.";