<?php
namespace App\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\NotificacionModel;
use App\Core\EmailService;
use App\Core\Database;
use App\Core\Session; // <-- ¡AÑADIR ESTE!

class MovimientoController {

    public function index() {
        if ($_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'Acceso denegado. Esta es una función Premium.');
            redirect('index.php?controlador=dashboard');
        }
        
        $userId = $_SESSION['user_id']; 
        $movimientoModel = new Movimiento();
        $productoModel = new Producto();
        $proveedorModel = new Proveedor();

        // === Configuración de Paginación ===
        $opcionesLimite = [3, 5, 7, 10, 25, 50, 100];
        $limiteDefault = 10;
        
        $porPagina = (int)($_GET['limite'] ?? $_SESSION['mov_per_page'] ?? $limiteDefault);
        
        if (!in_array($porPagina, $opcionesLimite)) {
            if ($porPagina <= 0) $porPagina = $limiteDefault;
        }

        $_SESSION['mov_per_page'] = $porPagina;

        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) $paginaActual = 1;
        $offset = ($paginaActual - 1) * $porPagina;

        $filtros = [
            'limit' => $porPagina,
            'offset' => $offset
        ];

        $totalRegistros = $movimientoModel->contarTodos($userId, $filtros);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        $movimientosRecientes = $movimientoModel->obtenerTodos($userId, $filtros);

        $productos = $productoModel->obtenerTodos($userId);
        $proveedores = $proveedorModel->obtenerTodos($userId);

        $this->render('movimientos/index', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'movimientos' => $movimientosRecientes,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'porPagina' => $porPagina,
            'opcionesLimite' => $opcionesLimite
        ]);
    }

    public function crear() {
        $userId = $_SESSION['user_id'];
        
        if ($_SESSION['user_plan'] === 'free') {
             redirect('index.php?controlador=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=movimiento&accion=index');
        }

        $productoId = (int)$_POST['mov-producto'];
        $tipo = $_POST['mov-tipo'];
        $motivo = $_POST['mov-motivo'];
        $cantidad = (int)$_POST['mov-cantidad'];
        $proveedorId = (int)$_POST['mov-proveedor'];
        $nota = trim($_POST['mov-nota'] ?? '');

        // Validación de cantidad
        if ($cantidad <= 0) {
            Session::flash('error', 'La cantidad debe ser un número positivo.');
            redirect('index.php?controlador=movimiento&accion=index');
        }

        $productoModel = new Producto();
        $proveedorModel = new Proveedor();
        $movimientoModel = new Movimiento();
        $db = Database::conectar();

        $producto = $productoModel->obtenerPorId($userId, $productoId);
        if (!$producto) {
            Session::flash('error', 'Producto no válido.');
            redirect('index.php?controlador=movimiento&accion=index');
        }
        
        $proveedorNombre = null;
        if ($proveedorId > 0) {
            $proveedor = $proveedorModel->obtenerPorId($userId, $proveedorId);
            if ($proveedor) $proveedorNombre = $proveedor['nombre'];
        }

        // --- ¡LÓGICA DE STOCK CORREGIDA! ---
        if ($tipo === 'Salida') {
            if ($producto['stock'] < $cantidad) {
                Session::flash('error', 'Stock insuficiente para esta salida.');
                redirect('index.php?controlador=movimiento&accion=index');
            }
        }
        // 'Entrada' simplemente suma, no necesita chequeo previo.
        // --- FIN DE CORRECCIÓN ---

        try {
            $db->beginTransaction();
            $productoModel->actualizarStock($userId, $productoId, $cantidad, $tipo);
            $movimientoModel->crear(
                $userId, $productoId, $producto['nombre'], $tipo,
                $motivo, $cantidad, $nota, $proveedorNombre
            );
            $db->commit();
            
            // === VERIFICAR STOCK BAJO Y CREAR NOTIFICACIÓN ===
            if ($tipo === 'Salida') {
                $productoActualizado = $productoModel->obtenerPorId($userId, $productoId);
                $umbral = $_SESSION['stock_umbral'] ?? 10;
                
                if ($productoActualizado && $productoActualizado['stock'] <= $umbral) {
                    $notifModel = new NotificacionModel();
                    $notifModel->crearAlertaStock($userId, $productoActualizado['nombre'], 
                        $productoActualizado['stock'], $umbral);
                    
                    // Enviar email si stock está agotado
                    if ($productoActualizado['stock'] == 0) {
                        try {
                            $emailService = new EmailService();
                            if ($emailService->estaConfigurado()) {
                                $db2 = Database::conectar();
                                $stmt = $db2->prepare("SELECT u.email, c.nombre_negocio FROM usuarios u 
                                    LEFT JOIN configuracion c ON u.id = c.user_id WHERE u.id = ?");
                                $stmt->execute([$userId]);
                                $userData = $stmt->fetch();
                                if ($userData && $userData['email']) {
                                    $emailService->enviarAlertaStock(
                                        $userData['email'],
                                        $userData['nombre_negocio'] ?? 'SaaS Pro',
                                        [['nombre' => $productoActualizado['nombre'], 'stock' => 0]]
                                    );
                                }
                            }
                        } catch (\Exception $emailEx) {
                            error_log("Error enviando email alerta stock: " . $emailEx->getMessage());
                        }
                    }
                }
            }
            // === FIN VERIFICACIÓN ===
            
            Session::flash('success', 'Movimiento registrado correctamente.');
        } catch (\Exception $e) {
            $db->rollBack();
            Session::flash('error', 'Error al registrar el movimiento.');
        }

        redirect('index.php?controlador=movimiento&accion=index');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}