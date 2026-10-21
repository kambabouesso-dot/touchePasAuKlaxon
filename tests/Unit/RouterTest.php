<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

/** Vérifie le comportement du routeur HTTP. */
final class RouterTest extends TestCase
{
    /** La route GET correspond au chemin même si l'URL contient une query string. */
    public function testDispatchesGetRouteWithoutQueryString(): void
    {
        $router = new Router();
        $wasCalled = false;
        $router->get('/', static function () use (&$wasCalled): void {
            $wasCalled = true;
        });

        $router->dispatch('GET', '/?page=2');

        self::assertTrue($wasCalled);
    }

    /** Une route inconnue renvoie le statut HTTP 404. */
    public function testReturnsNotFoundForUnknownRoute(): void
    {
        $router = new Router();

        ob_start();
        $router->dispatch('GET', '/unknown');
        $response = ob_get_clean();

        self::assertSame('Page introuvable.', $response);
    }
}
