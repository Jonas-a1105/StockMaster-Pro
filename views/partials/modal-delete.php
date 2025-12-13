<?php
/**
 * MODAL DELETE - Modal de confirmación de eliminación
 */
?>
<div id="modal-confirmar-eliminar" class="modal modal-confirm-delete">
    <div class="modal-content">
        <span class="close-btn" id="cerrar-modal-eliminar">&times;</span>
        <div class="delete-icon"><i class="fas fa-trash-alt"></i></div>
        <h2>¿Estás seguro?</h2>
        <p>Esta acción no se puede deshacer.</p>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-secondary" id="cancelar-modal-eliminar">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">Sí, Eliminar</button>
        </div>
    </div>
</div>
