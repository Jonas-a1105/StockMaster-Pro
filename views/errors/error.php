<div class="error-container" style="text-align: center; padding: 50px; margin-top: 50px;">
    <h1 style="font-size: 72px; color: #dc3545; margin-bottom: 20px;"><?php echo $code; ?></h1>
    <h2 style="margin-bottom: 30px;"><?php echo $message; ?></h2>
    <p style="color: #6c757d; margin-bottom: 40px;">Lo sentimos, algo salió mal. Si el problema persiste, contacta al soporte técnico.</p>
    <a href="index.php?controlador=dashboard" class="btn btn-primary" style="padding: 10px 25px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;">
        <i class="fas fa-home"></i> Volver al Inicio
    </a>

    <?php if (isset($debug)): ?>
        <div class="debug-info" style="text-align: left; margin-top: 50px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; font-family: monospace; overflow-x: auto;">
            <h4>Debug Information (Visible in DEV mode only)</h4>
            <p><strong>Exception:</strong> <?php echo $debug['exception']; ?></p>
            <p><strong>File:</strong> <?php echo $debug['file']; ?> (Line <?php echo $debug['line']; ?>)</p>
            <pre><?php echo $debug['trace'] ?? ''; ?></pre>
            <?php if (isset($debug['validation_errors'])): ?>
                <h5>Validation Errors:</h5>
                <pre><?php print_r($debug['validation_errors']); ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
