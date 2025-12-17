<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Proveedor;
use App\Models\AuditModel;
use App\Core\Session;

class ProductoController {

    public function __construct() {
        if (!isset($_SESSION['user_plan']) || $_SESSION['user_plan'] === 'free') {
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Función exclusiva Premium.']);
                exit;
            } else {
                Session::flash('error', 'El Inventario es exclusivo del Plan Premium.');
                redirect('index.php?controlador=free');
            }
        }
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $productoModel = new Producto();
        $proveedorModel = new Proveedor();
        
        // === Configuración de Paginación ===
        $opcionesLimite = [3, 5, 7, 10, 25, 50, 100];
        $limiteDefault = 10;
        
        // 1. Obtener límite solicitado (GET) o guardado (SESSION) o default
        $porPagina = (int)($_GET['limite'] ?? $_SESSION['stock_per_page'] ?? $limiteDefault);
        
        // 2. Validar que sea una opción válida (o al menos positiva)
        if (!in_array($porPagina, $opcionesLimite)) {
            // Si el valor no está en la lista permitida, usamos el más cercano o el default
            // Para flexibilidad, permitimos cualquier valid positivo, pero si queremos ser estrictos:
            // $porPagina = $limiteDefault; 
            if ($porPagina <= 0) $porPagina = $limiteDefault;
        }

