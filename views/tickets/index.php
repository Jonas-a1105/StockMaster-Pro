<div class="glass-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-life-ring"></i> Mis Tickets de Soporte</h2>
        <a class="btn btn-success" href="index.php?controlador=ticket&accion=crear">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Ticket
        </a>
    </div>

    <div class="table-container">
        <table id="tabla-tickets">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Última Actualización</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="6" class="text-center">No has creado ningún ticket.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?= $ticket['id'] ?></td>
                            <td><?= htmlspecialchars($ticket['subject']) ?></td>
                            <td>
                                <?php
                                    $color = 'var(--secondary-color)';
                                    if ($ticket['status'] === 'Abierto') $color = 'var(--success-color)';
                                    if ($ticket['status'] === 'En Progreso') $color = 'var(--info-color)';
                                ?>
                                <span style="font-weight: bold; color: <?= $color; ?>"><?= $ticket['status'] ?></span>
                            </td>
                            <td><?= $ticket['priority'] ?></td>
                            <td><?= (new DateTime($ticket['updated_at']))->format('d/m/Y h:i A') ?></td>
                            <td class="text-center">
                                <a href="index.php?controlador=ticket&accion=ver&id=<?= $ticket['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>