<!DOCTYPE html>
<html>
<head>
    <title>¡Hola PHP desde Docker!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }
        .container {
            text-align: center;
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .info {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 ¡Hola Mundo PHP!</h1>
        <p>Esta aplicación fue desplegada automáticamente con Jenkins</p>
        
        <div class="info">
            <p><strong>Versión del Build:</strong> <?php echo getenv('BUILD_VERSION') ?: '1.0.0'; ?></p>
            <p><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <p style="margin-top: 30px;">🎉 ¡Despliegue automático exitoso!</p>
    </div>
</body>
</html>