<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Fournit les lectures de trajets nécessaires à l'application. */
final class TripRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array{
     *     date_depart: string,
     *     date_arrivee: string,
     *     places_disponibles: int|string,
     *     agence_depart: string,
     *     agence_arrivee: string
     * }>
     */
    public function findUpcomingWithAvailableSeats(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                trajet.date_depart,
                trajet.date_arrivee,
                trajet.places_disponibles,
                agence_depart.nom AS agence_depart,
                agence_arrivee.nom AS agence_arrivee
             FROM trajet
             INNER JOIN agence AS agence_depart
                ON agence_depart.id_agence = trajet.id_agence_depart
             INNER JOIN agence AS agence_arrivee
                ON agence_arrivee.id_agence = trajet.id_agence_arrivee
             WHERE trajet.date_depart >= CURRENT_TIMESTAMP
                AND trajet.places_disponibles > 0
             ORDER BY trajet.date_depart ASC'
        );

        return $statement->fetchAll();
    }
}
