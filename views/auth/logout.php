<?php
require_once '../../core/session.php';
logoutUsuario();
header('Location: login.php'); // ← Redirige dentro de la misma carpeta
exit;