<?php
namespace App\Services;

use App\Models\Cliente;
use App\Models\AuditModel;

class ClienteService {
    private $clienteModel;
    private $auditModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
        $this->auditModel = new AuditModel();
    }

    public function createCliente($userId, $data) {
        $data['user_id'] = $userId;
        $nombre = trim($data['nombre'] ?? '');

        if (empty($nombre)) {
            throw new \Exception('El nombre es obligatorio.');
        }

        $clienteId = $this->clienteModel->crear($data);

        if ($clienteId) {
            $this->auditModel->registrar($userId, 'crear', 'cliente', $clienteId, $nombre, null, [
                'nombre' => $nombre, 'documento' => $numeroDocumento, 'tipo' => $tipoCliente
            ]);
            return $clienteId;
        }

        return false;
    }

    public function updateCliente($userId, $data) {
        $id = (int)($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');

        if (empty($nombre)) {
            throw new \Exception('El nombre es obligatorio.');
        }

        $clienteActual = $this->clienteModel->obtenerPorId($userId, $id);
        if (!$clienteActual) return false;

        $exito = $this->clienteModel->actualizar($id, $data);

        if ($exito) {
            $this->auditModel->registrar($userId, 'actualizar', 'cliente', $id, $nombre, 
                ['nombre' => $clienteActual['nombre'], 'limite' => $clienteActual['limite_credito']],
                ['nombre' => $nombre, 'limite' => $limiteCredito]
            );
            return true;
        }

        return false;
    }

    public function deactivateCliente($userId, $id) {
        $cliente = $this->clienteModel->obtenerPorId($userId, $id);
        if ($cliente) {
            $this->auditModel->registrar($userId, 'desactivar', 'cliente', $id, $cliente['nombre'], null, null);
            return $this->clienteModel->desactivar($userId, $id);
        }
        return false;
    }
}
