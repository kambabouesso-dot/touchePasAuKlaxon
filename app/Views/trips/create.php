<?php

/**
 * @var array{prenom: string, nom: string, email: string, telephone: string} $currentUser
 * @var string $csrfToken
 * @var list<array{id_agence: int|string, nom: string}> $agencies
 * @var array<string, string> $old
 * @var array<string, string> $errors
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$invalid = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<section class="row justify-content-center" aria-labelledby="page-heading">
    <div class="col-12 col-lg-8">
        <h1 class="h2 mb-4" id="page-heading">Proposer un trajet</h1>

        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-danger" role="alert"><?= $e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="/trajets" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

            <fieldset class="mb-4">
                <legend class="h5">Contact</legend>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input class="form-control" id="prenom" type="text" value="<?= $e($currentUser['prenom']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nom">Nom</label>
                        <input class="form-control" id="nom" type="text" value="<?= $e($currentUser['nom']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input class="form-control" id="email" type="email" value="<?= $e($currentUser['email']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control" id="telephone" type="text" value="<?= $e($currentUser['telephone']) ?>" readonly>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-4">
                <legend class="h5">Trajet</legend>
                <div class="row g-3">
                    <?php foreach (['agence_depart' => 'Agence de départ', 'agence_arrivee' => "Agence d'arrivée"] as $field => $label): ?>
                        <div class="col-md-6">
                            <label class="form-label" for="<?= $field ?>"><?= $e($label) ?></label>
                            <select class="form-select<?= $invalid($field) ?>" id="<?= $field ?>" name="<?= $field ?>" required>
                                <option value="">Choisir…</option>
                                <?php foreach ($agencies as $agency): ?>
                                    <option value="<?= (int) $agency['id_agence'] ?>"
                                        <?= (string) $agency['id_agence'] === ($old[$field] ?? '') ? 'selected' : '' ?>>
                                        <?= $e($agency['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors[$field])): ?>
                                <div class="invalid-feedback"><?= $e($errors[$field]) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach (['date_depart' => 'Départ', 'date_arrivee' => 'Arrivée'] as $field => $label): ?>
                        <div class="col-md-6">
                            <label class="form-label" for="<?= $field ?>"><?= $e($label) ?></label>
                            <input class="form-control<?= $invalid($field) ?>" type="datetime-local" id="<?= $field ?>"
                                   name="<?= $field ?>" value="<?= $e($old[$field] ?? '') ?>" required>
                            <?php if (isset($errors[$field])): ?>
                                <div class="invalid-feedback"><?= $e($errors[$field]) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach (['places_total' => 'Places au total', 'places_disponibles' => 'Places disponibles'] as $field => $label): ?>
                        <div class="col-md-6">
                            <label class="form-label" for="<?= $field ?>"><?= $e($label) ?></label>
                            <input class="form-control<?= $invalid($field) ?>" type="number" min="0" id="<?= $field ?>"
                                   name="<?= $field ?>" value="<?= $e($old[$field] ?? '') ?>" required>
                            <?php if (isset($errors[$field])): ?>
                                <div class="invalid-feedback"><?= $e($errors[$field]) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <button class="btn btn-primary" type="submit">Publier le trajet</button>
            <a class="btn btn-link" href="/">Annuler</a>
        </form>
    </div>
</section>