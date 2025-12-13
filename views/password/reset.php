<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale:1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .form-box { max-width: 450px; width: 100%; }
    </style>
</head>
<body>
    <div class="form-box">
        <div class="glass-section">
            <h2><i class="fas fa-lock"></i> Crea tu Nueva Contraseña</h2>
            
            <form action="index.php?controlador=password&accion=update" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password_confirm">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top: 20px; width: 100%;">Guardar Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>