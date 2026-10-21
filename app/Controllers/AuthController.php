<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;

/** Affichage du formulaire de connexion et traitement de la connexion et la déconnexion. */
final class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session
    ) {
    }

    /** Affichage du formulaire de connexion. */
    public function showLogin(): void
    {
        if ($this->auth->currentUser() !== null) {
            $this->redirect('/');
            return;
        }

        View::render('auth/login', ['pageTitle' => 'Connexion', 'error' => null, 'email' => '']);
    }

    /** Traitement de contrôle et de soumission du formulaire de connexion. */
    public function login(): void
    {
        $email = trim($this->postValue('email'));
        $password = $this->postValue('password');

        if (!$this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('auth/login', [
                'pageTitle' => 'Connexion',
                'error' => 'Session expirée, veuillez réessayer.',
                'email' => $email,
            ]);
            return;
        }

        if ($email === '' || $password === '' || !$this->auth->attempt($email, $password)) {
            http_response_code(422);
            View::render('auth/login', [
                'pageTitle' => 'Connexion',
                'error' => 'Adresse e-mail ou mot de passe incorrect.',
                'email' => $email,
            ]);
            return;
        }

        $user = $this->auth->currentUser();
        $this->redirect($user !== null && $user['role'] === 'admin' ? '/admin' : '/');
    }

    /** Déconnexion de l'utilisateur (requête POST protégée par jeton CSRF). */
    public function logout(): void
    {
        if ($this->session->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->auth->logout();
        }

        $this->redirect('/');
    }

    private function postValue(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
    }
}
