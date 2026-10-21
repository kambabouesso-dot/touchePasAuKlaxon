<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Models\TripRepository;
use App\Models\UserRepository;
use App\Services\AuthService;

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = Database::connect();
$session = new Session();
$session->start();

$auth = new AuthService(new UserRepository($pdo), $session);
View::share('currentUser', $auth->currentUser());
View::share('csrfToken', $session->csrfToken());

$router = new Router();
$router->get('/', static function () use ($pdo): void {
    (new HomeController(new TripRepository($pdo)))->index();
});

$authController = new AuthController($auth, $session);
$router->get('/connexion', [$authController, 'showLogin']);
$router->post('/connexion', [$authController, 'login']);
$router->post('/deconnexion', [$authController, 'logout']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
