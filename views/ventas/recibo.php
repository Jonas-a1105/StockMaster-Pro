<?php
// Obtener datos de la empresa y usuario
$db = \App\Core\Database::conectar();
$stmt = $db->prepare("SELECT username, email, empresa_nombre, empresa_direccion, empresa_telefono, empresa_logo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

$nombreEmpresa = !empty($usuario['empresa_nombre']) ? $usuario['empresa_nombre'] : 'MI NEGOCIO';
$direccion = !empty($usuario['empresa_direccion']) ? $usuario['empresa_direccion'] : 'Dirección no configurada';
$telefono = !empty($usuario['empresa_telefono']) ? $usuario['empresa_telefono'] : '';
$nombreUsuario = $usuario['username'] ?? $usuario['email'] ?? 'Usuario';
$empresa = $usuario; 

// Obtener cliente
$clienteNombre = 'Cliente General';
$clienteDocumento = '';
if (!empty($venta['cliente_id'])) {
    $stmtCliente = $db->prepare("SELECT nombre, numero_documento, tipo_documento FROM clientes WHERE id = ? AND user_id = ?");
    $stmtCliente->execute([$venta['cliente_id'], $_SESSION['user_id']]);
    $cliente = $stmtCliente->fetch();
    if ($cliente) {
        $clienteNombre = $cliente['nombre'];
        $clienteDocumento = ($cliente['tipo_documento'] ?? 'CI') . ': ' . ($cliente['numero_documento'] ?? 'N/A');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?= $venta['id'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Roboto Mono', 'Courier New', monospace; 
            background: #eef2f6; 
            color: #1e293b;
            font-size: 12px;
            line-height: 1.4;
            display: flex;
            justify-content: center;
            padding: 24px;
        }

        .ticket {
            width: 320px;
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 16px;
        }
        
        .logo {
            max-width: 100px;
            max-height: 80px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 16px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 11px;
            color: #64748b;
        }

        .info-group {
            margin-bottom: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .info-label {
            color: #64748b;
        }

        .info-value {
            font-weight: 600;
            text-align: right;
        }

        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 16px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }

        th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 8px 0;
            vertical-align: top;
        }

        .qty { width: 30px; text-align: center; }
        .price { text-align: right; width: 60px; }
        .total { text-align: right; width: 60px; font-weight: 600; }

        .totals-section {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
        }

        .footer {
            text-align: center;
            margin-top: 24px;
            color: #64748b;
            font-size: 10px;
        }

        .btn-print {
            display: block;
            width: 100%;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-family: inherit;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-top: 24px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #4338ca;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .ticket {
                box-shadow: none;
                width: 100%;
                max-width: 80mm; /* Standard thermal width */
                margin: 0;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Logo & Header -->
        <div class="header">
            <?php if(!empty($empresa['empresa_logo'])): ?>
                <div class="logo-container">
                    <img src="uploads/<?= htmlspecialchars($empresa['empresa_logo']) ?>" class="logo">
                </div>
            <?php endif; ?>
            <h2><?= htmlspecialchars($nombreEmpresa) ?></h2>
            <?php if($direccion): ?><p><?= htmlspecialchars($direccion) ?></p><?php endif; ?>
            <?php if($telefono): ?><p>Tel: <?= htmlspecialchars($telefono) ?></p><?php endif; ?>
        </div>

        <!-- Info Ticket -->
        <div class="info-group">
            <div class="info-row">
                <span class="info-label">Recibo #:</span>
                <span class="info-value"><?= str_pad($venta['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value"><?= (new DateTime($venta['created_at']))->format('d/m/Y H:i') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Atendió:</span>
                <span class="info-value"><?= htmlspecialchars($nombreUsuario) ?></span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Info Cliente -->
        <div class="info-group">
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value"><?= htmlspecialchars($clienteNombre) ?></span>
            </div>
            <?php if(!empty($clienteDocumento)): ?>
            <div class="info-row">
                <span class="info-label">Doc:</span>
                <span class="info-value"><?= htmlspecialchars($clienteDocumento) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla Productos -->
        <table>
            <thead>
                <tr>
                    <th>Desc</th>
                    <th class="qty">Cant</th>
                    <th class="price">Bs.Unit</th>
                    <th class="total">Bs.Tot</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                $precioUnitarioUSD = (float)($item['precio_unitario_usd'] ?? 0);
                $tasa = (float)($venta['tasa_ves'] ?? 0);
                
                // Calcular en Bs
                $precioUnitarioBs = $precioUnitarioUSD * $tasa;
                $subtotalItemBs = $precioUnitarioBs * ($item['cantidad'] ?? 1);
                ?>
                <tr>
                    <td class="item-name"><?= htmlspecialchars($item['nombre_producto'] ?? 'Producto') ?></td>
                    <td class="text-center"><?= $item['cantidad'] ?? 1 ?></td>
                    <td class="text-right"><?= number_format($precioUnitarioBs, 2) ?></td>
                    <td class="text-right"><?= number_format($subtotalItemBs, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal Bs:</span>
                <span><?= number_format($venta['total_ves'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>Tasa (Bs/$):</span>
                <span><?= number_format($venta['tasa_ves'], 2) ?></span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL Bs:</span>
                <span><?= number_format($venta['total_ves'], 2) ?></span>
            </div>

        </div>

        <!-- Footer Info -->
        <div class="footer">
            <p style="margin-bottom: 8px;">
                Método: <strong><?= htmlspecialchars($venta['metodo_pago'] ?? 'Efectivo') ?></strong> | 
                Estado: <strong><?= ($venta['estado_pago'] ?? 'Pagada') ?></strong>
            </p>
            <?php if(!empty($venta['notas'])): ?>
                <p style="margin-bottom: 8px;">Notas: <?= htmlspecialchars($venta['notas']) ?></p>
            <?php endif; ?>
            <p>¡Gracias por su compra!</p>
            <p>Conserve este ticket para cualquier reclamo.</p>
        </div>

        <button class="btn-print" onclick="window.print()">Imprimir Recibo</button>
    </div>
</body>
</html>
