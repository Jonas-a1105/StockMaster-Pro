<?php
namespace App\Core\Middleware;

class MiddlewareHandler {
    private $middlewares = [];

    /**
     * Adds a middleware to the stack
     */
    public function add(MiddlewareInterface $middleware) {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Executes all middlewares in the stack
     * Returns true if all passed, false otherwise
     */
    public function run(): bool {
        foreach ($this->middlewares as $middleware) {
            if (!$middleware->handle()) {
                return false;
            }
        }
        return true;
    }
}
