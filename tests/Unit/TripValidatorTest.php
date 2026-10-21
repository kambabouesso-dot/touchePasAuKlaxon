<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\TripValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** Vérifie les contrôles de cohérence d'un trajet. */
final class TripValidatorTest extends TestCase
{
    /**
     * @param array<string, string> $overrides
     * @return array<string, string>
     */
    private function errorsFor(array $overrides = []): array
    {
        $input = array_merge([
            'agence_depart' => '1',
            'agence_arrivee' => '2',
            'date_depart' => '2030-06-01T08:00',
            'date_arrivee' => '2030-06-01T10:00',
            'places_total' => '4',
            'places_disponibles' => '3',
        ], $overrides);

        return (new TripValidator())->validate($input, [1, 2, 3], new DateTimeImmutable('2030-01-01 00:00'));
    }

    /** Un trajet cohérent ne produit aucune erreur. */
    public function testAcceptsValidTrip(): void
    {
        self::assertSame([], $this->errorsFor());
    }

    /** Les agences de départ et d'arrivée doivent différer. */
    public function testRejectsSameAgencies(): void
    {
        self::assertArrayHasKey('agence_arrivee', $this->errorsFor(['agence_arrivee' => '1']));
    }

    /** Une agence absente de la liste est refusée. */
    public function testRejectsUnknownAgency(): void
    {
        self::assertArrayHasKey('agence_depart', $this->errorsFor(['agence_depart' => '99']));
    }

    /** On ne peut pas arriver avant de partir. */
    public function testRejectsArrivalBeforeDeparture(): void
    {
        self::assertArrayHasKey('date_arrivee', $this->errorsFor(['date_arrivee' => '2030-06-01T07:00']));
    }

    /** Un départ dans le passé est refusé. */
    public function testRejectsDepartureInThePast(): void
    {
        self::assertArrayHasKey('date_depart', $this->errorsFor(['date_depart' => '2029-12-31T08:00']));
    }

    /** Les places disponibles ne dépassent pas le total. */
    public function testRejectsAvailableSeatsAboveTotal(): void
    {
        self::assertArrayHasKey('places_disponibles', $this->errorsFor(['places_disponibles' => '5']));
    }

    /** Une valeur non numérique est refusée. */
    public function testRejectsNonNumericSeats(): void
    {
        self::assertArrayHasKey('places_total', $this->errorsFor(['places_total' => 'abc']));
    }
}