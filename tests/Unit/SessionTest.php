<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Session;
use PHPUnit\Framework\TestCase;

/** Vérifie le jeton CSRF de la session. */
final class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    /** Le jeton est généré une fois puis réutilisé. */
    public function testCsrfTokenIsStable(): void
    {
        $session = new Session();

        self::assertSame($session->csrfToken(), $session->csrfToken());
        self::assertSame(64, strlen($session->csrfToken()));
    }

    /** Seul le jeton exact est accepté. */
    public function testValidatesOnlyTheExactToken(): void
    {
        $session = new Session();
        $token = $session->csrfToken();

        self::assertTrue($session->isValidCsrfToken($token));
        self::assertFalse($session->isValidCsrfToken('autre'));
        self::assertFalse($session->isValidCsrfToken(null));
    }
}
