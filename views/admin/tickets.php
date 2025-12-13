<div class="glass-section">
    <h2><i class="fas fa-ticket-alt"></i> Administrar Tickets de Soporte</h2>
    <p>Aquí puedes ver y responder los tickets de todos los usuarios.</p>

    <div class="table-container">
        <table id="tabla-tickets-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Usuario (Email)</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Actualizado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="7" class="text-center">No hay tickets activos.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?= $ticket['id'] ?></td>
                            <td><?= htmlspecialchars($ticket['subject']) ?></td>
                            <td><?= htmlspecialchars($ticket['email']) ?></td>
                            <td>
                                <?php
                                    $color = 'var(--secondary-color)';
                                    if ($ticket['status'] === 'Abierto') $color = 'var(--success-color)';
                                    if ($ticket['status'] === 'En Progreso') $color = 'var(--info-color)';
                                ?>
                                <span style="font-weight: bold; color: <?= $color; ?>"><?= $ticket['status'] ?></span>
                            </td>
                            <td><?= $ticket['priority'] ?></td>
                            <td><?= (new DateTime($ticket['updated_at']))->format('d/m/Y H:i') ?></td>
                            <td class="text-center">
                                <a href="index.php?controlador=admin&accion=verTicket&id=<?= $ticket['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Responder
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>