        // 3. Guardar preferencia en sesión
        $_SESSION['stock_per_page'] = $porPagina;

        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) $paginaActual = 1;
        $offset = ($paginaActual - 1) * $porPagina;

        $busqueda = $_GET['buscar'] ?? '';
        $totalRegistros = $productoModel->contarTodos($userId, $busqueda);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        $productos = $productoModel->obtenerTodos($userId, $busqueda, $porPagina, $offset);
        $proveedores = $proveedorModel->obtenerTodos($userId);

        // === NUEVA LÓGICA: Detectar productos con stock bajo/agotado ===
        // Solo mostrar alertas cada 10 minutos para no molestar al usuario
        $bloqueMinutos = floor(date('i') / 10); // 0-5 (bloques de 10 min)
        $alertasMostradasKey = 'stock_alerts_' . date('Y-m-d-H') . '-' . $bloqueMinutos;
        
        if (!isset($_SESSION[$alertasMostradasKey])) {
            $umbralStock = (int)($_SESSION['stock_umbral'] ?? 10);
            $productosAgotados = [];
            $productosBajos = [];
            
            // Revisar TODOS los productos, no solo los de la página actual
            $todosProductos = $productoModel->obtenerTodos($userId, '', 9999, 0);
            
            foreach ($todosProductos as $producto) {
                if ($producto['stock'] == 0) {
                    $productosAgotados[] = $producto['nombre'];
                } elseif ($producto['stock'] <= $umbralStock && $producto['stock'] > 0) {
                    $productosBajos[] = $producto['nombre'] . ' (' . $producto['stock'] . ' uds)';
                }
            }
            
            // Crear flash messages
            if (!empty($productosAgotados)) {
                $msg = '🚫 Stock agotado: ' . implode(', ', array_slice($productosAgotados, 0, 3));
                if (count($productosAgotados) > 3) $msg .= ' y ' . (count($productosAgotados) - 3) . ' más';
                Session::flash('error', $msg);
            }
            
            if (!empty($productosBajos)) {
                $msg = '⚠️ Stock bajo: ' . implode(', ', array_slice($productosBajos, 0, 3));
                if (count($productosBajos) > 3) $msg .= ' y ' . (count($productosBajos) - 3) . ' más';
                Session::flash('warning', $msg);
            }
            
            // Marcar que ya se mostraron las alertas esta hora
            $_SESSION[$alertasMostradasKey] = true;
        }
        // === FIN NUEVA LÓGICA ===

        // Definir categorías estándar
        $categorias = [
            'General', 'Electrónica', 'Hogar', 'Alimentos', 
            'Tecnología', 'Salud', 'Belleza', 'Limpieza', 'Bebidas', 'Snacks'
        ];
        sort($categorias);

        // Obtener tasa de cambio (prioridad: sesión > DB > Default)
        $tasaCambio = $_SESSION['tasa_bcv'] ?? 0;
        if ($tasaCambio <= 0) {
            $tasaCambio = 30; // Valor por defecto si no hay sesión
        }

        $this->render('productos/index', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'tasaCambio' => $tasaCambio,
            'porPagina' => $porPagina,
            'opcionesLimite' => $opcionesLimite // Pasar opciones a la vista
        ]);
    }

    
    public function apiBuscar() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $termino = $_GET['term'] ?? '';
        
        $productoModel = new Producto();
        $productos = $productoModel->obtenerTodos($userId, $termino, 50, 0);
        
        echo json_encode($productos);
        exit;
    }

    // --- EXPORTAR CSV CON PROVEEDORES E IVA ---
    public function exportar() {
        $userId = $_SESSION['user_id'];
        $productoModel = new Producto();
        
        $productos = $productoModel->obtenerTodos($userId, '', 999999, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="inventario_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'Nombre', 
            'Categoria', 
            'Stock', 
            'Precio Base (Sin IVA)', 
            'Tiene IVA',
            'IVA %',
            'Margen %', 
            'Codigo Barras',
            'Proveedor Nombre',
            'Proveedor Contacto',
            'Proveedor Telefono',
            'Proveedor Email'
        ]);

        foreach ($productos as $p) {
            $precioCompra = (float)$p['precioCompraUSD'];
            $tieneIva = !empty($p['tiene_iva']) && $p['tiene_iva'] == 1;
            $ivaPorcentaje = (float)($p['iva_porcentaje'] ?? 0);
            
            // Calcular precio base (sin IVA)
            $precioBase = $precioCompra;
            if ($tieneIva && $ivaPorcentaje > 0) {
                $precioBase = $precioCompra / (1 + $ivaPorcentaje / 100);
            }
            
            // Calcular margen
            $margen = 0;
            if ($precioCompra > 0) {
                $margen = (($p['precioVentaUSD'] / $precioCompra) - 1) * 100;
            }
            
            $proveedorNombre = $p['nombre_proveedor'] ?? '';
            $proveedorContacto = '';
            $proveedorTelefono = '';
            $proveedorEmail = '';
            
            if (!empty($p['proveedor_id'])) {
                $proveedorModel = new Proveedor();
                $proveedor = $proveedorModel->obtenerPorId($userId, $p['proveedor_id']);
                if ($proveedor) {
                    $proveedorNombre = $proveedor['nombre'] ?? '';
                    $proveedorContacto = $proveedor['contacto'] ?? '';
                    $proveedorTelefono = $proveedor['telefono'] ?? '';
                    $proveedorEmail = $proveedor['email'] ?? '';
                }
            }
            
            fputcsv($output, [
                $p['nombre'],
                $p['categoria'],
                $p['stock'],
                number_format($precioBase, 2, '.', ''),
                $tieneIva ? 'Si' : 'No',
                $tieneIva ? number_format($ivaPorcentaje, 0) : '0',
                number_format($margen, 2, '.', ''),
                $p['codigo_barras'] ?? '',
                $proveedorNombre,
                $proveedorContacto,
                $proveedorTelefono,
                $proveedorEmail
            ]);
        }
        fclose($output);
        exit;
    }

    // --- IMPORTAR CSV CON PROVEEDORES E IVA ---
    public function importar() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
            $file = $_FILES['archivo_csv']['tmp_name'];
            
            if (is_uploaded_file($file)) {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $headers = fgetcsv($handle);
                    $numColumnas = count($headers);
                    
                    $productoModel = new Producto();
                    $proveedorModel = new Proveedor();
                    $movimientoModel = new Movimiento();
                    
                    $contadorProductos = 0;
                    $contadorProveedoresCreados = 0;
                    $errores = [];

                    try {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $nombre = trim($data[0] ?? '');
                            $categoria = trim($data[1] ?? 'Varios');
                            $stock = (int)($data[2] ?? 0);
                            $precioBase = (float)($data[3] ?? 0);
                            
                            // Formato nuevo con IVA (12 columnas)
                            if ($numColumnas >= 12) {
                                $tieneIva = strtolower(trim($data[4] ?? '')) === 'si' ? 1 : 0;
                                $ivaPorcentaje = (float)($data[5] ?? 0);
                                $margen = (float)($data[6] ?? 30);
                                $codigo = trim($data[7] ?? '') ?: null;
                                $proveedorNombre = trim($data[8] ?? '');
                                $proveedorContacto = trim($data[9] ?? '') ?: null;
                                $proveedorTelefono = trim($data[10] ?? '') ?: null;
                                $proveedorEmail = trim($data[11] ?? '') ?: null;
                            } else {
                                // Formato antiguo (sin IVA)
                                $tieneIva = 0;
                                $ivaPorcentaje = 0;
                                $margen = (float)($data[4] ?? 30);
                                $codigo = trim($data[5] ?? '') ?: null;
                                $proveedorNombre = trim($data[6] ?? '');
                                $proveedorContacto = trim($data[7] ?? '') ?: null;
                                $proveedorTelefono = trim($data[8] ?? '') ?: null;
                                $proveedorEmail = trim($data[9] ?? '') ?: null;
                            }
                            
                            $proveedorId = null;
                            if (!empty($proveedorNombre)) {
                                $proveedorExistente = $proveedorModel->obtenerPorNombre($userId, $proveedorNombre);
                                if ($proveedorExistente) {
                                    $proveedorId = $proveedorExistente['id'];
                                } else {
                                    $proveedorId = $proveedorModel->crear($userId, $proveedorNombre, $proveedorContacto, $proveedorTelefono, $proveedorEmail);
                                    if ($proveedorId) $contadorProveedoresCreados++;
                                }
                            }

                            if (!empty($nombre)) {
                                $productoId = $productoModel->crear($userId, $nombre, $categoria, $stock, $precioBase, $tieneIva, $ivaPorcentaje, $margen, $proveedorId ?? 0, $codigo);
                                
                                if ($productoId) {
                                    $movimientoModel->crear($userId, $productoId, $nombre, 'Entrada', 'Importación Masiva', $stock, 'Carga desde CSV', null);
                                    $contadorProductos++;
                                } else {
                                    $errores[] = "No se pudo crear: $nombre";
                                }
                            }
                        }
                        
                        fclose($handle);
                        
                        $mensaje = "✅ Importación completada:<br>";
                        $mensaje .= "• $contadorProductos productos importados<br>";
                        if ($contadorProveedoresCreados > 0) {
                            $mensaje .= "• $contadorProveedoresCreados proveedores nuevos creados";
                        }
                        if (!empty($errores)) {
                            $mensaje .= "<br>⚠️ Errores: " . implode(', ', array_slice($errores, 0, 5));
                        }
                        
                        Session::flash('success', $mensaje);
                        
                    } catch (\Exception $e) {
                        Session::flash('error', 'Error: ' . $e->getMessage());
                    }
                } else {
                    Session::flash('error', 'No se pudo leer el archivo CSV.');
                }
            } else {
                Session::flash('error', 'Error al subir el archivo.');
            }
        }
        redirect('index.php?controlador=producto&accion=index');
    }

    public function crear() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            file_put_contents('debug_producto_crear.txt', print_r($_POST, true));
            if (empty($_POST['nombre'])) {
                echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
                exit;
            }

            $nombre = $_POST['nombre'];
            $categoria = $_POST['categoria'];
            $stock = (int)$_POST['stock'];
            
            // Campos IVA
            $precioBase = (float)$_POST['precio_base'];
            $tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;
            $ivaPorcentaje = $tieneIva ? (float)($_POST['iva_porcentaje'] ?? 0) : 0;
            
            $margen = (float)$_POST['margen_ganancia'];
            $proveedorId = (int)($_POST['proveedor_id'] ?? 0);
            $codigoBarras = trim($_POST['codigo_barras'] ?? '');

            $productoModel = new Producto();
            
            try {
                file_put_contents('debug_producto_before_call.txt', "Calling crear with: $nombre");
                $nuevoId = $productoModel->crear(
                    $userId, $nombre, $categoria, $stock,
                    $precioBase, $tieneIva, $ivaPorcentaje, $margen,
                    $proveedorId, $codigoBarras
                );

                if ($nuevoId) {
                    $nombreProveedor = null;
                    if ($proveedorId > 0) {
                        $proveedorModel = new Proveedor();
                        $provData = $proveedorModel->obtenerPorId($userId, $proveedorId);
                        if ($provData) $nombreProveedor = $provData['nombre'];
                    }

                    $movimientoModel = new Movimiento();
                    $movimientoModel->crear($userId, $nuevoId, $nombre, 'Entrada', 'Stock Inicial', $stock, 'Registro inicial', $nombreProveedor);
                    
                    // Registrar auditoría
                    $audit = new AuditModel();
                    $audit->registrar($userId, 'crear', 'producto', $nuevoId, $nombre, null, [
                        'nombre' => $nombre, 'categoria' => $categoria, 'stock' => $stock, 'precio' => $precioBase
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Producto creado correctamente']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error DB: Posible código de barras duplicado.']);
                    exit;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
        exit;
    }

    public function apiObtener() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) { echo json_encode(['error' => 'ID no válido']); exit; }
        
        $productoModel = new Producto();
        $producto = $productoModel->obtenerPorId($userId, $id);

        if ($producto) {
            // Margen = ((Venta / Compra) - 1) * 100
            // Compra ya incluye IVA, así que el cálculo es directo
            $producto['margen'] = 0;
            if ($producto['precioCompraUSD'] > 0) {
                $producto['margen'] = (($producto['precioVentaUSD'] / $producto['precioCompraUSD']) - 1) * 100;
            }
            echo json_encode($producto);
        } else {
            echo json_encode(['error' => 'No encontrado']);
        }
        exit;
    }

    public function actualizar() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $nombre = $_POST['nombre'];
            
            // Campos IVA
            $precioBase = (float)$_POST['precio_base'];
            $tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;
            $ivaPorcentaje = $tieneIva ? (float)($_POST['iva_porcentaje'] ?? 0) : 0;
            
            $margen = (float)$_POST['margen_ganancia'];
            $codigoBarras = trim($_POST['codigo_barras'] ?? '');

            $productoModel = new Producto();
            $prodActual = $productoModel->obtenerPorId($userId, $id);
            $proveedorId = $prodActual['proveedor_id'] ?? 0;
            if (isset($_POST['proveedor_id'])) $proveedorId = (int)$_POST['proveedor_id'];

            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : $prodActual['stock']; // Obtener stock o mantener actual

            try {
                $exito = $productoModel->actualizarCompleto(
                    $userId, $id, $nombre,
                    $precioBase, $tieneIva, $ivaPorcentaje, $margen,
                    $proveedorId, $codigoBarras, $stock
                );
                
                if ($exito) {
                    $producto = $productoModel->obtenerPorId($userId, $id);
                    
                    // Registrar auditoría
                    $audit = new AuditModel();
                    $audit->registrar($userId, 'actualizar', 'producto', $id, $nombre, 
                        ['nombre' => $prodActual['nombre'], 'precio' => $prodActual['precioCompraUSD'], 'stock' => $prodActual['stock']],
                        ['nombre' => $nombre, 'precio' => $precioBase, 'stock' => $stock]
                    );
                    
                    echo json_encode(['success' => true, 'producto' => $producto]);
                    exit;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        exit;
    }

    // --- ELIMINACIÓN MASIVA ---
    public function eliminarMasivo() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             echo json_encode(['success' => false, 'message' => 'Método no permitido']);
             exit;
        }

        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
             echo json_encode(['success' => false, 'message' => 'No se seleccionaron productos']);
             exit;
        }

        $productoModel = new Producto();
        $audit = new AuditModel();
        $deletedCount = 0;

        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;

            $producto = $productoModel->obtenerPorId($userId, $id);
            if ($producto) {
                 // Registrar auditoría
                 $audit->registrar($userId, 'eliminar_masivo', 'producto', $id, $producto['nombre'], 
                    ['nombre' => $producto['nombre'], 'stock' => $producto['stock']],
                    null
                 );
                 // Usamos eliminar directamente del modelo
                 if ($productoModel->eliminar($userId, $id)) {
                     $deletedCount++;
                 }
            }
        }

        if ($deletedCount > 0) {
            Session::flash('success', "$deletedCount productos eliminados correctamente.");
            echo json_encode(['success' => true, 'message' => "$deletedCount productos eliminados."]);
        } else {
            echo json_encode(['success' => false, 'message' => "No se pudieron eliminar los productos."]);
        }
        exit;
    }

    public function eliminar() {
        $userId = $_SESSION['user_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'] ?? 0;
            if ($id > 0) {
                $productoModel = new Producto();
                $producto = $productoModel->obtenerPorId($userId, $id);
                
                if ($producto) {
                    // Registrar auditoría antes de eliminar
                    $audit = new AuditModel();
                    $audit->registrar($userId, 'eliminar', 'producto', $id, $producto['nombre'], 
                        ['nombre' => $producto['nombre'], 'stock' => $producto['stock']],
                        null
                    );
                }
                
                $productoModel->eliminar($userId, $id);
                Session::flash('success', 'Producto eliminado.');
            }
        }
        redirect('index.php?controlador=producto&accion=index');
    }
    
    public function apiObtenerAlertas() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $umbral = (int)($_GET['umbral'] ?? 10);
        $productoModel = new Producto();
        echo json_encode($productoModel->obtenerAlertasStock($userId, $umbral));
        exit;
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }

}