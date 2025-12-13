<?php
namespace App\Core;

class Session {

    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
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
}