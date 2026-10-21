<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\TripRepository;

/** Affichage de la page publique d'accueil. */
final class HomeController
{
    /** Création du contrôleur avec son dépôt de trajets. */
    public function __construct(private readonly TripRepository $trips)
    {
    }

    /** Affichage des trajets futurs disposant encore de places. */
    public function index(): void
    {
        View::render('home/index', [
            'pageTitle' => 'Trajets disponibles',
            'trips' => $this->trips->findUpcomingWithAvailableSeats(),
        ]);
    }
}
