<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\VentaModel;
use App\Models\Cliente;
use App\Core\Session;

class ReporteController {

    public function index() {
        // 1. Seguridad: Solo usuarios Premium
        if ($_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'Los reportes son una función Premium.');
            redirect('index.php?controlador=dashboard');
        }

        $userId = $_SESSION['user_id'];
        
        // 2. Cargar datos para filtros
        $productoModel = new Producto();
        $clienteModel = new Cliente();
        $productos = $productoModel->obtenerTodos($userId);
        $clientes = $clienteModel->obtenerTodos($userId, '', true);

        $datosReporte = [
            'productos' => $productos,
            'clientes' => $clientes,
            'filtros' => $_POST,
            'reporte' => null
        ];

        // 3. Si se envió el formulario...
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['reporte-tipo'] ?? 'valor-inventario';
            $productoId = (int)($_POST['reporte-producto'] ?? 0);
            $clienteId = (int)($_POST['reporte-cliente'] ?? 0);
            $fechaInicio = $_POST['reporte-fecha-inicio'] ?? '';
            $fechaFin = $_POST['reporte-fecha-fin'] ?? '';
            $limite = (int)($_POST['reporte-limite'] ?? 10);

            $titulo = '';
            $columnas = [];
            $resultados = [];

            $movimientoModel = new Movimiento();
            $ventaModel = new VentaModel();

