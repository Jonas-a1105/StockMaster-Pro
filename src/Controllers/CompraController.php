<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Movimiento;
use App\Models\CompraModel;
use App\Models\VentaModel;

class CompraController {

    public function __construct() {
        if (!isset($_SESSION['user_plan']) || $_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'El Módulo de Compras es Premium.');
            redirect('index.php?controlador=dashboard');
        }
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $compraModel = new CompraModel();
        
        // Paginación
        $porPagina = 20;
        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) $paginaActual = 1;
        $offset = ($paginaActual - 1) * $porPagina;
        
        $filtro = $_GET['estado'] ?? '';
        
        $totalRegistros = $compraModel->contarTodas($userId, $filtro);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        $compras = $compraModel->obtenerTodas($userId, $filtro, $porPagina, $offset);

        $this->render('compras/index', [
            'compras' => $compras, 
            'filtro' => $filtro,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros
        ]);
    }

    public function crear() {
        $userId = $_SESSION['user_id'];
        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->obtenerTodos($userId);
        
        $this->render('compras/crear', ['proveedores' => $proveedores]);
    }

    public function buscarProductos() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $termino = $_GET['term'] ?? '';
        
        $productoModel = new Producto();
        // Usamos la función especial que busca sin importar el stock (definida en el Modelo)
        $productos = $productoModel->buscarParaCompra($userId, $termino);
        
        echo json_encode($productos);
        exit;
    }

    public function guardar() {
        header('Content-Type: application/json');
        
        // --- DEBUG LOG START ---
        $logFile = __DIR__ . '/../../debug_compra.txt';
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] Intento de guardar compra...\n", FILE_APPEND);
        // --- DEBUG LOG END ---
        
        $userId = $_SESSION['user_id'];
        
        $json = file_get_contents('php://input');
        
        // Log del payload
        file_put_contents($logFile, "Payload recibido: " . $json . "\n", FILE_APPEND);
        
        $data = json_decode($json, true);

        if (empty($data['carrito']) || empty($data['proveedor_id'])) {
            file_put_contents($logFile, "ERROR: Datos incompletos.\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $compraModel = new CompraModel();
        $productoModel = new Producto();
        $movimientoModel = new Movimiento();
        
        $proveedorId = $data['proveedor_id'];
        $nroFactura = $data['nro_factura'] ?? 'S/N';
        $estado = $data['estado']; // 'Pagada' o 'Pendiente'
        $fechaEmision = $data['fecha_emision'];
        $fechaVencimiento = $data['fecha_vencimiento'];
        
        $total = 0;
        foreach ($data['carrito'] as $item) {
            $total += $item['costo'] * $item['cantidad'];
        }

        try {
            file_put_contents($logFile, "Iniciando transacción...\n", FILE_APPEND);
            
            // 1. Registrar la Compra
            $compraId = $compraModel->crearCompra($userId, $proveedorId, $nroFactura, $total, $estado, $fechaEmision, $fechaVencimiento);
            file_put_contents($logFile, "Compra creada ID: $compraId\n", FILE_APPEND);

            // 2. Procesar Items
            foreach ($data['carrito'] as $item) {
                file_put_contents($logFile, "Procesando item: " . json_encode($item) . "\n", FILE_APPEND);
                
                // Guardar detalle de la factura SIEMPRE
                $compraModel->crearCompraItem($compraId, $item['id'], $item['cantidad'], $item['costo']);
                
                // Guardar precio histórico por proveedor SIEMPRE
                $compraModel->actualizarPrecioProveedor($item['id'], $proveedorId, $item['costo']);
                
                // --- LÓGICA CONDICIONAL ---
                // Solo sumamos stock y registramos movimiento si está PAGADA
                if ($estado === 'Pagada') {
                    
                    // A. Aumentar Stock
                    $productoModel->actualizarStock($userId, $item['id'], $item['cantidad'], 'Entrada');
                    
                    // B. Actualizar costo actual del producto
                    $prodActual = $productoModel->obtenerPorId($userId, $item['id']);
                    
                    if (!$prodActual) {
                         file_put_contents($logFile, "ERROR FATAL: Producto ID " . $item['id'] . " no encontrado.\n", FILE_APPEND);
                         continue;
                    }

                    // Obtener datos actuales del producto
                    $precioCompraActual = (float)($prodActual['precioCompraUSD'] ?? 0);
                    $precioVentaActual = (float)($prodActual['precioVentaUSD'] ?? 0);
                    $tieneIva = $prodActual['tiene_iva'] ?? 0;
                    $ivaPorcentaje = $prodActual['iva_porcentaje'] ?? 0;
                    
                    // NUEVA LÓGICA: precioCompraUSD ya incluye IVA
                    // Margen = ((Venta / Compra) - 1) * 100
                    $margenPorcentaje = 0;
                    if ($precioCompraActual > 0) {
                        $margenPorcentaje = (($precioVentaActual / $precioCompraActual) - 1) * 100;
                    }
                    
                    // El costo de la factura es el PRECIO BASE (sin IVA)
                    // actualizarCompleto aplicará el IVA automáticamente
                    $nuevoPrecioBase = $item['costo'];
                    
                    $productoModel->actualizarCompleto(
                        $userId, 
                        $item['id'], 
                        $prodActual['nombre'], 
                        $nuevoPrecioBase,  // Precio base sin IVA
                        $tieneIva,
                        $ivaPorcentaje,
                        $margenPorcentaje,
                        $proveedorId, 
                        $prodActual['codigo_barras']
                    );

                    // C. Registrar Movimiento
                    $movimientoModel->crear($userId, $item['id'], $item['nombre'], 'Entrada', 'Compra', $item['cantidad'], "Factura $nroFactura", null);
                }
            }
            
            file_put_contents($logFile, "Transacción completada con éxito.\n", FILE_APPEND);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            file_put_contents($logFile, "EXCEPCIÓN CAPTURADA: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Marcar como pagada y liberar stock
    public function marcarPagada() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $compraId = (int)$_POST['id'];
            
            $compraModel = new CompraModel();
            $productoModel = new Producto();
            $movimientoModel = new Movimiento();
            
            // 1. Verificar que la compra exista y sea de este usuario
            $compra = $compraModel->obtenerPorId($userId, $compraId);
            
            if ($compra && $compra['estado'] === 'Pendiente') {
                
                // 2. Obtener los productos de esa compra
                $items = $compraModel->obtenerItems($compraId);
                
                foreach ($items as $item) {
                    // A. Aumentar Stock AHORA
                    $productoModel->actualizarStock($userId, $item['producto_id'], $item['cantidad'], 'Entrada');
                    
                    // B. Actualizar precio actual con nuevo costo de compra
                    $prodActual = $productoModel->obtenerPorId($userId, $item['producto_id']);
                    if ($prodActual) {
                        // Datos actuales del producto
                        $precioCompraActual = (float)($prodActual['precioCompraUSD'] ?? 0);
                        $precioVentaActual = (float)($prodActual['precioVentaUSD'] ?? 0);
                        $tieneIva = $prodActual['tiene_iva'] ?? 0;
                        $ivaPorcentaje = $prodActual['iva_porcentaje'] ?? 0;
                        
                        // NUEVA LÓGICA: precioCompraUSD ya incluye IVA
                        // Margen = ((Venta / Compra) - 1) * 100
                        $margenPorcentaje = 0;
                        if ($precioCompraActual > 0) {
                            $margenPorcentaje = (($precioVentaActual / $precioCompraActual) - 1) * 100;
                        }
                        
                        // El costo de la factura es el PRECIO BASE (sin IVA)
                        $nuevoPrecioBase = $item['precio_unitario'];
                        
                        $productoModel->actualizarCompleto(
                            $userId, 
                            $item['producto_id'], 
                            $prodActual['nombre'], 
                            $nuevoPrecioBase,  // Precio base sin IVA
                            $tieneIva,
                            $ivaPorcentaje,
                            $margenPorcentaje,  // Ahora es porcentaje correcto
                            $compra['proveedor_id'], 
                            $prodActual['codigo_barras']
                        );
                    }

                    // C. Registrar Movimiento (Entrada Diferida)
                    $nota = "Pago Factura " . $compra['nro_factura'];
                    $movimientoModel->crear($userId, $item['producto_id'], $item['nombre_producto'], 'Entrada', 'Compra (Pago Diferido)', $item['cantidad'], $nota, null);
                }

                // 3. Cambiar estado a Pagada
                $compraModel->pagarCompra($compraId);
                Session::flash('success', 'Factura pagada. El stock ha sido sumado al inventario.');
            }
        }
        redirect('index.php?controlador=compra&accion=index');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}