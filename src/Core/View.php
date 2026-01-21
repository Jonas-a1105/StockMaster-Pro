<?php
namespace App\Core;

/**
 * Clase para marcar contenido como "seguro" y evitar el escapado automático
 */
class RawString {
    private $value;
    public function __construct($value) { $this->value = $value; }
    public function __toString() { return (string)$this->value; }
}

class View {
    private static $layout = 'main';
    private static $data = [];

    /**
     * Renderiza una vista completa con layout
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string {
        $layout = $layout ?? self::$layout;
        $data = array_merge(self::$data, $data);
        
        // El contenido de la vista se captura en un buffer
        $viewContent = self::renderViewOnly($view, $data);
        
        // El layout recibe el contenido de la vista como $content.
        // Lo marcamos como raw() para que no se escape el HTML de la vista interna.
        return self::renderViewOnly("layouts/$layout", array_merge($data, ['content' => self::raw($viewContent)]));
    }

    /**
     * Renderiza solo la vista (o un componente/parcial)
     */
    public static function renderViewOnly(string $path, array $data = []): string {
        // Lista de claves que se consideran seguras por defecto en componentes/vistas
        $safeKeys = ['attributes', 'content', 'title', 'footer', 'extra_html', 'html'];
        foreach ($safeKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = self::raw($data[$key]);
            }
        }

        // Escapamos los datos automáticamente antes de extraerlos
        $escapedData = self::escapeData($data);
        extract($escapedData);
        
        $file = __DIR__ . "/../../views/$path.php";
        
        if (!file_exists($file)) {
            throw new \Exception("Vista no encontrada: $path");
        }

        ob_start();
        require $file;
        return ob_get_clean();
    }

    /**
     * Escapa datos de forma recursiva (XSS Protection)
     */
    private static function escapeData($data) {
        if (is_array($data)) {
            return array_map([self::class, 'escapeData'], $data);
        }
        
        if ($data instanceof RawString) {
            return (string)$data;
        }

        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Marca un contenido como seguro (no se escapará)
     */
    public static function raw($value) {
        return new RawString($value);
    }

    /**
     * Renderiza un componente reusable
     */
    public static function component(string $name, array $props = []): RawString {
        return self::raw(self::renderViewOnly("components/$name", $props));
    }

    /**
     * Renderiza un parcial
     */
    public static function partial(string $name, array $data = []): RawString {
        return self::raw(self::renderViewOnly("partials/$name", $data));
    }

    /**
     * Establece datos globales para todas las vistas
     */
    public static function share(string $key, $value): void {
        self::$data[$key] = $value;
    }
}
