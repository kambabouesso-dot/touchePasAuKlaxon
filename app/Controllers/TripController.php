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

/** Gère la création, la modification et la suppression de trajets par leur auteur. */
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

    /** Affiche le formulaire de création. */
    public function create(): void
    {
        if ($this->auth->currentUser() === null) {
            $this->redirect('/connexion');
            return;
        }

        $this->renderForm([], []);
    }

    /** Valide et enregistre un nouveau trajet. */
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

        $errors = $this->validationErrors($input);

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

    /** Affiche le formulaire de modification d'un trajet dont l'utilisateur est l'auteur. */
    public function edit(): void
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            $this->redirect('/connexion');
            return;
        }

        $trip = $this->findOwnedTrip($user['id_utilisateur'], $_GET['id'] ?? null);

        if ($trip === null) {
            return;
        }

        $this->renderForm([
            'agence_depart' => (string) $trip['id_agence_depart'],
            'agence_arrivee' => (string) $trip['id_agence_arrivee'],
            'date_depart' => $this->toInputDate((string) $trip['date_depart']),
            'date_arrivee' => $this->toInputDate((string) $trip['date_arrivee']),
            'places_total' => (string) $trip['places_total'],
            'places_disponibles' => (string) $trip['places_disponibles'],
        ], [], (int) $trip['id_trajet']);
    }

    /** Valide et enregistre les modifications d'un trajet. */
    public function update(): void
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            $this->redirect('/connexion');
            return;
        }

        $trip = $this->findOwnedTrip($user['id_utilisateur'], $_POST['id'] ?? null);

        if ($trip === null) {
            return;
        }

        $tripId = (int) $trip['id_trajet'];
        $input = $this->collectInput();

        if (!$this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            $this->renderForm($input, ['form' => 'Session expirée, veuillez réessayer.'], $tripId);
            return;
        }

        $errors = $this->validationErrors($input);

        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($input, $errors, $tripId);
            return;
        }

        $this->trips->update(
            $tripId,
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

    /** Supprime un trajet dont l'utilisateur est l'auteur. */
    public function destroy(): void
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            $this->redirect('/connexion');
            return;
        }

        if (!$this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            echo 'Session expirée, veuillez réessayer.';
            return;
        }

        $trip = $this->findOwnedTrip($user['id_utilisateur'], $_POST['id'] ?? null);

        if ($trip === null) {
            return;
        }

        $this->trips->delete((int) $trip['id_trajet'], $user['id_utilisateur']);
        $this->redirect('/');
    }

    /**
     * Retourne le trajet demandé s'il appartient à l'utilisateur ; sinon envoie une réponse 404 ou 403 et retourne null.
     *
     * @return array<string, int|string>|null
     */
    private function findOwnedTrip(int $userId, mixed $rawId): ?array
    {
        $id = is_string($rawId) && preg_match('/\A\d{1,10}\z/', $rawId) === 1 ? (int) $rawId : 0;
        $trip = $id > 0 ? $this->trips->findById($id) : null;

        if ($trip === null) {
            http_response_code(404);
            echo 'Trajet introuvable.';
            return null;
        }

        if ((int) $trip['id_auteur'] !== $userId) {
            http_response_code(403);
            echo 'Vous ne pouvez modifier que vos propres trajets.';
            return null;
        }

        return $trip;
    }

    /**
     * @param array<string, string> $input
     * @return array<string, string>
     */
    private function validationErrors(array $input): array
    {
        $agencyIds = array_map(
            static fn (array $agency): int => (int) $agency['id_agence'],
            $this->agencies->findAll()
        );

        return $this->validator->validate($input, $agencyIds, new DateTimeImmutable());
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
    private function renderForm(array $old, array $errors, ?int $tripId = null): void
    {
        $isEdit = $tripId !== null;

        View::render('trips/create', [
            'pageTitle' => $isEdit ? 'Modifier un trajet' : 'Proposer un trajet',
            'heading' => $isEdit ? 'Modifier un trajet' : 'Proposer un trajet',
            'formAction' => $isEdit ? '/trajets/modifier' : '/trajets',
            'submitLabel' => $isEdit ? 'Enregistrer les modifications' : 'Publier le trajet',
            'tripId' => $tripId,
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

    private function toInputDate(string $sqlValue): string
    {
        return (new DateTimeImmutable($sqlValue))->format('Y-m-d\TH:i');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
    }
}