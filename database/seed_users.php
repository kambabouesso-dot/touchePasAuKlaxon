<?php

declare(strict_types=1);

function requiredEnvironmentValue(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        throw new RuntimeException(sprintf('La variable %s doit être définie.', $name));
    }

    return $value;
}

$usersFile = $argv[1] ?? '';

if ($usersFile === '' || !is_file($usersFile)) {
    fwrite(
        STDERR,
        "Usage : php seed_users.php <chemin-vers-users.txt>\n"
    );
    exit(1);
}

$lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($lines === false) {
    throw new RuntimeException('Impossible de lire le fichier des employés.');
}

$users = [];
$seenEmails = [];

foreach ($lines as $index => $line) {
    $fields = str_getcsv($line, ',', '"', '\\');

    if ($index === 0) {
        $fields[0] = preg_replace('/^\xEF\xBB\xBF/', '', $fields[0]) ?? $fields[0];
    }

    $fields = array_map(static fn (string $field): string => trim($field), $fields);

    if (count($fields) !== 4) {
        throw new RuntimeException(sprintf(
            'La ligne %d doit contenir exactement quatre champs.',
            $index + 1
        ));
    }

    [$nom, $prenom, $telephone, $email] = $fields;

    if ($nom === '' || $prenom === '' || $telephone === '') {
        throw new RuntimeException(sprintf('Un champ est vide à la ligne %d.', $index + 1));
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException(sprintf('Adresse e-mail invalide à la ligne %d.', $index + 1));
    }

    $emailKey = mb_strtolower($email, 'UTF-8');

    if (isset($seenEmails[$emailKey])) {
        throw new RuntimeException(sprintf('Adresse e-mail en double à la ligne %d.', $index + 1));
    }

    $seenEmails[$emailKey] = true;
    $users[] = [
        'nom' => $nom,
        'prenom' => $prenom,
        'telephone' => $telephone,
        'email' => $email,
    ];
}

if (count($users) !== 20) {
    throw new RuntimeException(sprintf(
        '20 employés étaient attendus ; %d ligne(s) valide(s) ont été trouvées.',
        count($users)
    ));
}

$sharedPassword = requiredEnvironmentValue('SEED_INITIAL_PASSWORD');

if (mb_strlen($sharedPassword, 'UTF-8') < 12) {
    throw new RuntimeException('Le mot de passe temporaire doit contenir au moins 12 caractères.');
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_NAME') ?: 'touche_pas_au_klaxon';
$databaseUser = requiredEnvironmentValue('DB_USER');
$databasePassword = requiredEnvironmentValue('DB_PASSWORD');

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $database
);

$pdo = new PDO($dsn, $databaseUser, $databasePassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$existingUsers = (int) $pdo->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();

if ($existingUsers !== 0) {
    throw new RuntimeException(
        'La table utilisateur n’est pas vide ; import annulé pour éviter les doublons.'
    );
}

$insertUser = $pdo->prepare(
    'INSERT INTO utilisateur
        (nom, prenom, email, telephone, mot_de_passe_hash, role)
     VALUES
        (:nom, :prenom, :email, :telephone, :mot_de_passe_hash, :role)'
);

$pdo->beginTransaction();

try {
    foreach ($users as $user) {
        $passwordHash = password_hash($sharedPassword, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('Le hachage d’un mot de passe a échoué.');
        }

        $insertUser->execute([
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'telephone' => $user['telephone'],
            'mot_de_passe_hash' => $passwordHash,
            'role' => 'utilisateur',
        ]);
    }

    $adminPasswordHash = password_hash($sharedPassword, PASSWORD_DEFAULT);

    if ($adminPasswordHash === false) {
        throw new RuntimeException('Le hachage du mot de passe administrateur a échoué.');
    }

    $insertUser->execute([
        'nom' => 'Administrateur',
        'prenom' => 'Local',
        'email' => 'admin@touche-pas-au-klaxon.test',
        'telephone' => '0000000000',
        'mot_de_passe_hash' => $adminPasswordHash,
        'role' => 'admin',
    ]);

    $pdo->commit();
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

printf("%d employés et 1 administrateur ont été insérés.\n", count($users));
fwrite(STDOUT, "Le mot de passe temporaire commun n’a pas été affiché ni enregistré en clair.\n");
