<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Ressort les trajets nécessaires à l'application. */
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

    /**
     * @return list<array{
     *     id_trajet: int|string,
     *     date_depart: string,
     *     date_arrivee: string,
     *     places_disponibles: int|string,
     *     places_total: int|string,
     *     agence_depart: string,
     *     agence_arrivee: string,
     *     auteur_nom: string,
     *     auteur_prenom: string,
     *     auteur_email: string,
     *     auteur_telephone: string
     * }>
     */
    public function findUpcomingWithDetails(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                trajet.id_trajet,
                trajet.date_depart,
                trajet.date_arrivee,
                trajet.places_disponibles,
                trajet.places_total,
                agence_depart.nom AS agence_depart,
                agence_arrivee.nom AS agence_arrivee,
                utilisateur.nom AS auteur_nom,
                utilisateur.prenom AS auteur_prenom,
                utilisateur.email AS auteur_email,
                utilisateur.telephone AS auteur_telephone
             FROM trajet
             INNER JOIN agence AS agence_depart
                ON agence_depart.id_agence = trajet.id_agence_depart
             INNER JOIN agence AS agence_arrivee
                ON agence_arrivee.id_agence = trajet.id_agence_arrivee
             INNER JOIN utilisateur
                ON utilisateur.id_utilisateur = trajet.id_auteur
             WHERE trajet.date_depart >= CURRENT_TIMESTAMP
                AND trajet.places_disponibles > 0
             ORDER BY trajet.date_depart ASC'
        );

        return $statement->fetchAll();
    }

    /** Enregistre un trajet et retourne son identifiant. */
    public function create(
        int $authorId,
        int $departureAgencyId,
        int $arrivalAgencyId,
        string $departure,
        string $arrival,
        int $seatsTotal,
        int $seatsAvailable
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO trajet
                (date_depart, date_arrivee, places_total, places_disponibles,
                 id_auteur, id_agence_depart, id_agence_arrivee)
             VALUES
                (:date_depart, :date_arrivee, :places_total, :places_disponibles,
                 :id_auteur, :id_agence_depart, :id_agence_arrivee)'
        );
        $statement->execute([
            'date_depart' => $departure,
            'date_arrivee' => $arrival,
            'places_total' => $seatsTotal,
            'places_disponibles' => $seatsAvailable,
            'id_auteur' => $authorId,
            'id_agence_depart' => $departureAgencyId,
            'id_agence_arrivee' => $arrivalAgencyId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
