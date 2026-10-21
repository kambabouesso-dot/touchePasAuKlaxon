<?php

/**
 * @var string|null $error
 * @var string $email
 * @var string $csrfToken
 */
?>
<section class="row justify-content-center" aria-labelledby="page-heading">
    <div class="col-12 col-md-8 col-lg-5">
        <h1 class="h2 mb-4" id="page-heading">Connexion</h1>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/connexion" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <div class="mb-3">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input class="form-control" type="email" id="email" name="email" autocomplete="username" required
                       value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">Mot de passe</label>
                <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
            </div>

            <button class="btn btn-primary" type="submit">Se connecter</button>
        </form>
    </div>
</section>
