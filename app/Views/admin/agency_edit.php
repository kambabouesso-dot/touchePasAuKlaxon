<?php

/**
 * @var array{id_agence: int|string, nom: string} $agency
 * @var string $name
 * @var string|null $error
 * @var string $csrfToken
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<section aria-labelledby="page-heading">
    <h1 class="h2 mb-4" id="page-heading">Modifier une agence</h1>

    <form method="post" action="/admin/agences/modifier" class="col-12 col-md-6" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $agency['id_agence'] ?>">
        <div class="mb-3">
            <label class="form-label" for="nom">Nom de l'agence</label>
            <input class="form-control<?= $error !== null ? ' is-invalid' : '' ?>" type="text" id="nom"
                   name="nom" maxlength="100" required value="<?= $e($name) ?>">
            <?php if ($error !== null): ?>
                <div class="invalid-feedback"><?= $e($error) ?></div>
            <?php endif; ?>
        </div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a class="btn btn-outline-secondary" href="/admin/agences">Annuler</a>
    </form>
</section>
