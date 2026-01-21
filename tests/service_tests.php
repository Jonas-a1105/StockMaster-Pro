<?php
namespace App\Tests;

use App\Services\ProductoService;
use App\Services\VentaService;
use App\Services\CompraService;
use App\Services\AuthService;
use App\Core\Database;

require_once __DIR__ . '/../vendor/autoload.php';

class ServiceTest {
    public function run() {
        echo "Running Service Tests...\n";
        $this->testProductoService();
        $this->testVentaService();
        $this->testCompraService();
        $this->testAuthService();
        echo "All Service Tests Passed (Dummy Check)!\n";
    }

    private function testProductoService() {
        echo "- Testing ProductoService...\n";
        $service = new ProductoService();
        // In a real environment, we would use a mock DB or test DB
        // Here we just verify the class can be instantiated and the methods exist
        if (!method_exists($service, 'createProduct')) {
            throw new \Exception("ProductoService::createProduct missing");
        }
        echo "  [OK]\n";
    }

    private function testVentaService() {
        echo "- Testing VentaService...\n";
        $service = new VentaService();
        if (!method_exists($service, 'processCheckout')) {
            throw new \Exception("VentaService::processCheckout missing");
        }
        echo "  [OK]\n";
    }

    private function testCompraService() {
        echo "- Testing CompraService...\n";
        $service = new CompraService();
        if (!method_exists($service, 'processPurchase')) {
            throw new \Exception("CompraService::processPurchase missing");
        }
        echo "  [OK]\n";
    }

    private function testAuthService() {
        echo "- Testing AuthService...\n";
        $service = new AuthService();
        if (!method_exists($service, 'authenticate')) {
            throw new \Exception("AuthService::authenticate missing");
        }
        echo "  [OK]\n";
    }
}

$test = new ServiceTest();
$test->run();
