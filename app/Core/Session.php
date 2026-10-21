<?php

declare(strict_types=1);

namespace App\Core;

/** Encapsule la session PHP et le jeton CSRF. */
class Session
{
    /** Démarre la session avec des cookies protégés (HttpOnly, SameSite). */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }

    /** Lecture d'une valeur de session. */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /** Écriture d'une valeur de session. */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Suppression d'une valeur de session. */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Changement de l'identifiant de session pour empêcher sa fixation. */
    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /** Vide et détruit la session courante. */
    public function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /** Retourne le jeton CSRF de la session, en le créant au besoin. */
    public function csrfToken(): string
    {
        $token = $this->get('csrf_token');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }

        return $token;
    }

    /** Vérification d'un jeton CSRF reçu d'un formulaire. */
    public function isValidCsrfToken(mixed $token): bool
    {
        return is_string($token) && hash_equals($this->csrfToken(), $token);
    }
}
