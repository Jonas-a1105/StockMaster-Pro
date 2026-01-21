<?php
namespace App\Services;

use App\Models\Proveedor;
use App\Models\AuditModel;

class ProveedorService {
    private $proveedorModel;
    private $auditModel;

    public function __construct() {
        $this->proveedorModel = new Proveedor();
        $this->auditModel = new AuditModel();
    }

    public function createProveedor($userId, $data) {
        $data['user_id'] = $userId;
        $nombre = trim($data['nombre'] ?? '');

        if (empty($nombre)) {
            throw new \Exception('El nombre es obligatorio.');
        }

        $proveedorId = $this->proveedorModel->crear($data);

        if ($proveedorId) {
            $this->auditModel->registrar($userId, 'crear', 'proveedor', $proveedorId, $nombre, null, [
                'nombre' => $nombre, 'contacto' => $contacto
            ]);
            return $proveedorId;
        }

        return false;
    }

    public function updateProveedor($userId, $data) {
        $id = (int)($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        $contacto = trim($data['contacto'] ?? '');

        if (empty($nombre)) {
            throw new \Exception('El nombre es obligatorio.');
        }

        $prodActual = $this->proveedorModel->obtenerPorId($userId, $id);
        if (!$prodActual) return false;

        $exito = $this->proveedorModel->actualizar($id, $data);

        if ($exito) {
            $this->auditModel->registrar($userId, 'actualizar', 'proveedor', $id, $nombre, 
                ['nombre' => $prodActual['nombre'], 'contacto' => $prodActual['contacto']],
                ['nombre' => $nombre, 'contacto' => $contacto]
            );
            return true;
        }

        return false;
    }

    public function deleteProveedor($userId, $id) {
        $proveedor = $this->proveedorModel->obtenerPorId($userId, $id);
        if ($proveedor) {
            $this->auditModel->registrar($userId, 'eliminar', 'proveedor', $id, $proveedor['nombre'], null, null);
            return $this->proveedorModel->eliminar($userId, $id);
        }
        return false;
    }
}
