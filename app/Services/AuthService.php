<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\UserRepository;

/** Gère la connexion, la déconnexion et l'utilisateur courant. */
final class AuthService
{
    private const SESSION_KEY = 'user_id';

    private static ?string $dummyHash = null;

    public function __construct(
        private readonly UserRepository $users,
        private readonly Session $session
    ) {
    }

    /** Tente de connecter l'utilisateur ; retourne false si les identifiants sont invalides. */
    public function attempt(string $email, string $password): bool
    {
        $credentials = $this->users->findCredentialsByEmail($email);

        // Un hash factice garde un temps de réponse comparable si l'e-mail est inconnu.
        $hash = $credentials['mot_de_passe_hash'] ?? self::dummyHash();
        $isValid = password_verify($password, $hash);

        if ($credentials === null || !$isValid) {
            return false;
        }

        $this->session->regenerate();
        $this->session->set(self::SESSION_KEY, (int) $credentials['id_utilisateur']);

        return true;
    }

    /**
     * Retourne l'utilisateur connecté, ou null.
     *
     * @return array{id_utilisateur: int, nom: string, prenom: string, email: string, telephone: string, role: string}|null
     */
    public function currentUser(): ?array
    {
        $id = $this->session->get(self::SESSION_KEY);

        if (!is_int($id)) {
            return null;
        }

        $user = $this->users->findById($id);

        if ($user === null) {
            $this->session->remove(self::SESSION_KEY);
        }

        return $user;
    }

    /** Déconnecte l'utilisateur en détruisant la session. */
    public function logout(): void
    {
        $this->session->destroy();
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= password_hash('mot-de-passe-factice', PASSWORD_DEFAULT);
    }
}
