<?php

declare(strict_types=1);

/** @var string $content */
$pageTitle = $pageTitle ?? 'Touche pas au klaxon';
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
                <a class="navbar-brand fw-semibold" href="/">Touche pas au klaxon</a>
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
    </body>
</html>
