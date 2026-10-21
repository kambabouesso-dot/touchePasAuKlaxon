<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\UserRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/** Vérifie que les requêtes utilisateur sont paramétrées. */
final class UserRepositoryTest extends TestCase
{
    /** La recherche par e-mail utilise un paramètre lié et retourne null si absent. */
    public function testFindCredentialsByEmailReturnsNullWhenMissing(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with(['email' => 'x@example.test']);
        $statement->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())->method('prepare')
            ->with(self::stringContains('WHERE email = :email'))
            ->willReturn($statement);

        self::assertNull((new UserRepository($pdo))->findCredentialsByEmail('x@example.test'));
    }

    /** La recherche par identifiant ne sélectionne pas le hash du mot de passe. */
    public function testFindByIdDoesNotSelectPasswordHash(): void
    {
        $row = ['id_utilisateur' => 1, 'nom' => 'A', 'prenom' => 'B', 'email' => 'a@b.test', 'telephone' => '0', 'role' => 'admin'];
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('fetch')->willReturn($row);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())->method('prepare')
            ->with(self::logicalNot(self::stringContains('mot_de_passe_hash')))
            ->willReturn($statement);

        self::assertSame($row, (new UserRepository($pdo))->findById(1));
    }
}