            switch ($tipo) {
                // ========== REPORTES DE INVENTARIO ==========
                case 'valor-inventario':
                    $titulo = 'Reporte de Valor de Inventario';
                    $columnas = ['ID', 'Producto', 'Categoría', 'Stock', 'P. Compra (USD)', 'Valor Venta (USD)', 'Ganancia Unit. (USD)', 'Ganancia Total (USD)'];
                    
                    foreach ($productos as $p) {
                        $resultados[] = [
                            $p['id'],
                            $p['nombre'],
                            $p['categoria'],
                            $p['stock'],
                            '$' . number_format($p['precioCompraUSD'], 2),
                            '$' . number_format($p['stock'] * $p['precioVentaUSD'], 2),
                            '$' . number_format($p['gananciaUnitariaUSD'], 2),
                            '$' . number_format($p['stock'] * $p['gananciaUnitariaUSD'], 2)
                        ];
                    }
                    break;

                case 'movimientos-producto':
                    $prodNombre = 'Producto Desconocido';
                    foreach($productos as $p) {
                        if($p['id'] == $productoId) { $prodNombre = $p['nombre']; break; }
                    }
                    
                    $titulo = 'Movimientos de: ' . $prodNombre;
                    $columnas = ['Fecha', 'Tipo', 'Motivo', 'Proveedor', 'Cantidad', 'Nota'];
                    
                    $datosMovs = $movimientoModel->obtenerTodos($userId, [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'producto_id' => $productoId
                    ]);
                    
                    foreach ($datosMovs as $m) {
                        $resultados[] = [
                            (new \DateTime($m['fecha']))->format('d/m/Y h:i A'),
                            $m['tipo'],
                            $m['motivo'],
                            $m['proveedor'] ?? '-',
                            ($m['tipo'] === 'Entrada' ? '+' : '-') . $m['cantidad'],
                            $m['nota'] ?? '-'
                        ];
                    }
                    break;

                case 'movimientos-general':
                    $titulo = 'Reporte General de Movimientos';
                    $columnas = ['Fecha', 'Producto', 'Tipo', 'Motivo', 'Proveedor', 'Cantidad', 'Nota'];
                    
                    $datosMovs = $movimientoModel->obtenerTodos($userId, [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]);
                    
                    foreach ($datosMovs as $m) {
                        $resultados[] = [
                            (new \DateTime($m['fecha']))->format('d/m/Y h:i A'),
                            $m['productoNombre'],
                            $m['tipo'],
                            $m['motivo'],
                            $m['proveedor'] ?? '-',
                            ($m['tipo'] === 'Entrada' ? '+' : '-') . $m['cantidad'],
                            $m['nota'] ?? '-'
                        ];
                    }
                    break;

                // ========== REPORTES DE VENTAS ==========
                case 'ventas-periodo':
                    $titulo = 'Reporte de Ventas por Período';
                    if ($fechaInicio && $fechaFin) {
                        $titulo .= ' (' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . ')';
                    }
                    $columnas = ['ID', 'Fecha', 'Cliente', 'Total USD', 'Total VES', 'Método Pago', 'Estado'];
                    
                    $ventas = $ventaModel->obtenerVentas($userId, [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]);
                    
                    foreach ($ventas as $v) {
                        $resultados[] = [
                            '#' . $v['id'],
                            (new \DateTime($v['created_at']))->format('d/m/Y h:i A'),
                            $v['cliente_nombre'] ?? 'Anónimo',
                            '$' . number_format($v['total_usd'], 2),
                            'Bs. ' . number_format($v['total_ves'], 2),
                            $v['metodo_pago'] ?? 'Efectivo',
                            $v['estado_pago'] ?? 'Pagada'
                        ];
                    }
                    break;

                case 'productos-mas-vendidos':
                    $titulo = 'Top ' . $limite . ' Productos Más Vendidos';
                    if ($fechaInicio && $fechaFin) {
                        $titulo .= ' (' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . ')';
                    }
                    $columnas = ['#', 'Producto', 'Cantidad Vendida', 'Total Vendido USD', 'Núm. Ventas'];
                    
                    $topProductos = $ventaModel->obtenerProductosMasVendidos($userId, $fechaInicio, $fechaFin, $limite);
                    
                    $index = 1;
                    foreach ($topProductos as $p) {
                        $resultados[] = [
                            $index++,
                            $p['nombre_producto'],
                            $p['cantidad_vendida'] . ' unidades',
                            '$' . number_format($p['total_vendido_usd'], 2),
                            $p['num_ventas'] . ' ventas'
                        ];
                    }
                    break;

                case 'reporte-ganancias':
                    $titulo = 'Reporte de Ganancias';
                    if ($fechaInicio && $fechaFin) {
                        $titulo .= ' (' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . ')';
                    }
                    $columnas = ['Producto', 'Cant. Vendida', 'Costo Total', 'Venta Total', 'Ganancia', 'Margen %'];
                    
                    $ganancias = $ventaModel->obtenerReporteGanancias($userId, $fechaInicio, $fechaFin);
                    
                    foreach ($ganancias as $g) {
                        $margen = $g['venta_total'] > 0 ? (($g['ganancia_total'] / $g['venta_total']) * 100) : 0;
                        $resultados[] = [
                            $g['nombre_producto'],
                            $g['cantidad_vendida'] . ' unidades',
                            '$' . number_format($g['costo_total'], 2),
                            '$' . number_format($g['venta_total'], 2),
                            '$' . number_format($g['ganancia_total'], 2),
                            number_format($margen, 1) . '%'
                        ];
                    }
                    break;

                case 'cuentas-por-cobrar':
                    $titulo = 'Cuentas por Cobrar (Ventas Pendientes)';
                    $columnas = ['Venta ID', 'Cliente', 'Documento', 'Teléfono', 'Fecha Venta', 'Total USD', 'Días Transcurridos'];
                    
                    $cuentasPorCobrar = $ventaModel->obtenerCuentasPorCobrar($userId, $clienteId > 0 ? $clienteId : null);
                    
                    foreach ($cuentasPorCobrar as $c) {
                        $resultados[] = [
                            '#' . $c['id'],
                            $c['cliente_nombre'],
                            $c['numero_documento'] ?? '-',
                            $c['cliente_telefono'] ?? '-',
                            (new \DateTime($c['created_at']))->format('d/m/Y'),
                            '$' . number_format($c['total_usd'], 2),
                            $c['dias_transcurridos'] . ' días'
                        ];
                    }
                    break;
            }

            // 4. Guardar los resultados en el array para la vista
            $datosReporte['reporte'] = [
                'titulo' => $titulo,
                'columnas' => $columnas,
                'resultados' => $resultados
            ];
        }

        // 5. Renderizar
        $this->render('reportes/index', $datosReporte);
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}