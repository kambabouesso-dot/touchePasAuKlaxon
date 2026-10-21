<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\TripController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Models\AgencyRepository;
use App\Models\TripRepository;
use App\Models\UserRepository;
use App\Services\AuthService;
use App\Services\TripValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

$pdo = Database::connect();
$session = new Session();
$session->start();

$auth = new AuthService(new UserRepository($pdo), $session);
View::share('currentUser', $auth->currentUser());
View::share('csrfToken', $session->csrfToken());

$trips = new TripRepository($pdo);

$router = new Router();
$router->get('/', static function () use ($trips, $auth): void {
    (new HomeController($trips, $auth))->index();
});

$authController = new AuthController($auth, $session);
$router->get('/connexion', [$authController, 'showLogin']);
$router->post('/connexion', [$authController, 'login']);
$router->post('/deconnexion', [$authController, 'logout']);

$tripController = new TripController($auth, $session, $trips, new AgencyRepository($pdo), new TripValidator());
$router->get('/trajets/nouveau', [$tripController, 'create']);
$router->post('/trajets', [$tripController, 'store']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
