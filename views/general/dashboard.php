<?php
require_once __DIR__ . '/../../controllers/seguridad.controller.php';
require_once __DIR__ . '/../../config/db.php';

$nombre = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];
$usuario_id = $_SESSION['usuario']['id'];

$vista = $_GET['vista'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - <?php echo ucfirst($rol); ?></title>
    <link rel="stylesheet" href="/amt_enci/public/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --fondo-general: #e8f5e9;
            --texto-principal: #2e7d32;
            --navbar-bg: #00796b;
            --navbar-hover: #004d40;
            --navbar-text: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--fondo-general);
            color: var(--texto-principal);
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: var(--navbar-bg);
            color: var(--navbar-text);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            font-weight: 400;
            /* o incluso 300 si deseas más ligereza */
        }


        nav ul li a {
            color: var(--navbar-text);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
            transition: background 0.2s ease-in-out;
        }

        nav ul li a:hover {
            background-color: var(--navbar-hover);
        }

        .contenido-principal {
            padding: 100px 30px 30px;
            /* Espacio para navbar fijo */
        }

        h1 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 20px;
            }

            nav ul {
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
            }

            .contenido-principal {
                padding: 120px 20px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- NAVBAR FIJO -->
    <nav>
        <ul>
            <li><strong><?php echo htmlspecialchars($nombre); ?></strong> (<?php echo $rol; ?>)</li>
        </ul>
        <ul>
            <li><a href="dashboard.php" title="Inicio"><i class="fas fa-home"></i></a></li>
            <li><a href="dashboard.php?vista=gestion"><i class="fas fa-building-user"></i> Empresas y Empleados</a></li>
            <li><a href="dashboard.php?vista=productos"><i class="fas fa-box-open"></i> Productos</a></li>
            <li><a href="dashboard.php?vista=kardex"><i class="fas fa-clipboard-list"></i> KARDEX empleado</a></li>
            <li><a href="dashboard.php?vista=mano_de_obra"><i class="fas fa-hard-hat"></i> Mano de Obra</a></li>
            <li><a href="dashboard.php?vista=costos_indirectos"><i class="fas fa-chart-line"></i> Costos Indirectos</a>
            </li>


            <li><a href="/amt_enci/views/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>

    </nav>

    <!-- CONTENIDO -->
    <div class="contenido-principal">
        <?php if ($vista === 'gestion'): ?>
            <?php include __DIR__ . '/partes/empresas_empleados.php'; ?>

        <?php elseif ($vista === 'productos'): ?>
            <?php include __DIR__ . '/partes/productos.php'; ?>

        <?php elseif ($vista === 'kardex'): ?>
            <?php include __DIR__ . '/partes/kardex.php'; ?>

        <?php elseif ($vista === 'mano_de_obra'): ?>
            <?php include __DIR__ . '/partes/mano_de_obra.php'; ?>

        <?php elseif ($vista === 'costos_indirectos'): ?>
            <?php include __DIR__ . '/partes/costos_indirectos.php'; ?>



        <?php else: ?>
            <h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> 👋</h1>
            <p>Has iniciado sesión como <strong><?php echo htmlspecialchars($rol); ?></strong>.</p>
            <p>Utiliza el menú para acceder a las funciones del sistema.</p>
        <?php endif; ?>
    </div>
</body>

</html>