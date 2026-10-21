<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\AgencyRepository;
use App\Models\TripRepository;
use App\Models\UserRepository;
use App\Services\AuthService;

/** Espace d'administration : utilisateurs, agences et trajets. */
final class AdminController
{
    private const AGENCY_NAME_MAX_LENGTH = 100;

    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly UserRepository $users,
        private readonly AgencyRepository $agencies,
        private readonly TripRepository $trips
    ) {
    }

    /** Liste des utilisateurs. */
    public function users(): void
    {
        if (!$this->guard()) {
            return;
        }

        View::render('admin/users', [
            'pageTitle' => 'Utilisateurs',
            'users' => $this->users->findAll(),
        ]);
    }

    /** Liste des agences avec formulaire d'ajout. */
    public function agencies(): void
    {
        if (!$this->guard()) {
            return;
        }

        $this->renderAgencies('', null);
    }

    /** Crée une agence. */
    public function createAgency(): void
    {
        if (!$this->guard() || !$this->checkCsrf()) {
            return;
        }

        $name = $this->postedName();
        $error = $this->nameError($name);

        if ($error === null && !$this->agencies->create($name)) {
            $error = 'Une agence porte déjà ce nom.';
        }

        if ($error !== null) {
            http_response_code(422);
            $this->renderAgencies($name, $error);
            return;
        }

        $this->flash('Agence créée.');
        $this->redirect('/admin/agences');
    }

    /** Affiche le formulaire de renommage d'une agence. */
    public function editAgency(): void
    {
        if (!$this->guard()) {
            return;
        }

        $agency = $this->findAgency($_GET['id'] ?? null);

        if ($agency === null) {
            return;
        }

        View::render('admin/agency_edit', [
            'pageTitle' => 'Modifier une agence',
            'agency' => $agency,
            'name' => $agency['nom'],
            'error' => null,
        ]);
    }

    /** Renomme une agence. */
    public function updateAgency(): void
    {
        if (!$this->guard() || !$this->checkCsrf()) {
            return;
        }

        $agency = $this->findAgency($_POST['id'] ?? null);

        if ($agency === null) {
            return;
        }

        $name = $this->postedName();
        $error = $this->nameError($name);

        if ($error === null && !$this->agencies->rename((int) $agency['id_agence'], $name)) {
            $error = 'Une agence porte déjà ce nom.';
        }

        if ($error !== null) {
            http_response_code(422);
            View::render('admin/agency_edit', [
                'pageTitle' => 'Modifier une agence',
                'agency' => $agency,
                'name' => $name,
                'error' => $error,
            ]);
            return;
        }

        $this->flash('Agence modifiée.');
        $this->redirect('/admin/agences');
    }

    /** Supprime une agence si aucun trajet ne l'utilise. */
    public function deleteAgency(): void
    {
        if (!$this->guard() || !$this->checkCsrf()) {
            return;
        }

        $agency = $this->findAgency($_POST['id'] ?? null);

        if ($agency === null) {
            return;
        }

        $this->flash(
            $this->agencies->delete((int) $agency['id_agence'])
                ? 'Agence supprimée.'
                : 'Suppression impossible : des trajets utilisent cette agence.'
        );
        $this->redirect('/admin/agences');
    }

    /** Liste de tous les trajets. */
    public function trips(): void
    {
        if (!$this->guard()) {
            return;
        }

        View::render('admin/trips', [
            'pageTitle' => 'Trajets',
            'trips' => $this->trips->findAllForAdmin(),
            'flash' => $this->takeFlash(),
        ]);
    }

    /** Supprime n'importe quel trajet. */
    public function deleteTrip(): void
    {
        if (!$this->guard() || !$this->checkCsrf()) {
            return;
        }

        $rawId = $_POST['id'] ?? null;
        $id = is_string($rawId) && preg_match('/\A\d{1,10}\z/', $rawId) === 1 ? (int) $rawId : 0;

        if ($id === 0 || !$this->trips->deleteAsAdmin($id)) {
            http_response_code(404);
            echo 'Trajet introuvable.';
            return;
        }

        $this->flash('Trajet supprimé.');
        $this->redirect('/admin/trajets');
    }

    /** Vérifie que l'utilisateur est un administrateur connecté ; sinon répond 302 ou 403. */
    private function guard(): bool
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            $this->redirect('/connexion');
            return false;
        }

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo 'Accès réservé aux administrateurs.';
            return false;
        }

        return true;
    }

    private function checkCsrf(): bool
    {
        if ($this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            return true;
        }

        http_response_code(419);
        echo 'Session expirée, veuillez réessayer.';

        return false;
    }

    /** @return array{id_agence: int|string, nom: string}|null */
    private function findAgency(mixed $rawId): ?array
    {
        $id = is_string($rawId) && preg_match('/\A\d{1,10}\z/', $rawId) === 1 ? (int) $rawId : 0;
        $agency = $id > 0 ? $this->agencies->findById($id) : null;

        if ($agency === null) {
            http_response_code(404);
            echo 'Agence introuvable.';
        }

        return $agency;
    }

    private function postedName(): string
    {
        $name = $_POST['nom'] ?? '';

        return is_string($name) ? trim($name) : '';
    }

    private function nameError(string $name): ?string
    {
        if ($name === '') {
            return 'Le nom de l\'agence est obligatoire.';
        }

        if (mb_strlen($name) > self::AGENCY_NAME_MAX_LENGTH) {
            return sprintf('Le nom ne doit pas dépasser %d caractères.', self::AGENCY_NAME_MAX_LENGTH);
        }

        return null;
    }

    private function renderAgencies(string $name, ?string $error): void
    {
        View::render('admin/agencies', [
            'pageTitle' => 'Agences',
            'agencies' => $this->agencies->findAll(),
            'name' => $name,
            'error' => $error,
            'flash' => $this->takeFlash(),
        ]);
    }

    private function flash(string $message): void
    {
        $this->session->set('flash', $message);
    }

    private function takeFlash(): ?string
    {
        $message = $this->session->get('flash');
        $this->session->remove('flash');

        return is_string($message) ? $message : null;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
    }
}
