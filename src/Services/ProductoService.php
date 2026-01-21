<?php
namespace App\Services;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\AuditModel;
use App\Models\Proveedor;
use App\Core\Session;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use App\Domain\Enums\MovementType;

class ProductoService {
    private $productoModel;
    private $movimientoModel;
    private $auditModel;
    private $proveedorModel;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->auditModel = new AuditModel();
        $this->proveedorModel = new Proveedor();
    }

    public function createProduct($userId, $data) {
        $nombre = $data['nombre'] ?? '';
        $categoria = $data['categoria'] ?? 'Varios';
        
        $stockValue = (int)($data['stock'] ?? 0);
        $precioBaseValue = (float)($data['precio_base'] ?? 0);
        $tieneIva = isset($data['tiene_iva']) ? 1 : 0;
        $ivaPorcentaje = $tieneIva ? (float)($data['iva_porcentaje'] ?? 0) : 0;
        $margen = (float)($data['margen_ganancia'] ?? 0);
        $proveedorId = (int)($data['proveedor_id'] ?? 0);
        $codigoBarras = trim($data['codigo_barras'] ?? '');

        // Cálculos
        $precioCompraUSD = $precioBaseValue;
        if ($tieneIva && $ivaPorcentaje > 0) {
            $precioCompraUSD = $precioCompraUSD * (1 + ($ivaPorcentaje / 100));
        }
        $precioVentaUSD = $precioCompraUSD * (1 + ($margen / 100));
        $gananciaUnitariaUSD = $precioVentaUSD - $precioCompraUSD;
        $codigo = !empty($codigoBarras) ? $codigoBarras : 'PROD-' . time() . '-' . mt_rand(1000, 9999);

        $nuevoId = $this->productoModel->crear([
            'user_id' => $userId,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'categoria' => $categoria,
            'stock' => $stockValue,
            'precioCompraUSD' => round($precioCompraUSD, 2),
            'precioVentaUSD' => round($precioVentaUSD, 2),
            'gananciaUnitariaUSD' => round($gananciaUnitariaUSD, 2),
            'proveedor_id' => $proveedorId > 0 ? $proveedorId : null,
            'codigo_barras' => !empty($codigoBarras) ? $codigoBarras : null,
            'tiene_iva' => $tieneIva,
            'iva_porcentaje' => $tieneIva ? round($ivaPorcentaje, 2) : 0
        ]);

        if ($nuevoId) {
            $this->movimientoModel->crear($userId, $nuevoId, $nombre, MovementType::ENTRADA->value, 'Stock Inicial', $stockValue, 'Registro inicial', null);
            
            $this->auditModel->registrar($userId, 'crear', 'producto', $nuevoId, $nombre, null, [
                'nombre' => $nombre, 'categoria' => $categoria, 'stock' => $stockValue, 'precio' => $precioBaseValue
            ]);

            return $nuevoId;
        }

        return false;
    }

    public function updateProduct($userId, $data) {
        $id = (int)$data['id'];
        $prodActual = $this->productoModel->obtenerPorId($userId, $id);
        if (!$prodActual) return false;

        $nombre = $data['nombre'] ?? $prodActual['nombre'];
        $precioBaseValue = (float)($data['precio_base'] ?? 0);
        $tieneIva = isset($data['tiene_iva']) ? 1 : 0;
        $ivaPorcentaje = $tieneIva ? (float)($data['iva_porcentaje'] ?? 0) : 0;
        $margen = (float)($data['margen_ganancia'] ?? 0);
        $codigoBarras = trim($data['codigo_barras'] ?? $prodActual['codigo_barras']);
        $proveedorId = (int)($data['proveedor_id'] ?? $prodActual['proveedor_id']);
        $stockValue = isset($data['stock']) ? (int)$data['stock'] : (int)$prodActual['stock'];

        // Repetir cálculos
        $precioCompraUSD = $precioBaseValue;
        if ($tieneIva && $ivaPorcentaje > 0) {
            $precioCompraUSD = $precioCompraUSD * (1 + ($ivaPorcentaje / 100));
        }
        $precioVentaUSD = $precioCompraUSD * (1 + ($margen / 100));
        $gananciaUnitariaUSD = $precioVentaUSD - $precioCompraUSD;

        $updateData = [
            'nombre' => $nombre,
            'stock' => $stockValue,
            'precioCompraUSD' => round($precioCompraUSD, 2),
            'precioVentaUSD' => round($precioVentaUSD, 2),
            'gananciaUnitariaUSD' => round($gananciaUnitariaUSD, 2),
            'proveedor_id' => $proveedorId > 0 ? $proveedorId : null,
            'codigo_barras' => !empty($codigoBarras) ? $codigoBarras : null,
            'tiene_iva' => $tieneIva,
            'iva_porcentaje' => $tieneIva ? round($ivaPorcentaje, 2) : 0
        ];

        $exito = $this->productoModel->actualizarCompleto($id, $updateData);

        if ($exito) {
            $this->auditModel->registrar($userId, 'actualizar', 'producto', $id, $nombre, 
                ['nombre' => $prodActual['nombre'], 'precio' => $prodActual['precioCompraUSD'], 'stock' => $prodActual['stock']],
                ['nombre' => $nombre, 'precio' => $precioBaseValue, 'stock' => $stockValue]
            );
            return true;
        }

        return false;
    }

    public function deleteProduct($userId, $id) {
        $producto = $this->productoModel->obtenerPorId($userId, $id);
        if ($producto) {
            $this->auditModel->registrar($userId, 'eliminar', 'producto', $id, $producto['nombre'], 
                ['nombre' => $producto['nombre'], 'stock' => $producto['stock']],
                null
            );
            return $this->productoModel->eliminar($userId, $id);
        }
        return false;
    }

    public function getStockAlerts($userId, $umbral = null) {
        $umbral = $umbral ?? (int)($_SESSION['stock_umbral'] ?? 10);
        return $this->productoModel->obtenerAlertasStock($userId, $umbral);
    }

    /**
     * Exporta el inventario a un formato de datos para CSV
     */
    public function getExportData($userId) {
        $productos = $this->productoModel->obtenerTodos($userId, '', 999999, 0);
        $rows = [[
            'Nombre', 'Categoria', 'Stock', 'Precio Base (Sin IVA)', 'Tiene IVA',
            'IVA %', 'Margen %', 'Codigo Barras', 'Proveedor Nombre',
            'Proveedor Contacto', 'Proveedor Telefono', 'Proveedor Email'
        ]];

        foreach ($productos as $p) {
            $precioCompra = (float)$p['precioCompraUSD'];
            $tieneIva = !empty($p['tiene_iva']) && $p['tiene_iva'] == 1;
            $ivaPorcentaje = (float)($p['iva_porcentaje'] ?? 0);
            
            $precioBase = $tieneIva && $ivaPorcentaje > 0 ? $precioCompra / (1 + $ivaPorcentaje / 100) : $precioCompra;
            $margen = $precioCompra > 0 ? (($p['precioVentaUSD'] / $precioCompra) - 1) * 100 : 0;
            
            $proveedorNombre = $p['nombre_proveedor'] ?? '';
            $proveedorContacto = '';
            $proveedorTelefono = '';
            $proveedorEmail = '';
            
            if (!empty($p['proveedor_id'])) {
                $proveedor = $this->proveedorModel->obtenerPorId($userId, $p['proveedor_id']);
                if ($proveedor) {
                    $proveedorNombre = $proveedor['nombre'] ?? '';
                    $proveedorContacto = $proveedor['contacto'] ?? '';
                    $proveedorTelefono = $proveedor['telefono'] ?? '';
                    $proveedorEmail = $proveedor['email'] ?? '';
                }
            }
            
            $rows[] = [
                $p['nombre'], $p['categoria'], $p['stock'],
                number_format($precioBase, 2, '.', ''), $tieneIva ? 'Si' : 'No',
                $tieneIva ? number_format($ivaPorcentaje, 0) : '0',
                number_format($margen, 2, '.', ''), $p['codigo_barras'] ?? '',
                $proveedorNombre, $proveedorContacto, $proveedorTelefono, $proveedorEmail
            ];
        }
        return $rows;
    }

    /**
     * Importa productos desde un archivo CSV
     */
    public function importFromCsv($userId, $filePath) {
        if (!is_file($filePath) || !($handle = fopen($filePath, "r"))) {
            return ['success' => false, 'message' => 'No se pudo leer el archivo CSV.'];
        }

        $headers = fgetcsv($handle);
        $numColumnas = count($headers);
        $contadorProductos = 0;
        $contadorProveedoresCreados = 0;
        $errores = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $nombre = trim($data[0] ?? '');
            if (empty($nombre)) continue;

            $categoria = trim($data[1] ?? 'Varios');
            $stockVal = (int)($data[2] ?? 0);
            $precioBase = (float)($data[3] ?? 0);
            
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
                $tieneIva = 0; $ivaPorcentaje = 0;
                $margen = (float)($data[4] ?? 30);
                $codigo = trim($data[5] ?? '') ?: null;
                $proveedorNombre = trim($data[6] ?? '');
                $proveedorContacto = trim($data[7] ?? '') ?: null;
                $proveedorTelefono = trim($data[8] ?? '') ?: null;
                $proveedorEmail = trim($data[9] ?? '') ?: null;
            }

            $proveedorId = 0;
            if (!empty($proveedorNombre)) {
                $proveedorExistente = $this->proveedorModel->obtenerPorNombre($userId, $proveedorNombre);
                if ($proveedorExistente) {
                    $proveedorId = $proveedorExistente['id'];
                } else {
                    $proveedorId = $this->proveedorModel->crear([
                        'user_id' => $userId, 
                        'nombre' => $proveedorNombre, 
                        'contacto' => $proveedorContacto, 
                        'telefono' => $proveedorTelefono, 
                        'email' => $proveedorEmail
                    ]);
                    if ($proveedorId) $contadorProveedoresCreados++;
                }
            }

            $productoId = $this->createProduct($userId, [
                'nombre' => $nombre,
                'categoria' => $categoria,
                'stock' => $stockVal,
                'precio_base' => $precioBase,
                'tiene_iva' => $tieneIva ? 'on' : null,
                'iva_porcentaje' => $ivaPorcentaje,
                'margen_ganancia' => $margen,
                'codigo_barras' => $codigo,
                'proveedor_id' => $proveedorId
            ]);
            
            if ($productoId) {
                $contadorProductos++;
            } else {
                $errores[] = "No se pudo crear: $nombre";
            }
        }
        fclose($handle);

        return [
            'success' => true,
            'count' => $contadorProductos,
            'providers_created' => $contadorProveedoresCreados,
            'errors' => $errores
        ];
    }
}
