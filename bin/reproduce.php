<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$controllers = [
    'dashboard',
    'producto',
    'cliente',
    'venta',
    'acerca',
    'admin',
    'api',
    'compra',
    'config',
    'perfil'
];

foreach ($controllers as $name) {
    $class = 'App\\Controllers\\' . ucfirst($name) . 'Controller';
    echo "Checking $class: " . (class_exists($class) ? 'FOUND' : 'NOT FOUND') . "\n";
}
