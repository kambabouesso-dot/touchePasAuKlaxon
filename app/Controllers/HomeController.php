<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\TripRepository;
use App\Services\AuthService;

/** Préparation et affichage de la page publique d'accueil. */
final class HomeController
{
    /** Création du contrôleur avec son dépôt de trajets. */
    public function __construct(
        private readonly TripRepository $trips,
        private readonly AuthService $auth
    ) {
    }

    /** Affichage des trajets futurs disposant encore de places. les coordonnées des auteurs sont réservées aux utilisateurs connectés. */
    public function index(): void
    {
        $trips = $this->auth->currentUser() === null
            ? $this->trips->findUpcomingWithAvailableSeats()
            : $this->trips->findUpcomingWithDetails();
    
        View::render('home/index', [
            'pageTitle' => 'Trajets disponibles',
            'trips' => $trips,
        ]);
    }
}
