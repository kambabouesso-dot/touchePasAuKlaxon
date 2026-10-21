<?php

declare(strict_types=1);

/**
 * @var string $content
 * @var array{prenom: string, nom: string, role: string}|null $currentUser
 * @var string $csrfToken
 */
$pageTitle = $pageTitle ?? 'Touche pas au klaxon';
$currentUser = $currentUser ?? null;
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> - Touche pas au klaxon</title>
        <link rel="stylesheet" href="/assets/app.css">
    </head>
    <body class="d-flex min-vh-100 flex-column">
        <header class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="<?= $currentUser !== null && $currentUser['role'] === 'admin' ? '/admin' : '/' ?>">Touche pas au klaxon</a>

                <div class="d-flex align-items-center gap-3">
                    <?php if ($currentUser === null): ?>
                        <a class="btn btn-light" href="/connexion">Connexion</a>
                    <?php else: ?>
                        <?php if ($currentUser['role'] !== 'admin'): ?>
                            <a class="btn btn-light" href="/trajets/nouveau">Créer un trajet</a>
                            <span class="navbar-text text-white">
                                <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </span>
                        <?php else: ?>
                            <a class="btn btn-light" href="/admin/utilisateurs">Utilisateurs</a>
                            <a class="btn btn-light" href="/admin/agences">Agences</a>
                            <a class="btn btn-light" href="/admin/trajets">Trajets</a>
                        <?php endif; ?>
                        <form method="post" action="/deconnexion" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <button class="btn btn-outline-light" type="submit">Déconnexion</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </header>


        <main class="container flex-grow-1 py-5">
            <?= $content ?>
        </main>

        <footer class="border-top py-3">
            <div class="container text-center text-body-secondary small">
                Touche pas au klaxon &copy; <?= date('Y') ?>
            </div>
        </footer>
        <script src="/assets/bootstrap.bundle.min.js"></script>
    </body>
</html>
