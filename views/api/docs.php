<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - SaaS Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #e2e8f0;
            line-height: 1.6;
        }
        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        h1 { font-size: 2.5em; margin-bottom: 10px; }
        h1 i { color: #3b82f6; }
        .subtitle { color: #64748b; margin-bottom: 40px; }
        .section { background: #1e293b; border-radius: 12px; padding: 25px; margin-bottom: 25px; border: 1px solid #334155; }
        .section h2 { color: #3b82f6; font-size: 1.3em; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .endpoint { background: #0f172a; border-radius: 8px; padding: 15px; margin: 15px 0; border-left: 4px solid #3b82f6; }
        .method { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85em; margin-right: 10px; }
        .method.get { background: #10b981; }
        .method.post { background: #3b82f6; }
        .method.put { background: #f59e0b; }
        .method.delete { background: #ef4444; }
        .path { font-family: monospace; color: #f1f5f9; }
        .desc { color: #94a3b8; margin-top: 8px; font-size: 0.95em; }
        .params { margin-top: 12px; }
        .params table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        .params th { text-align: left; padding: 8px; background: #334155; color: #94a3b8; }
        .params td { padding: 8px; border-bottom: 1px solid #334155; }
        .params code { background: #334155; padding: 2px 6px; border-radius: 4px; font-size: 0.85em; }
        pre { background: #0f172a; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 0.9em; }
        code { color: #10b981; }
        .auth-box { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 8px; padding: 20px; margin: 20px 0; }
        .auth-box h3 { margin-bottom: 10px; }
        .try-it { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        .try-it:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-code"></i> SaaS Pro API</h1>
        <p class="subtitle">Documentación de la API REST v1.0</p>
        
        <div class="section">
            <h2><i class="fas fa-key"></i> Autenticación</h2>
            <p>Todas las peticiones requieren autenticación mediante uno de estos métodos:</p>
            
            <div class="auth-box">
                <h3>Opción 1: API Key en Query String</h3>
                <pre><code>GET /api/productos?api_key=TU_API_KEY</code></pre>
            </div>
            
            <div class="auth-box">
                <h3>Opción 2: Bearer Token en Header</h3>
                <pre><code>Authorization: Bearer TU_API_KEY</code></pre>
            </div>
            
            <p style="margin-top: 15px; color: #94a3b8;">
                <i class="fas fa-info-circle"></i> 
                Genera tu API Key desde <a href="index.php?controlador=config" style="color: #3b82f6;">Configuración del Negocio</a>
            </p>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-box"></i> Productos</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/index.php?controlador=api&accion=productos</span>
                <p class="desc">Obtener lista de productos del inventario</p>
                <div class="params">
                    <table>
                        <tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr>
                        <tr><td><code>buscar</code></td><td>string</td><td>Filtrar por nombre</td></tr>
                        <tr><td><code>limit</code></td><td>int</td><td>Máximo 100, default 50</td></tr>
                        <tr><td><code>offset</code></td><td>int</td><td>Para paginación</td></tr>
                    </table>
                </div>
            </div>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/index.php?controlador=api&accion=producto&id={id}</span>
                <p class="desc">Obtener un producto específico por ID</p>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-shopping-cart"></i> Ventas</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/index.php?controlador=api&accion=ventas</span>
                <p class="desc">Obtener historial de ventas</p>
                <div class="params">
                    <table>
                        <tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr>
                        <tr><td><code>fecha_inicio</code></td><td>date</td><td>Formato YYYY-MM-DD</td></tr>
                        <tr><td><code>fecha_fin</code></td><td>date</td><td>Formato YYYY-MM-DD</td></tr>
                        <tr><td><code>estado</code></td><td>string</td><td>Pagada | Pendiente</td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-chart-line"></i> Estadísticas</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/index.php?controlador=api&accion=estadisticas</span>
                <p class="desc">Obtener KPIs del dashboard (inventario y ventas del mes)</p>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-users"></i> Clientes</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/index.php?controlador=api&accion=clientes</span>
                <p class="desc">Obtener lista de clientes</p>
            </div>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-terminal"></i> Ejemplo de Respuesta</h2>
            <pre><code>{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Producto Ejemplo",
            "categoria": "General",
            "stock": 50,
            "precioVentaUSD": 10.99
        }
    ],
    "meta": {
        "total": 100,
        "limit": 50,
        "offset": 0,
        "has_more": true
    }
}</code></pre>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-exclamation-triangle"></i> Códigos de Error</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #334155;"><th style="padding: 10px; text-align: left;">Código</th><th style="padding: 10px; text-align: left;">Descripción</th></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #334155;">400</td><td style="padding: 10px; border-bottom: 1px solid #334155;">Solicitud inválida</td></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #334155;">401</td><td style="padding: 10px; border-bottom: 1px solid #334155;">No autorizado (API key inválida)</td></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #334155;">404</td><td style="padding: 10px; border-bottom: 1px solid #334155;">Recurso no encontrado</td></tr>
                <tr><td style="padding: 10px;">500</td><td style="padding: 10px;">Error interno del servidor</td></tr>
            </table>
        </div>
        
        <p style="text-align: center; color: #64748b; margin-top: 40px;">
            <i class="fas fa-rocket"></i> SaaS Pro API v1.0 - 
            <a href="index.php?controlador=dashboard" style="color: #3b82f6;">Volver al Sistema</a>
        </p>
    </div>
</body>
</html>
