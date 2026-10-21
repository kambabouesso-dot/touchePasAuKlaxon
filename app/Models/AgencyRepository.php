<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

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
}