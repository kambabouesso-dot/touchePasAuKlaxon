<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\TripRepository;

/** Prépare et affiche la page publique d'accueil. */
final class HomeController
{
    /** Crée le contrôleur avec son dépôt de trajets. */
    public function __construct(private readonly TripRepository $trips)
    {
    }

    /** Affiche les trajets futurs disposant encore de places. */
    public function index(): void
    {
        View::render('home/index', [
            'pageTitle' => 'Trajets disponibles',
            'trips' => $this->trips->findUpcomingWithAvailableSeats(),
        ]);
    }
}
