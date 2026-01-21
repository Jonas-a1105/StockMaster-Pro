<?php
namespace App\Core;

use Throwable;
use App\Core\Response;

class ExceptionHandler {
    private $response;

    public function __construct() {
        $this->response = new Response();
    }

    /**
     * Registra el manejador global
     */
    public function register(): void {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Maneja excepciones no capturadas
     */
    public function handleException(Throwable $exception): void {
        $code = 500;
        if (method_exists($exception, 'getHttpCode')) {
            $code = $exception->getHttpCode();
        } elseif ($exception->getCode() >= 100 && $exception->getCode() < 600) {
            $code = $exception->getCode();
        }

        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        // Log the error using our Logger
        Logger::error("[EXCEPTION] $message in $file on line $line", [
            'exception' => get_class($exception),
            'stack' => $exception->getTraceAsString()
        ]);

        $isDev = getenv('APP_ENV') === 'dev';
        $data = [
            'exception' => get_class($exception),
            'file' => $file,
            'line' => $line
        ];

        if ($isDev) {
            $data['trace'] = $exception->getTraceAsString();
        }

        if ($exception instanceof \App\Exceptions\ValidationException) {
            $data['validation_errors'] = $exception->getErrors();
        }

        $this->renderError($message, $code, $data);
    }

    /**
     * Maneja errores de PHP (convertidos a excepciones si se desea)
     */
    public function handleError(int $level, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $level)) {
            return false;
        }

        Logger::warning(" [ERROR $level] $message in $file on line $line");

        // En desarrollo podríamos lanzar una excepción
        return false; 
    }

    /**
     * Maneja errores fatales al cierre
     */
    public function handleShutdown(): void {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->renderError("Error Fatal del Sistema", 500);
        }
    }

    /**
     * Renderiza el error según el tipo de petición (JSON o HTML)
     */
    private function renderError(string $message, int $code, array $extraData = []): void {
        $request = new Request();
        
        if ($request->isAjax() || $request->isJson() || (isset($_GET['controlador']) && $_GET['controlador'] === 'api')) {
            $responseBody = [
                'success' => false,
                'error' => [
                    'message' => $message,
                    'code' => $code
                ]
            ];
            
            if (getenv('APP_ENV') === 'dev') {
                $responseBody['debug'] = $extraData;
            } elseif (isset($extraData['validation_errors'])) {
                $responseBody['errors'] = $extraData['validation_errors'];
            }

            $this->response->json($responseBody, $code);
        } else {
            try {
                $viewData = [
                    'message' => $message,
                    'code' => $code
                ];
                if (getenv('APP_ENV') === 'dev') {
                    $viewData['debug'] = $extraData;
                }

                // Asegurar que Response sepa qué código enviar
                http_response_code($code);
                $this->response->view('errors/error', $viewData);
            } catch (Throwable $e) {
                // Fallback extremo si el motor de vistas falla
                if (!headers_sent()) {
                    http_response_code($code);
                }
                echo "<h1>Error $code</h1>";
                echo "<p>" . htmlspecialchars($message) . "</p>";
                if (getenv('APP_ENV') === 'dev') {
                    echo "<pre>" . htmlspecialchars(print_r($extraData, true)) . "</pre>";
                }
                echo "<hr><a href='" . (defined('BASE_URL') ? BASE_URL : '/') . "'>Volver al inicio</a>";
                exit;
            }
        }
    }
}
