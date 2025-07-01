<?php require_once __DIR__ . '/../../controllers/auth.controller.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - AMT_ENCI</title>
    <style>
        html {
            font-size: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main {
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
            flex-direction: row-reverse;
            /* ← Invierte el orden visual */
        }

        .left-panel {
            flex: 1;
            background: rgba(40, 167, 69, 0.08);
            color: #155724;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            backdrop-filter: blur(4px);
        }

        .left-panel h1 {
            font-size: 72px;
            margin-bottom: 15px;
            color: #28a745;
        }

        .left-panel p {
            font-size: 26px;
            line-height: 38px;
            max-width: 480px;
        }

        .right-panel {
            flex: 1;
            padding: 40px 35px;
            background-color: #fff;
            border-radius: 0;
        }

        .right-panel h2 {
            font-size: 40px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin-bottom: 30px;
        }

        .right-panel input {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 18px;
        }

        .right-panel button {
            width: 100%;
            padding: 16px;
            background-color: #28a745;
            color: white;
            font-size: 20px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 10px;
        }

        .right-panel button:hover {
            background-color: #218838;
        }

        .right-panel a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
        }

        .right-panel a:hover {
            text-decoration: underline;
        }

        .right-panel p {
            margin-top: 20px;
            text-align: center;
        }

        .error {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 16px;
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

       


        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-panel,
            .right-panel {
                text-align: center;
            }

            .left-panel h1 {
                font-size: 60px;
            }

            .left-panel p {
                font-size: 22px;
                line-height: 32px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="main">
        <div class="container">

            <div class="right-panel">
                <h2>Iniciar Sesión</h2>

                <?php if (isset($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <input type="email" name="correo" placeholder="Correo electrónico" required>
                    <input type="password" name="contrasena" placeholder="Contraseña" required>
                    <button type="submit" name="login">Ingresar</button>
                </form>

                <p><a href="registro.php">¿No tienes cuenta? Regístrate</a></p>
            </div>

            <div class="left-panel">
                <h1>AMT</h1>
                <p>Plataforma desarrollada por estudiantes de TI con la ENCI</p>
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