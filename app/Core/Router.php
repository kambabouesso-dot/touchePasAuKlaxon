<?php

declare(strict_types=1);

namespace App\Core;

/** Enregistre et distribue les routes HTTP de l'application. */
final class Router
{
    /** @var array<string, array<string, callable(): void>> */
    private array $routes = [];

    /** Enregistre une route GET. */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /** Exécute la route correspondant à la méthode et au chemin demandés. */
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            http_response_code(400);
            echo 'Requête invalide.';
            return;
        }

        $handler = $this->routes[strtoupper($method)][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo 'Page introuvable.';
            return;
        }

        $handler();
    }
}
