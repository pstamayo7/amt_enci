<?php require_once __DIR__ . '/../../controllers/auth.controller.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro - AMT_ENCI</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f8;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            display: flex;
            width: 1000px;
            background-color: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            overflow: hidden;
        }

        .form-box {
            flex: 1.2;
            padding: 50px 40px;
        }

        .info-box {
            flex: 1;
            background: rgba(0, 123, 255, 0.08);
            color: #004085;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            backdrop-filter: blur(4px);
        }

        .info-box h3 {
            margin-bottom: 15px;
            font-size: 22px;
        }

        .info-box p {
            font-size: 16px;
            line-height: 1.6;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #222;
            font-size: 26px;
        }

        label {
            font-weight: bold;
            color: #444;
            display: block;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 30px;
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        .login-link {
            text-align: center;
            margin-top: 18px;
        }

        .login-link a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            padding: 18px 0;
            font-size: 15px;
            color: #555;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }

        .footer a {
            color: #333;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="container">
            <div class="form-box">
                <h2>Registro de Usuario</h2>

                <?php if (isset($error)): ?>
                    <p class="message error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <?php if (isset($mensaje)): ?>
                    <p class="message success"><?= htmlspecialchars($mensaje) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" required>

                    <label for="correo">Correo:</label>
                    <input type="email" name="correo" required>

                    <label for="contrasena">Contraseña:</label>
                    <input type="password" name="contrasena" required>

                    <button type="submit" name="registrar">Registrarse</button>
                </form>

                <div class="login-link">
                    <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
                </div>
            </div>

            <div class="info-box">
                <h3>¿Qué sucede después del registro?</h3>
                <p>
                    Tu cuenta será revisada por el administrador antes de ser activada.
                    Hasta entonces, permanecerás en estado de espera.
                </p>
                <p>
                    Recibirás una notificación cuando se apruebe tu solicitud. ¡Gracias por registrarte en AMT_ENCI!
                </p>
            </div>
        </div>
    </div>

    <div class="footer">
        Desarrollado por Santiago Avila L. ·
        <a href="https://github.com/santsavila" target="_blank">
            <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png"
                alt="GitHub" width="20" height="20" style="vertical-align: middle;">
            GitHub
        </a>
    </div>

</body>

</html>