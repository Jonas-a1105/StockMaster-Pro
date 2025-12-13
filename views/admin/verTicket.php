<style>
    .ticket-chat-container { max-width: 900px; margin: auto; }
    .ticket-header { border-bottom: 2px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px; }
    .chat-bubble { padding: 15px; border-radius: 15px; margin-bottom: 15px; max-width: 80%; line-height: 1.5; }
    .chat-bubble-user { background-color: #e0eafc; border-bottom-left-radius: 0; float: left; clear: both; }
    .chat-bubble-admin { background-color: #f1f1f1; border-bottom-right-radius: 0; float: right; clear: both; }
    .chat-bubble-meta { font-size: 0.85em; color: var(--text-secondary); margin-bottom: 5px; }
    .chat-bubble-meta .email { font-weight: bold; }
</style>

<div class="glass-section ticket-chat-container">
    <div class="ticket-header">
        <h2><?= htmlspecialchars($ticket['subject']) ?></h2>
        <p>
            <strong>ID de Ticket:</strong> #<?= $ticket['id'] ?> |
            <strong>Prioridad:</strong> <?= $ticket['priority'] ?>
        </p>
    </div>

    <div class="chat-history" style="margin-bottom: 30px; overflow: auto; max-height: 500px; padding: 10px;">
        <?php foreach ($respuestas as $respuesta): ?>
            <?php
                // ¡Lógica Invertida! El 'user' es el cliente, el 'admin' es este admin.
                $bubbleClass = ($respuesta['user_id'] == $ticket['user_id']) ? 'chat-bubble-user' : 'chat-bubble-admin';
            ?>
            <div class="chat-bubble <?= $bubbleClass ?>">
                <div class="chat-bubble-meta">
                    <span class="email"><?= htmlspecialchars($respuesta['email']) ?></span>
                    <span class="date"> - <?= (new DateTime($respuesta['created_at']))->format('d/m/Y H:i') ?></span>
                </div>
                <div class="chat-message">
                    <?= nl2br(htmlspecialchars($respuesta['message'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <hr style="border: 1px solid var(--border-color); margin: 20px 0;">
    <h3><i class="fas fa-reply"></i> Responder como Administrador</h3>
    <form action="index.php?controlador=admin&accion=responderTicket" method="POST">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
        
        <div class="form-group">
            <label for="message">Respuesta:</label>
            <textarea name="message" rows="6" style="width: 100%; box-sizing: border-box; font-family: inherit; font-size: 1em; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.8);" placeholder="Escribe tu respuesta..." required></textarea>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label for="status">Cambiar Estado del Ticket:</label>
            <select name="status" id="status">
                <option value="Abierto" <?= $ticket['status'] == 'Abierto' ? 'selected' : '' ?>>Abierto</option>
                <option value="En Progreso" <?= $ticket['status'] == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                <option value="Cerrado" <?= $ticket['status'] == 'Cerrado' ? 'selected' : '' ?>>Cerrado</Goption>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">
            <i class="fas fa-paper-plane"></i> Enviar Respuesta
        </button>
        <a href="index.php?controlador=admin&accion=tickets" class="btn btn-secondary" style="margin-top: 15px;">
            <i class="fas fa-arrow-left"></i> Volver a la lista
        </a>
    </form>
</div>