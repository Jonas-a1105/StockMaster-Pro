<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use App\Core\Session;
use \PDO;

class UsuarioModel extends BaseModel {
    protected $table = 'usuarios';
    protected $fillable = [
        'username', 'email', 'password', 'rol', 'plan', 
        'owner_id', 'tasa_dolar', 'es_empleado', 'remember_token',
        'trial_ends_at', 'created_at', 'updated_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    public function findByUsername($username) {
        return QueryBuilder::table('usuarios')
            ->where('username', $username)
            ->first();
    }

    public function getPlanAndTrialById($userId) {
        return QueryBuilder::table('usuarios')
            ->select(['plan', 'trial_ends_at'])
            ->where('id', $userId)
            ->first();
    }

    // --- Funciones de Admin ---
    public function obtenerTodos() {
        $userId = Session::get('user_id', 0);
        return QueryBuilder::table('usuarios')
            ->select(['id', 'email', 'plan', 'rol', 'created_at as fecha_registro', 'trial_ends_at'])
            ->where('id', '!=', $userId)
            ->get();
    }

    /**
     * Obtener miembros del equipo de un usuario (owner)
     */
    public function obtenerEquipo($ownerId) {
        return $this->query()
            ->select(['id', 'email', 'created_at as fecha_registro'])
            ->where('owner_id', $ownerId)
            ->get();
    }

    /**
     * Eliminar miembro del equipo (verificando owner)
     */
    public function eliminarMiembro($id, $ownerId) {
        return $this->query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->delete() > 0;
    }

   // --- FUNCIÓN ACTUALIZADA ---
    /**
     * Actualiza el plan y la fecha de vencimiento
     * @param int $usuarioId
     * @param string $nuevoPlan ('free' o 'premium')
     * @param string|null $fechaVencimiento Fecha Y-m-d H:i:s o NULL para permanente
     */
    public function actualizarPlan($usuarioId, $nuevoPlan, $fechaVencimiento = null) {
        if ($nuevoPlan === 'free') {
            $fechaVencimiento = null;
        }
        
        return QueryBuilder::table('usuarios')
            ->where('id', $usuarioId)
            ->update([
                'plan' => $nuevoPlan,
                'trial_ends_at' => $fechaVencimiento
            ]);
    }

    // --- Funciones de Perfil (Cambio de datos) ---
    public function getPasswordById($userId) {
        $result = QueryBuilder::table('usuarios')
            ->select(['password'])
            ->where('id', $userId)
            ->first();
        return $result ? $result['password'] : null;
    }

    public function updatePasswordById($userId, $newPasswordHash) {
        return QueryBuilder::table('usuarios')
            ->where('id', $userId)
            ->update(['password' => $newPasswordHash]);
    }

    public function updateEmail($userId, $newEmail) {
        try {
            return QueryBuilder::table('usuarios')
                ->where('id', $userId)
                ->update(['email' => $newEmail]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { return false; }
            throw $e;
        }
    }

    public function updateUsername($userId, $newUsername) {
        try {
            return QueryBuilder::table('usuarios')
                ->where('id', $userId)
                ->update(['username' => $newUsername]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { return false; }
            throw $e;
        }
    }

    public function findByEmail($email) {
        return QueryBuilder::table('usuarios')
            ->where('email', $email)
            ->first();
    }

    public function createResetToken($email, $token) {
        QueryBuilder::table('password_resets')->where('email', $email)->delete();
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        return QueryBuilder::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires
        ]);
    }

    public function getResetToken($token) {
        $now = date('Y-m-d H:i:s');
        return QueryBuilder::table('password_resets')
            ->where('token', $token)
            ->where('expires_at', '>', $now)
            ->first();
    }

    public function updatePassword($email, $newPasswordHash) {
        return QueryBuilder::table('usuarios')
            ->where('email', $email)
            ->update(['password' => $newPasswordHash]);
    }

    public function deleteResetToken($token) {
        return QueryBuilder::table('password_resets')
            ->where('token', $token)
            ->delete();
    }
    public function limpiarTokensVencidos() {
        $cutoff = date('Y-m-d H:i:s', strtotime('-1 day'));
        return QueryBuilder::table('password_resets')
            ->where('expires_at', '<', $cutoff)
            ->delete();
    }
    // --- REMEMBER ME (Auto Login) ---
    
    public function setRememberToken($userId, $token) {
        $hash = hash('sha256', $token);
        return QueryBuilder::table('usuarios')
            ->where('id', $userId)
            ->update(['remember_token' => $hash]);
    }

    public function findByRememberToken($token) {
        $hash = hash('sha256', $token);
        return QueryBuilder::table('usuarios')
            ->where('remember_token', $hash)
            ->first();
    }

    public function removeRememberToken($userId) {
        return QueryBuilder::table('usuarios')
            ->where('id', $userId)
            ->update(['remember_token' => null]);
    }
}