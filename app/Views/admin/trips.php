<?php

/**
 * @var list<array{id_trajet: int|string, date_depart: string, date_arrivee: string, places_total: int|string, places_disponibles: int|string, agence_depart: string, agence_arrivee: string, auteur_nom: string, auteur_prenom: string}> $trips
 * @var string|null $flash
 * @var string $csrfToken
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$formatDate = static fn (string $value): string => (new DateTimeImmutable($value))->format('d/m/Y H:i');
?>
<section aria-labelledby="page-heading">
    <h1 class="h2 mb-4" id="page-heading">Trajets</h1>

    <?php if ($flash !== null): ?>
        <div class="alert alert-info" role="status"><?= $e($flash) ?></div>
    <?php endif; ?>

    <?php if ($trips === []): ?>
        <div class="alert alert-info" role="status">Aucun trajet enregistré.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Départ</th>
                        <th scope="col">Arrivée</th>
                        <th scope="col">Date de départ</th>
                        <th scope="col">Date d'arrivée</th>
                        <th scope="col">Places</th>
                        <th scope="col">Auteur</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td><?= $e($trip['agence_depart']) ?></td>
                            <td><?= $e($trip['agence_arrivee']) ?></td>
                            <td><?= $e($formatDate($trip['date_depart'])) ?></td>
                            <td><?= $e($formatDate($trip['date_arrivee'])) ?></td>
                            <td><?= (int) $trip['places_disponibles'] ?> / <?= (int) $trip['places_total'] ?></td>
                            <td><?= $e($trip['auteur_prenom'] . ' ' . $trip['auteur_nom']) ?></td>
                            <td class="text-end">
                                <form method="post" action="/admin/trajets/supprimer" class="d-inline"
                                      onsubmit="return confirm('Supprimer définitivement ce trajet ?');">
                                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $trip['id_trajet'] ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
