<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Session;
use App\Models\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

/** Vérifie la connexion et la résolution de l'utilisateur courant. */
final class AuthServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    /** Un mot de passe correct ouvre la session de l'utilisateur. */
    public function testAttemptStoresUserIdWhenCredentialsAreValid(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findCredentialsByEmail')->with('a@example.test')->willReturn([
            'id_utilisateur' => 7,
            'mot_de_passe_hash' => password_hash('secret-longue-phrase', PASSWORD_DEFAULT),
        ]);

        $session = new Session();

        self::assertTrue((new AuthService($users, $session))->attempt('a@example.test', 'secret-longue-phrase'));
        self::assertSame(7, $session->get('user_id'));
    }

    /** Un mauvais mot de passe est refusé sans ouvrir de session. */
    public function testAttemptRejectsWrongPassword(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findCredentialsByEmail')->willReturn([
            'id_utilisateur' => 7,
            'mot_de_passe_hash' => password_hash('bon-mot-de-passe', PASSWORD_DEFAULT),
        ]);

        $session = new Session();

        self::assertFalse((new AuthService($users, $session))->attempt('a@example.test', 'mauvais'));
        self::assertNull($session->get('user_id'));
    }

    /** Un e-mail inconnu est refusé. */
    public function testAttemptRejectsUnknownEmail(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findCredentialsByEmail')->willReturn(null);

        self::assertFalse((new AuthService($users, new Session()))->attempt('inconnu@example.test', 'x'));
    }

    /** Sans identifiant en session, il n'y a pas d'utilisateur courant. */
    public function testCurrentUserIsNullWithoutSession(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->expects(self::never())->method('findById');

        self::assertNull((new AuthService($users, new Session()))->currentUser());
    }

    /** L'utilisateur courant est relu en base à partir de l'identifiant de session. */
    public function testCurrentUserLoadsUserFromSessionId(): void
    {
        $user = [
            'id_utilisateur' => 7,
            'nom' => 'Martin',
            'prenom' => 'Alexandre',
            'email' => 'a@example.test',
            'telephone' => '0600000000',
            'role' => 'utilisateur',
        ];
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->with(7)->willReturn($user);

        $session = new Session();
        $session->set('user_id', 7);

        self::assertSame($user, (new AuthService($users, $session))->currentUser());
    }

    /** Un identifiant de session qui ne correspond plus à un utilisateur est effacé. */
    public function testCurrentUserClearsStaleSessionId(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(null);

        $session = new Session();
        $session->set('user_id', 99);

        self::assertNull((new AuthService($users, $session))->currentUser());
        self::assertNull($session->get('user_id'));
    }
}
