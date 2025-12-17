<?php
namespace App\Core;

class Session {

    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            if (!is_dir(sys_get_temp_dir())) {
                mkdir(sys_get_temp_dir(), 0777, true);
            }
            // PARCHE DE SEGURIDAD: Cookies robustas
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '', // Dominio actual
                'secure' => false, // True solo si hay HTTPS (En localhost/XAMPP suele ser false)
                'httponly' => true, // Previene acceso via JS (XSS)
                'samesite' => 'Strict' // Previene CSRF
            ]);
            
            session_save_path(sys_get_temp_dir());
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
     * Verifica si el usuario está logueado
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']);
    }
}