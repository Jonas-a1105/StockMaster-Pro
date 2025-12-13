<div class="glass-section">
    <h2><i class="fas fa-users"></i> Gestión de Equipo</h2>
    <p>Agrega empleados para que puedan acceder a tu inventario y POS.</p>

    <div class="dashboard-grid" style="margin-bottom: 30px;">
        <div class="kpi-card">
            <h3>Agregar Miembro</h3>
            <form action="index.php?controlador=equipo&accion=crear" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email del empleado" required style="margin-bottom: 10px;">
                    <input type="password" name="password" placeholder="Contraseña temporal" required style="margin-bottom: 10px;">
                    <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; text-align: center;">Crear Cuenta</button>
                </div>
            </form>
        </div>
    </div>

    <h3>Miembros Actuales</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Fecha Registro</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($empleados as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['email']) ?></td>
                    <td><?= $emp['fecha_registro'] ?></td>
                    <td>
                        <form action="index.php?controlador=equipo&accion=eliminar" method="POST" onsubmit="return confirm('¿Eliminar acceso?');">
                            <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>