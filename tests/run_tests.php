<?php
/**
 * Tests Automatizados - Framework Simple
 * Ejecutar: php tests/run_tests.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Producto;
use App\Models\VentaModel;
use App\Models\NotificacionModel;
use App\Core\Cache;

class TestRunner {
    private $passed = 0;
    private $failed = 0;
    private $errors = [];
    
    public function run() {
        echo "\n========================================\n";
        echo "  SaaS Pro - Tests Automatizados\n";
        echo "========================================\n\n";
        
        // Ejecutar suites de tests
        $this->testDatabase();
        $this->testProductoModel();
        $this->testCache();
        $this->testNotificaciones();
        
        // Resumen
        $this->printSummary();
    }
    
    private function assert($condition, $testName) {
        if ($condition) {
            $this->passed++;
            echo "  ✓ $testName\n";
        } else {
            $this->failed++;
            $this->errors[] = $testName;
            echo "  ✗ $testName\n";
        }
    }
    
    private function testDatabase() {
        echo "📦 Database Connection\n";
        
        try {
            $db = Database::conectar();
            $this->assert($db instanceof PDO, 'Conexión a la base de datos');
            
            $stmt = $db->query("SELECT 1");
            $this->assert($stmt !== false, 'Ejecutar query simple');
            
        } catch (Exception $e) {
            $this->assert(false, 'Database: ' . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testProductoModel() {
        echo "📦 ProductoModel\n";
        
        try {
            $model = new Producto();
            $this->assert($model !== null, 'Instanciar ProductoModel');
            
            // Test obtener productos (user_id = 1 para pruebas)
            $productos = $model->obtenerTodos(1, '', 10, 0);
            $this->assert(is_array($productos), 'obtenerTodos retorna array');
            
            // Test contar
            $total = $model->contarTodos(1);
            $this->assert(is_numeric($total), 'contarTodos retorna número');
            
            // Test KPIs
            $kpis = $model->obtenerKPIsDashboard(1, 10);
            $this->assert(isset($kpis['valorTotalVentaUSD']), 'obtenerKPIsDashboard tiene valorTotalVentaUSD');
            
        } catch (Exception $e) {
            $this->assert(false, 'ProductoModel: ' . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testCache() {
        echo "📦 Cache System\n";
        
        try {
            // Test set y get
            Cache::set('test_key', ['foo' => 'bar'], 60);
            $value = Cache::get('test_key');
            $this->assert($value['foo'] === 'bar', 'set y get valor');
            
            // Test remember
            $computed = Cache::remember('computed_test', function() {
                return 'computed_value';
            }, 60);
            $this->assert($computed === 'computed_value', 'remember pattern');
            
            // Test delete
            Cache::delete('test_key');
            $deleted = Cache::get('test_key');
            $this->assert($deleted === null, 'delete elimina valor');
            
            // Test stats
            $stats = Cache::stats();
            $this->assert(isset($stats['total_items']), 'stats retorna estadísticas');
            
            // Limpiar
            Cache::delete('computed_test');
            
        } catch (Exception $e) {
            $this->assert(false, 'Cache: ' . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testNotificaciones() {
        echo "📦 NotificacionModel\n";
        
        try {
            $model = new NotificacionModel();
            $this->assert($model !== null, 'Instanciar NotificacionModel');
            
            // Test contar
            $count = $model->contarNoLeidas(1);
            $this->assert(is_numeric($count), 'contarNoLeidas retorna número');
            
            // Test obtener
            $notifs = $model->obtenerNoLeidas(1, 5);
            $this->assert(is_array($notifs), 'obtenerNoLeidas retorna array');
            
        } catch (Exception $e) {
            $this->assert(false, 'NotificacionModel: ' . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function printSummary() {
        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100) : 0;
        
        echo "========================================\n";
        echo "  Resumen: $this->passed/$total tests pasados ($percentage%)\n";
        echo "========================================\n";
        
        if ($this->failed > 0) {
            echo "\n❌ Tests fallidos:\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
            exit(1);
        } else {
            echo "\n✅ Todos los tests pasaron!\n";
            exit(0);
        }
    }
}

// Ejecutar tests
$runner = new TestRunner();
$runner->run();
