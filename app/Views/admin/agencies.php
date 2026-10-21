<?php

/**
 * @var list<array{id_agence: int|string, nom: string}> $agencies
 * @var string $name
 * @var string|null $error
 * @var string|null $flash
 * @var string $csrfToken
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<section aria-labelledby="page-heading">
    <h1 class="h2 mb-4" id="page-heading">Agences</h1>

    <?php if ($flash !== null): ?>
        <div class="alert alert-info" role="status"><?= $e($flash) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/agences" class="row g-2 mb-4" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <div class="col-12 col-md-6">
            <label class="form-label" for="nom">Nouvelle agence</label>
            <input class="form-control<?= $error !== null ? ' is-invalid' : '' ?>" type="text" id="nom"
                   name="nom" maxlength="100" required value="<?= $e($name) ?>">
            <?php if ($error !== null): ?>
                <div class="invalid-feedback"><?= $e($error) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-12 col-md-auto d-flex align-items-end">
            <button class="btn btn-primary" type="submit">Ajouter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agencies as $agency): ?>
                    <tr>
                        <td><?= $e($agency['nom']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm"
                               href="/admin/agences/modifier?id=<?= (int) $agency['id_agence'] ?>">Modifier</a>
                            <form method="post" action="/admin/agences/supprimer" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette agence ?');">
                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= (int) $agency['id_agence'] ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
