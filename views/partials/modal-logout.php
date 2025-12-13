<?php
/**
 * MODAL LOGOUT - Modal de confirmación de cierre de sesión
 */
?>
<div id="modal-logout" class="modal modal-logout">
    <div class="modal-content">
        <div class="sad-face-icon">😢</div>
        <h2>¿Ya te vas?</h2>
        <p style="font-size: 1.1em; color: var(--text-secondary);">¿Estás seguro de que quieres cerrar sesión?</p>
        <div class="modal-footer" style="justify-content: center;">
            <button class="btn btn-secondary" id="btn-cancel-logout">No, quedarme</button>
            <a href="index.php?controlador=login&accion=logout" class="btn btn-danger">Sí, Salir</a>
        </div>
    </div>
</div>
