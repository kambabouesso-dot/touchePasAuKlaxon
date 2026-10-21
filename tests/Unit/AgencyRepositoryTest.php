<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\AgencyRepository;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/** Vérifie les écritures sur les agences et la gestion des contraintes d'intégrité. */
final class AgencyRepositoryTest extends TestCase
{
    public function testCreateInsertsAgencyWithBoundName(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with(['nom' => 'Rennes']);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('INSERT INTO agence'))
            ->willReturn($statement);

        self::assertTrue((new AgencyRepository($pdo))->create('Rennes'));
    }

    public function testCreateReturnsFalseOnDuplicateName(): void
    {
        self::assertFalse((new AgencyRepository($this->failingPdo('23000')))->create('Paris'));
    }

    public function testRenameUpdatesAgencyWithBoundParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with(['nom' => 'Brest', 'id' => 4]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('UPDATE agence'))
            ->willReturn($statement);

        self::assertTrue((new AgencyRepository($pdo))->rename(4, 'Brest'));
    }

    public function testDeleteRemovesAgency(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with(['id' => 4]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('DELETE FROM agence'))
            ->willReturn($statement);

        self::assertTrue((new AgencyRepository($pdo))->delete(4));
    }

    public function testDeleteReturnsFalseWhenAgencyIsUsedByTrips(): void
    {
        self::assertFalse((new AgencyRepository($this->failingPdo('23000')))->delete(1));
    }

    public function testOtherDatabaseErrorsAreNotHidden(): void
    {
        $this->expectException(PDOException::class);

        (new AgencyRepository($this->failingPdo('HY000')))->delete(1);
    }

    private function failingPdo(string $sqlState): PDO
    {
        $exception = new PDOException('erreur');
        $exception->errorInfo = [$sqlState, 0, 'erreur'];
        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($exception, $sqlState);
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willThrowException($exception);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        return $pdo;
    }
}
