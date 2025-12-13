<?php
/**
 * FLASH MESSAGES - Notificaciones de sesión
 */
use App\Core\Session;
?>
<div class="flash-container" id="flash-container">
    <?php if (Session::hasFlash()): ?>
        <?php if ($successMsg = Session::getFlash('success')): ?>
            <div class="flash-notification flash-success" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 400);">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($successMsg) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg = Session::getFlash('error')): ?>
            <div class="flash-notification flash-error" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 400);">
                <i class="fas fa-times-circle"></i>
                <span><?= htmlspecialchars($errorMsg) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($warningMsg = Session::getFlash('warning')): ?>
            <div class="flash-notification flash-warning" onclick="this.classList.add('fade-out'); setTimeout(() => this.remove(), 400);">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($warningMsg) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
