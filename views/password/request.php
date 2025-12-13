<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale:1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .form-box { max-width: 450px; width: 100%; }
    </style>
</head>
<body>
    <div class="form-box">
        <div class="glass-section">
            <h2><i class="fas fa-key"></i> Recuperar Contraseña</h2>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">
                Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
            </p>
            <form action="index.php?controlador=password&accion=send" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">Enviar Enlace</button>
            </form>
            <p style="text-align:center; margin-top: 20px;">
                <a href="index.php?controlador=login&accion=index">Volver a Iniciar Sesión</a>
            </p>
        </div>
    </div>
</body>
</html>