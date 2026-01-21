<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Session;
use App\Models\UsuarioModel;
use App\Domain\Enums\UserPlan;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;
 
class ConfigController extends BaseController {
    private $usuarioModel;
 
    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
 
        if ($this->userPlan === UserPlan::FREE->value) {
            if ($this->request->isAjax()) {
                throw new ForbiddenException('Función exclusiva Premium.');
            }
            return $this->response->redirect('index.php?controlador=premium');
        }
    }

    public function index() {
        $userId = Session::get('user_id');
        $config = $this->usuarioModel->find($userId);
        return $this->response->view('config/index', ['config' => $config]);
    }

    public function guardarTasa() {
        if ($tasa > 0) {
            Session::set('tasa_bcv', $tasa);
            
            try {
                $this->usuarioModel->update(Session::get('user_id'), ['tasa_dolar' => $tasa]);
                return $this->response->json(['success' => true, 'message' => 'Tasa actualizada y guardada en base de datos.']);
            } catch (\Exception $e) {
                // Si falla el DB update, al menos se guarda en sesión
                return $this->response->json(['success' => true, 'message' => 'Tasa actualizada (solo sesión temporal).']);
            }
        }
        
        throw new AppException('La tasa debe ser mayor a cero.', 400);
    }
    
    public function obtenerTasa() {
        $tasa = (float)Session::get('tasa_bcv', 0);
        
        if ($tasa <= 0) {
            $user = $this->usuarioModel->find(Session::get('user_id'));
            if ($user && isset($user['tasa_dolar']) && $user['tasa_dolar'] > 0) {
                $tasa = (float)$user['tasa_dolar'];
                Session::set('tasa_bcv', $tasa);
            }
        }
        
        return $this->response->json(['success' => true, 'tasa' => $tasa]);
    }

    public function guardar() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $userId = Session::get('user_id');
        $data = [
            'empresa_nombre' => $this->request->input('nombre'),
            'empresa_direccion' => $this->request->input('direccion'),
            'empresa_telefono' => $this->request->input('telefono'),
        ];

        // Procesar Logo usando Request wrapper y finfo para seguridad real
        if ($this->request->hasFile('logo')) {
            $logoFile = $this->request->file('logo');
            
            // Validar mediante contenido real
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $logoFile['tmp_name']);
            finfo_close($finfo);
            
            $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($mime, $permitidos)) {
                $ext = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
                // Forzar extensión según MIME si es posible, o sanear
                $ext = strtolower($ext);
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $ext = ($mime === 'image/png') ? 'png' : 'jpg';
                }
                
                $nombreArchivo = 'logo_' . $userId . '.' . $ext;
                $rutaDestino = __DIR__ . '/../../public/uploads/' . $nombreArchivo;
                
                if (!file_exists(__DIR__ . '/../../public/uploads/')) {
                    mkdir(__DIR__ . '/../../public/uploads/', 0777, true);
                }

                if (move_uploaded_file($logoFile['tmp_name'], $rutaDestino)) {
                    $data['empresa_logo'] = $nombreArchivo;
                }
            } else {
                Session::flash('error', 'El archivo no es una imagen válida.');
            }
        }

        $this->usuarioModel->update($userId, $data);
        Session::flash('success', 'Configuración guardada.');
        
        return $this->response->redirect('index.php?controlador=config');
    }
}
