<?php
namespace App\Services;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\VentaModel;
use App\Models\Cliente;

class ReporteService {
    private $productoModel;
    private $movimientoModel;
    private $ventaModel;
    private $clienteModel;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new Cliente();
    }

    public function generateReport($userId, $type, $filters = []) {
        $productoId = (int)($filters['reporte-producto'] ?? 0);
        $clienteId = (int)($filters['reporte-cliente'] ?? 0);
        $fechaInicio = $filters['reporte-fecha-inicio'] ?? '';
        $fechaFin = $filters['reporte-fecha-fin'] ?? '';
        $limite = (int)($filters['reporte-limite'] ?? 10);

        $titulo = '';
        $columnas = [];
        $resultados = [];

        switch ($type) {
            case 'valor-inventario':
                $titulo = 'Reporte de Valor de Inventario';
                $columnas = ['ID', 'Producto', 'Categoría', 'Stock', 'P. Compra (USD)', 'Valor Venta (USD)', 'Ganancia Unit. (USD)', 'Ganancia Total (USD)'];
                $productos = $this->productoModel->obtenerTodos($userId, '', 999999);
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
                $producto = $this->productoModel->obtenerPorId($userId, $productoId);
                $prodNombre = $producto ? $producto['nombre'] : 'Producto Desconocido';
                $titulo = 'Movimientos de: ' . $prodNombre;
                $columnas = ['Fecha', 'Tipo', 'Motivo', 'Proveedor', 'Cantidad', 'Nota'];
                $datosMovs = $this->movimientoModel->obtenerTodos($userId, [
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
                $datosMovs = $this->movimientoModel->obtenerTodos($userId, [
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

            case 'ventas-periodo':
                $titulo = 'Reporte de Ventas por Período';
                if ($fechaInicio && $fechaFin) {
                    $titulo .= ' (' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . ')';
                }
                $columnas = ['ID', 'Fecha', 'Cliente', 'Total USD', 'Total VES', 'Método Pago', 'Estado'];
                $ventas = $this->ventaModel->obtenerVentas($userId, [
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
                $topProductos = $this->ventaModel->obtenerProductosMasVendidos($userId, $fechaInicio, $fechaFin, $limite);
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
                $ganancias = $this->ventaModel->obtenerReporteGanancias($userId, $fechaInicio, $fechaFin);
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
                $cuentasPorCobrar = $this->ventaModel->obtenerCuentasPorCobrar($userId, $clienteId > 0 ? $clienteId : null);
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

        return [
            'titulo' => $titulo,
            'columnas' => $columnas,
            'resultados' => $resultados
        ];
    }

    public function getFilterData($userId) {
        return [
            'productos' => $this->productoModel->obtenerTodos($userId, '', 999999),
            'clientes' => $this->clienteModel->obtenerTodos($userId, '', true)
        ];
    }
}
