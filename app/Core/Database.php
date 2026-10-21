<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/** Crée les connexions PDO à la base MySQL configurée dans l'environnement. */
final class Database
{
    /** Ouvre une connexion configurée pour UTF-8 et les exceptions PDO. */
    public static function connect(): PDO
    {
        $username = getenv('DB_USER');
        if ($username === false || $username === '') {
            throw new RuntimeException('La variable DB_USER doit être définie.');
        }

        $password = getenv('DB_PASSWORD');
        if ($password === false) {
            throw new RuntimeException('La variable DB_PASSWORD doit être définie.');
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_NAME') ?: 'touche_pas_au_klaxon';

        if (preg_match('/\A[A-Za-z0-9_]+\z/', $database) !== 1) {
            throw new RuntimeException('La variable DB_NAME contient un nom invalide.');
        }

        if (filter_var($port, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]) === false) {
            throw new RuntimeException('La variable DB_PORT doit être comprise entre 1 et 65535.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
