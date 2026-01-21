<?php
namespace App\Core\Middleware;

interface MiddlewareInterface {
    /**
     * Executes the middleware.
     * Should return true to continue to next middleware, or false/redirect to stop.
     */
    public function handle(): bool;
}
