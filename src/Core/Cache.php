<?php
namespace App\Core;

/**
 * Sistema de Caché Simple
 * Almacena resultados de consultas frecuentes en archivos
 */
class Cache {
    
    private static $cacheDir;
    private static $defaultTTL = 300; // 5 minutos
    
    /**
     * Inicializar directorio de caché
     */
    public static function init() {
        self::$cacheDir = __DIR__ . '/../../storage/cache';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Obtener valor de caché
     * @param string $key Clave única
     * @return mixed|null Valor o null si no existe/expiró
     */
    public static function get($key) {
        self::init();
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Verificar expiración
        if ($data['expires'] !== null && $data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Guardar valor en caché
     * @param string $key Clave única
     * @param mixed $value Valor a guardar (será serializado a JSON)
     * @param int|null $ttl Tiempo de vida en segundos (null = sin expiración)
     */
    public static function set($key, $value, $ttl = null) {
        self::init();
        
        $ttl = $ttl ?? self::$defaultTTL;
        
        $data = [
            'value' => $value,
            'created' => time(),
            'expires' => $ttl ? time() + $ttl : null
        ];
        
        $file = self::getFilePath($key);
        file_put_contents($file, json_encode($data));
    }
    
    /**
     * Eliminar valor de caché
     */
    public static function delete($key) {
        self::init();
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    /**
     * Limpiar toda la caché
     */
    public static function clear() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Limpiar caché expirada
     */
    public static function clearExpired() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        $now = time();
        $deleted = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['expires'] !== null && $data['expires'] < $now) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Obtener o calcular valor (cache-aside pattern)
     * @param string $key Clave única
     * @param callable $callback Función que genera el valor si no está en caché
     * @param int|null $ttl Tiempo de vida
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value === null) {
            $value = $callback();
            self::set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Generar ruta de archivo para la clave
     */
    private static function getFilePath($key) {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Obtener estadísticas de la caché
     */
    public static function stats() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $expired = 0;
        $now = time();
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = json_decode(file_get_contents($file), true);
            if ($data['expires'] !== null && $data['expires'] < $now) {
                $expired++;
            }
        }
        
        return [
            'total_items' => count($files),
            'expired_items' => $expired,
            'total_size_kb' => round($totalSize / 1024, 2),
            'cache_dir' => self::$cacheDir
        ];
    }
}
