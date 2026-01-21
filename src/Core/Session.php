<?php
namespace App\Core;

class Session {

    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            $sessionPath = __DIR__ . '/../../storage/sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0777, true);
            }
            session_save_path($sessionPath);

            // PARCHE DE SEGURIDAD: Cookies robustas
            // En desarrollo (localhost), Lax es a veces más compatible que Strict para redirecciones
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => null, // Dejar null para que tome el actual automáticamente
                'secure' => false, 
                'httponly' => true, 
                'samesite' => 'Lax' 
            ]);
            
            session_start();
        }
    }

    /**
     * Establece un mensaje flash
     * @param string $key La clave (ej: 'success', 'error')
     * @param string $message El mensaje a mostrar
     */
    public static function flash($key, $message) {
        self::init();
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Obtiene y limpia un mensaje flash
     * @param string $key La clave a obtener
     * @return string|null El mensaje, si existe
     */
    public static function getFlash($key) {
        self::init();
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    /**
     * Comprueba si existe algún mensaje flash
     */
    public static function hasFlash() {
        self::init();
        return isset($_SESSION['flash']) && !empty($_SESSION['flash']);
    }

    /**
     * Obtiene un valor de la sesión
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Establece un valor en la sesión
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Elimina un valor de la sesión
     */
    public static function delete($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Verifica si el usuario está logueado
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']);
    }

    /**
     * Regenera el ID de sesión
     */
    public static function regenerate() {
        self::init();
        session_regenerate_id(true);
    }

    /**
     * Genera o recupera un token CSRF
     */
    public static function csrfToken() {
        self::init();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica el token CSRF
     */
    public static function verifyCsrf($token) {
        self::init();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}