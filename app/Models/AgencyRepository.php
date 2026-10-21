<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

/** Lecture de la liste des agences. */
class AgencyRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array{id_agence: int|string, nom: string}>
     */
    public function findAll(): array
    {
        $statement = $this->pdo->query('SELECT id_agence, nom FROM agence ORDER BY nom ASC');

        return $statement->fetchAll();
    }

    /**
     * @return array{id_agence: int|string, nom: string}|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id_agence, nom FROM agence WHERE id_agence = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** Crée une agence ; retourne false si le nom existe déjà. */
    public function create(string $name): bool
    {
        try {
            $this->pdo->prepare('INSERT INTO agence (nom) VALUES (:nom)')->execute(['nom' => $name]);
        } catch (PDOException $exception) {
            return $this->rethrowUnlessConstraint($exception);
        }

        return true;
    }

    /** Renomme une agence ; retourne false si le nom existe déjà. */
    public function rename(int $id, string $name): bool
    {
        try {
            $this->pdo->prepare('UPDATE agence SET nom = :nom WHERE id_agence = :id')
                ->execute(['nom' => $name, 'id' => $id]);
        } catch (PDOException $exception) {
            return $this->rethrowUnlessConstraint($exception);
        }

        return true;
    }

    /** Supprime une agence ; retourne false si elle est utilisée par des trajets. */
    public function delete(int $id): bool
    {
        try {
            $this->pdo->prepare('DELETE FROM agence WHERE id_agence = :id')->execute(['id' => $id]);
        } catch (PDOException $exception) {
            return $this->rethrowUnlessConstraint($exception);
        }

        return true;
    }

    /** Une violation de contrainte (SQLSTATE 23000) est un cas métier ; toute autre erreur est relancée. */
    private function rethrowUnlessConstraint(PDOException $exception): bool
    {
        if ((string) $exception->getCode() !== '23000') {
            throw $exception;
        }

        return false;
    }
}