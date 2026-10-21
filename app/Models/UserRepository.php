<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Lecture des utilisateurs (lecture seule : les employés ne sont pas gérés par l'application). */
class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Retourne les éléments nécessaires à la vérification du mot de passe.
     *
     * @return array{id_utilisateur: int, mot_de_passe_hash: string}|null
     */
    public function findCredentialsByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id_utilisateur, mot_de_passe_hash
             FROM utilisateur
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Liste des utilisateurs, sans mot de passe.
     *
     * @return list<array{id_utilisateur: int|string, nom: string, prenom: string, email: string, telephone: string, role: string}>
     */
    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT id_utilisateur, nom, prenom, email, telephone, role
             FROM utilisateur
             ORDER BY nom ASC, prenom ASC'
        );

        return $statement->fetchAll();
    }

    /**
     * Retourne un utilisateur sans son hash de mot de passe.
     *
     * @return array{id_utilisateur: int, nom: string, prenom: string, email: string, telephone: string, role: string}|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id_utilisateur, nom, prenom, email, telephone, role
             FROM utilisateur
             WHERE id_utilisateur = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }
}
