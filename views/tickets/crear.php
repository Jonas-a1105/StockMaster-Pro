<div class="glass-section">
    <h2><i class="fas fa-plus-circle"></i> Crear Nuevo Ticket</h2>
    <p>Por favor, detalla tu problema o consulta.</p>

    <form action="index.php?controlador=ticket&accion=guardar" method="POST">
        <div class="form-group">
            <label for="subject">Asunto</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        
        <div class="form-group" style="margin-top: 15px;">
            <label for="priority">Prioridad</label>
            <select id="priority" name="priority">
                <option value="Baja">Baja</option>
                <option value="Media" selected>Media</option>
                <option value="Alta">Alta</option>
            </select>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label for="message">Mensaje</label>
            <textarea id="message" name="message" rows="8" style="font-family: inherit; font-size: 1em; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.8);" required></textarea>
        </div>

        <div style="margin-top: 20px;">
            <a href="index.php?controlador=ticket" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Enviar Ticket
            </button>
        </div>
    </form>
</div>