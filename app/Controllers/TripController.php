<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\AgencyRepository;
use App\Models\TripRepository;
use App\Services\AuthService;
use App\Services\TripValidator;
use DateTimeImmutable;

/** Gestion de la création de trajets par un utilisateur connecté. */
final class TripController
{
    private const FIELDS = [
        'agence_depart', 'agence_arrivee', 'date_depart', 'date_arrivee',
        'places_total', 'places_disponibles',
    ];

    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly TripRepository $trips,
        private readonly AgencyRepository $agencies,
        private readonly TripValidator $validator
    ) {
    }

    /** Affichage du formulaire de création. */
    public function create(): void
    {
        if ($this->auth->currentUser() === null) {
            $this->redirect('/connexion');
            return;
        }

        $this->renderForm([], []);
    }

    /** Validation et enregistrement du trajet. */
    public function store(): void
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            $this->redirect('/connexion');
            return;
        }

        $input = $this->collectInput();

        if (!$this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            $this->renderForm($input, ['form' => 'Session expirée, veuillez réessayer.']);
            return;
        }

        $agencyIds = array_map(
            static fn (array $agency): int => (int) $agency['id_agence'],
            $this->agencies->findAll()
        );
        $errors = $this->validator->validate($input, $agencyIds, new DateTimeImmutable());

        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($input, $errors);
            return;
        }

        $this->trips->create(
            $user['id_utilisateur'],
            (int) $input['agence_depart'],
            (int) $input['agence_arrivee'],
            $this->toSqlDate($input['date_depart']),
            $this->toSqlDate($input['date_arrivee']),
            (int) $input['places_total'],
            (int) $input['places_disponibles']
        );

        $this->redirect('/');
    }

    /**
     * @return array<string, string>
     */
    private function collectInput(): array
    {
        $input = [];

        foreach (self::FIELDS as $field) {
            $value = $_POST[$field] ?? '';
            $input[$field] = is_string($value) ? trim($value) : '';
        }

        return $input;
    }

    /**
     * @param array<string, string> $old
     * @param array<string, string> $errors
     */
    private function renderForm(array $old, array $errors): void
    {
        View::render('trips/create', [
            'pageTitle' => 'Proposer un trajet',
            'agencies' => $this->agencies->findAll(),
            'old' => $old,
            'errors' => $errors,
        ]);
    }

    private function toSqlDate(string $inputValue): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $inputValue);

        return $date === false ? '' : $date->format('Y-m-d H:i:s');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
    }
}