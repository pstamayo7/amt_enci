<?php require_once __DIR__ . '/../../controllers/admin.controller.php'; ?>
<!DOCTYPE html>
<html lang="es">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Poppins:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../public/assets/css/admin.css">

<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>

</head>

<body>

    <div class="navbar">
        <div class="navbar-left">
            <i class="fas fa-user-shield"></i>
            <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Usuario') ?>
            · <span style="font-style: italic; text-transform: lowercase;">
                <?= htmlspecialchars($_SESSION['usuario']['rol'] ?? 'rol') ?>
            </span>
        </div>

        <div class="navbar-right">
            <a href="../auth/logout.php" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>

        </div>
    </div>

    <?php if (empty($usuarios)): ?>
        <p>No hay usuarios registrados.</p>
    <?php else: ?>

        <div class="card">
            <div class="card-header">
                Gestión de Usuarios
                <div class="card-subtitle">Usuarios registrados en el sistema</div>
            </div>

            <div class="card-body">

                <form method="GET" style="margin-bottom: 20px;">
                    <label for="estado">Filtrar por estado:</label>
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="">-- Todos --</option>
                        <option value="aprobado" <?= ($_GET['estado'] ?? '') === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                        <option value="pendiente" <?= ($_GET['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="rechazado" <?= ($_GET['estado'] ?? '') === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                        <option value="eliminado" <?= ($_GET['estado'] ?? '') === 'eliminado' ? 'selected' : '' ?>>Eliminado</option>
                    </select>
                </form>

                <table>

                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Rol</th>

                        <th>Acciones</th>
                    </tr>


                    <?php foreach ($usuarios as $usuario): ?>

                        <tr>
                            <form method="POST">
                                <td><?= $usuario['usu_id'] ?></td>
                                <td><?= htmlspecialchars($usuario['usu_nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['usu_correo']) ?></td>

                                <td>
                                    <select name="usu_estado" class="estado-select" required>
                                        <option value="aprobado" <?= $usuario['usu_estado'] === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                        <option value="pendiente" <?= $usuario['usu_estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="rechazado" <?= $usuario['usu_estado'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                        <option value="eliminado" <?= $usuario['usu_estado'] === 'eliminado' ? 'selected' : '' ?>>Eliminado</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="usu_rol" class="rol-select" <?= $usuario['usu_estado'] !== 'aprobado' ? 'disabled' : '' ?>>
                                        <option value="" <?= empty($usuario['usu_rol']) ? 'selected' : '' ?>>-- Sin rol --</option>
                                        <option value="admin" <?= $usuario['usu_rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="docente" <?= $usuario['usu_rol'] === 'docente' ? 'selected' : '' ?>>Docente</option>
                                        <option value="estudiante" <?= $usuario['usu_rol'] === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="usu_id" value="<?= $usuario['usu_id'] ?>">
                                    <button type="submit" name="actualizar_usuario">Actualizar</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>


    <?php endif; ?>

    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <?php if (!empty($_SESSION['guardado'])): ?>
        <div id="notificacion-guardado" class="notificacion-guardado mostrar">✔ Usuario actualizado</div>
        <?php unset($_SESSION['guardado']); ?>
    <?php endif; ?>


    <script src="../../public/assets/js/admin.js"></script>
</body>

</html>