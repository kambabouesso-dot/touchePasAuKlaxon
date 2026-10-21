<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\TripRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/** Vérifie le filtrage, le tri et le résultat du dépôt de trajets. */
final class TripRepositoryTest extends TestCase
{
    /** Seuls les trajets futurs ayant des places sont demandés dans l'ordre croissant. */
    public function testFindsFutureTripsWithAvailableSeatsInDepartureOrder(): void
    {
        $rows = [[
            'date_depart' => '2030-06-01 08:00:00',
            'date_arrivee' => '2030-06-01 10:00:00',
            'places_disponibles' => 2,
            'agence_depart' => 'Paris',
            'agence_arrivee' => 'Lyon',
        ]];
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('fetchAll')
            ->willReturn($rows);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('query')
            ->with(self::callback(static function (string $query): bool {
                return str_contains($query, 'trajet.date_depart >= CURRENT_TIMESTAMP')
                    && str_contains($query, 'trajet.places_disponibles > 0')
                    && str_contains($query, 'ORDER BY trajet.date_depart ASC');
            }))
            ->willReturn($statement);

        $trips = (new TripRepository($pdo))->findUpcomingWithAvailableSeats();

        self::assertSame($rows, $trips);
    }
    /** La création utilise des paramètres liés et retourne l'identifiant inséré. */
    public function testCreateInsertsTripWithBoundParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'date_depart' => '2030-06-01 08:00:00',
                'date_arrivee' => '2030-06-01 10:00:00',
                'places_total' => 4,
                'places_disponibles' => 3,
                'id_auteur' => 5,
                'id_agence_depart' => 1,
                'id_agence_arrivee' => 2,
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('INSERT INTO trajet'))
            ->willReturn($statement);
        $pdo->method('lastInsertId')->willReturn('15');

        $id = (new TripRepository($pdo))->create(5, 1, 2, '2030-06-01 08:00:00', '2030-06-01 10:00:00', 4, 3);

        self::assertSame(15, $id);
    }
    /** La modification est limitée à l'auteur et utilise des paramètres liés. */
    public function testUpdateIsRestrictedToAuthorWithBoundParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'date_depart' => '2030-06-01 08:00:00',
                'date_arrivee' => '2030-06-01 10:00:00',
                'places_total' => 4,
                'places_disponibles' => 2,
                'id_agence_depart' => 1,
                'id_agence_arrivee' => 2,
                'id_trajet' => 15,
                'id_auteur' => 5,
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::logicalAnd(
                self::stringContains('UPDATE trajet'),
                self::stringContains('AND id_auteur = :id_auteur')
            ))
            ->willReturn($statement);

        (new TripRepository($pdo))->update(15, 5, 1, 2, '2030-06-01 08:00:00', '2030-06-01 10:00:00', 4, 2);
    }

    /** La suppression retourne true quand une ligne de l'auteur est supprimée. */
    public function testDeleteReturnsTrueWhenARowIsDeleted(): void
    {
        self::assertTrue($this->runDelete(1));
    }

    /** La suppression retourne false si le trajet n'appartient pas à l'auteur. */
    public function testDeleteReturnsFalseWhenNothingIsDeleted(): void
    {
        self::assertFalse($this->runDelete(0));
    }

    private function runDelete(int $affectedRows): bool
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['id_trajet' => 15, 'id_auteur' => 5]);
        $statement->method('rowCount')->willReturn($affectedRows);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('AND id_auteur = :id_auteur'))
            ->willReturn($statement);

        return (new TripRepository($pdo))->delete(15, 5);
    }
}
