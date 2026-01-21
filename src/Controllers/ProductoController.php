<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\ProductoService;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\AuditModel;
use App\Core\Session;
use App\Core\Database;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;

use App\Domain\Enums\Capability;

class ProductoController extends BaseController {
    private $productoService;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::ADVANCED_INVENTORY, 'El Inventario es exclusivo del Plan Premium.');
        $this->productoService = new ProductoService();
    }

    public function index() {
        $productoModel = new Producto();
        $proveedorModel = new Proveedor();
        
        $busqueda = $this->request->query('buscar', '');
        $totalRegistros = $productoModel->contarTodos($this->userId, $busqueda);
        
        $pagData = $this->getPaginationData($totalRegistros, 10, 'stock_per_page');
        
        $productos = $productoModel->obtenerTodos(
            $this->userId, 
            $busqueda, 
            $pagData['limit'], 
            $pagData['offset']
        );
        $proveedores = $proveedorModel->obtenerTodos($this->userId);

        // Alertas de Stock
        $this->gestionarAlertasStock($this->userId, $productoModel);

        $categorias = ['General', 'Electrónica', 'Hogar', 'Alimentos', 'Tecnología', 'Salud', 'Belleza', 'Limpieza', 'Bebidas', 'Snacks'];
        sort($categorias);

        $tasaCambio = (float)Session::get('tasa_bcv', 30);

        return $this->response->view('productos/index', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'paginaActual' => $pagData['page'],
            'totalPaginas' => $pagData['totalPages'],
            'totalRegistros' => $totalRegistros,
            'tasaCambio' => $tasaCambio,
            'porPagina' => $pagData['limit'],
            'opcionesLimite' => [5, 10, 25, 50, 100] // Usar los estándar del BaseController
        ]);
    }

    private function gestionarAlertasStock($userId, $productoModel) {
        $bloqueMinutos = floor(date('i') / 10);
        $alertasMostradasKey = 'stock_alerts_' . date('Y-m-d-H') . '-' . $bloqueMinutos;
        
        if (!Session::get($alertasMostradasKey)) {
            $umbralStock = (int)Session::get('stock_umbral', 10);
            $alertas = $productoModel->obtenerAlertasStock($userId, $umbralStock);
            
            // obtenerAlertasStock retorna ['bajo' => [...], 'agotado' => [...]]
            $agotados = $alertas['agotado'] ?? [];
            $bajos = $alertas['bajo'] ?? [];

            if (!empty($agotados)) {
                $msg = '🚫 Stock agotado: ' . implode(', ', array_slice(array_column($agotados, 'nombre'), 0, 3));
                if (count($agotados) > 3) $msg .= ' y ' . (count($agotados) - 3) . ' más';
                Session::flash('error', $msg);
            }
            
            if (!empty($bajos)) {
                $msg = '⚠️ Stock bajo: ' . implode(', ', array_slice(array_column($bajos, 'nombre'), 0, 3));
                if (count($bajos) > 3) $msg .= ' y ' . (count($bajos) - 3) . ' más';
                Session::flash('warning', $msg);
            }
            Session::set($alertasMostradasKey, true);
        }
    }
    
    public function apiBuscar() {
        $termino = $this->request->query('term', '');
        $productoModel = new Producto();
        $productos = $productoModel->obtenerTodos($this->userId, $termino, 50, 0);
        return $this->response->json($productos);
    }

    public function exportar() {
        $rows = $this->productoService->getExportData($this->userId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="inventario_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function importar() {
        if (!$this->request->isPost()) {
             return $this->response->redirect('index.php?controlador=producto&accion=index');
        }

        $file = $_FILES['archivo_csv']['tmp_name'] ?? null;
        
        if ($file && is_uploaded_file($file)) {
            // Validar MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file);
            finfo_close($finfo);

            $permitidos = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
            
            if (in_array($mime, $permitidos)) {
                $result = $this->productoService->importFromCsv($this->userId, $file);
                if ($result['success']) {
                    $mensaje = "✅ Importación completada: {$result['count']} productos.";
                    Session::flash('success', $mensaje);
                } else {
                    Session::flash('error', $result['message']);
                }
            } else {
                Session::flash('error', 'El archivo debe ser un CSV válido.');
            }
        } else {
            Session::flash('error', 'Error al subir el archivo.');
        }

        return $this->response->redirect('index.php?controlador=producto&accion=index');
    }

    public function crear() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $rules = [
            'nombre' => 'required|min:3',
            'sku'    => 'required|unique:productos,sku',
            'precioVentaUSD' => 'numeric',
            'stock'  => 'numeric'
        ];

        if (!$this->request->validate($rules, Database::conectar())) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al crear producto.');
        }

        $nuevoId = $this->productoService->createProduct($this->userId, $this->request->all());
        if ($nuevoId) {
            return $this->response->json(['success' => true, 'message' => 'Producto creado correctamente']);
        }
        
        throw new AppException('Error al crear producto.', 500);
    }

    public function apiObtener() {
        $id = $this->request->query('id', 0, 'int');
        if ($id === 0) { 
            throw new AppException('ID de producto no válido', 400); 
        }
        
        $productoModel = new Producto();
        $producto = $productoModel->obtenerPorId($this->userId, $id);

        if ($producto) {
            $producto['margen'] = 0;
            if ($producto['precioCompraUSD'] > 0) {
                $producto['margen'] = (($producto['precioVentaUSD'] / $producto['precioCompraUSD']) - 1) * 100;
            }
            return $this->response->json($producto);
        }
        
        throw new AppException('Producto no encontrado', 404);
    }

    public function actualizar() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $id = $this->request->input('id', 0, 'int');
        $rules = [
            'id'     => 'required|numeric',
            'nombre' => 'required|min:3',
            'sku'    => "required|unique:productos,sku,$id",
            'precioVentaUSD' => 'numeric',
            'stock'  => 'numeric'
        ];

        if (!$this->request->validate($rules, Database::conectar())) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al actualizar producto.');
        }

        if ($this->productoService->updateProduct($this->userId, $this->request->all())) {
            $productoModel = new Producto();
            $producto = $productoModel->obtenerPorId($this->userId, $id);
            return $this->response->json(['success' => true, 'producto' => $producto]);
        }
        
        throw new AppException('Error al actualizar el producto.', 500);
    }

    public function eliminarMasivo() {
        if (!$this->request->isPost()) {
             return $this->response->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $ids = $this->request->input('ids', []);

        if (empty($ids)) {
             return $this->response->json(['success' => false, 'message' => 'No se seleccionaron productos'], 400);
        }

        $productoModel = new Producto();
        $audit = new AuditModel();
        $deletedCount = 0;

        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;

            $producto = $productoModel->obtenerPorId($this->userId, $id);
            if ($producto) {
                 $audit->registrar($this->userId, 'eliminar_masivo', 'producto', $id, $producto['nombre'], 
                    ['nombre' => $producto['nombre'], 'stock' => $producto['stock']], null
                 );
                 if ($productoModel->eliminar($this->userId, $id)) {
                      $deletedCount++;
                 }
            }
        }

        if ($deletedCount > 0) {
            Session::flash('success', "$deletedCount productos eliminados.");
            return $this->response->json(['success' => true, 'message' => "$deletedCount eliminados."]);
        }
        return $this->response->json(['success' => false, 'message' => 'No se pudo eliminar.'], 500);
    }

    public function eliminar() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=producto&accion=index');
        }

        $id = $this->request->input('id', 0, 'int');
        if ($id > 0) {
            $productoModel = new Producto();
            $producto = $productoModel->obtenerPorId($this->userId, $id);
            if ($producto) {
                $audit = new AuditModel();
                $audit->registrar($this->userId, 'eliminar', 'producto', $id, $producto['nombre'], 
                    ['nombre' => $producto['nombre'], 'stock' => $producto['stock']], null
                );
                $productoModel->eliminar($this->userId, $id);
                Session::flash('success', 'Producto eliminado.');
            }
        }
        return $this->response->redirect('index.php?controlador=producto&accion=index');
    }
    
    public function apiObtenerAlertas() {
        $umbral = $this->request->query('umbral', 10, 'int');
        $productoModel = new Producto();
        return $this->response->json($productoModel->obtenerAlertasStock($this->userId, $umbral));
    }
}
