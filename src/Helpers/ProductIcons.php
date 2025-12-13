<?php

namespace App\Helpers;

class ProductIcons
{
    private static $mappings = [
        'coffee' => ['cafe', 'café', 'coffee', 'espresso', 'late', 'capuchino'],
        'cookie' => ['galleta', 'cookie', 'dulce', 'caramelo', 'chocolate', 'snack', 'confite'],
        'bread' => ['pan', 'harina', 'sandwich', 'torta', 'pastel', 'trigo', 'masa'],
        'drink' => ['refresco', 'jugo', 'bebida', 'agua', 'gaseosa', 'coca', 'pepsi', 'liquido'],
        'droplet' => ['aceite', 'salsa', 'vinagre', 'lubricante'],
        'meat' => ['carne', 'pollo', 'res', 'cerdo', 'embutido', 'jamon', 'salchicha'],
        'fish' => ['pescado', 'atun', 'sardina', 'marisco'],
        'carrot' => ['fruta', 'verdura', 'vegetal', 'zanahoria', 'tomate', 'cebolla', 'papa'],
        'tag' => ['ropa', 'camisa', 'pantalon', 'zapato', 'vestido'],
        'device' => ['telefono', 'celular', 'laptop', 'computadora', 'mouse', 'teclado', 'cable', 'cargador'],
        'tool' => ['herramienta', 'martillo', 'clavo', 'tornillo', 'taladro'],
        'medicine' => ['medicina', 'pastilla', 'jarabe', 'farmacia', 'salud'],
        'box' => ['caja', 'paquete', 'bulto']
    ];

    public static function get(string $name, string $category = ''): string
    {
        $search = strtolower($name . ' ' . $category);
        
        foreach (self::$mappings as $icon => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($search, $keyword) !== false) {
                    return $icon;
                }
            }
        }
        
        // Default fallback based on category if name check fails
        if (strpos(strtolower($category), 'alimento') !== false) return 'food';
        if (strpos(strtolower($category), 'bebida') !== false) return 'drink';
        
        return 'box';
    }
    
    /**
     * Devuelve el color de fondo (clase Tailwind) sugerido para el ícono
     */
    public static function getBgColor(string $icon): string
    {
        $colors = [
            'coffee' => 'from-amber-100 to-amber-200 text-amber-600',
            'cookie' => 'from-orange-100 to-orange-200 text-orange-600',
            'bread' => 'from-yellow-100 to-yellow-200 text-yellow-600',
            'drink' => 'from-blue-100 to-blue-200 text-blue-600',
            'droplet' => 'from-cyan-100 to-cyan-200 text-cyan-600',
            'meat' => 'from-red-100 to-red-200 text-red-600',
            'fish' => 'from-sky-100 to-sky-200 text-sky-600',
            'carrot' => 'from-green-100 to-green-200 text-green-600',
            'tag' => 'from-purple-100 to-purple-200 text-purple-600',
            'device' => 'from-indigo-100 to-indigo-200 text-indigo-600',
            'medicine' => 'from-rose-100 to-rose-200 text-rose-600',
            'box' => 'from-slate-100 to-slate-200 text-slate-500' // Default
        ];
        
        return $colors[$icon] ?? 'from-slate-100 to-slate-200 text-slate-500';
    }
}
