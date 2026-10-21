<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Database;
use App\Core\Router;
use App\Models\TripRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();
$router->get('/', static function (): void {
    $controller = new HomeController(
        new TripRepository(Database::connect())
    );
    $controller->index();
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
