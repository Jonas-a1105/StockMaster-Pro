<?php
namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\Constraint\DirectoryExists;

class Logger {
    private static $logger;

    public static function getInstance() {
        if (!self::$logger) {
            self::$logger = new MonologLogger('app');
            $logPath = __DIR__ . '/../../storage/logs/app.log';
            
            // Ensure directory exists
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            self::$logger->pushHandler(new StreamHandler($logPath, MonologLogger::DEBUG));
        }
        return self::$logger;
    }

    public static function info($message, array $context = []) {
        self::getInstance()->info($message, $context);
    }

    public static function error($message, array $context = []) {
        self::getInstance()->error($message, $context);
    }

    public static function warning($message, array $context = []) {
        self::getInstance()->warning($message, $context);
    }
}
