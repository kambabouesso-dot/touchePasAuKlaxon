<?php

/**
 * @var list<array{
 *     id_trajet?: int|string,
 *     date_depart: string,
 *     date_arrivee: string,
 *     places_disponibles: int|string,
 *     agence_depart: string,
 *     agence_arrivee: string,
 *     places_total?: int|string,
 *     auteur_nom?: string,
 *     auteur_prenom?: string,
 *     auteur_email?: string,
 *     auteur_telephone?: string
 * }> $trips
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$formatDate = static fn (string $value): string => (new DateTimeImmutable($value))->format('d/m/Y à H:i');
?>
<section aria-labelledby="page-heading">
    <div class="mb-4">
        <h1 class="h2 mb-2" id="page-heading">Trajets disponibles</h1>
        <p class="text-body-secondary mb-0">Retrouvez les prochains trajets entre les agences.</p>
    </div>

    <?php if ($trips === []): ?>
        <div class="alert alert-info" role="status">
            Aucun trajet avec des places disponibles n'est planifié pour le moment.
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($trips as $trip): ?>
                <div class="col-12 col-lg-6">
                    <article class="card trip-card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <h2 class="h5 card-title mb-0">
                                    <?= $e($trip['agence_depart']) ?>
                                    <span class="text-body-secondary" aria-hidden="true">&rarr;</span>
                                    <?= $e($trip['agence_arrivee']) ?>
                                </h2>
                                <span class="badge text-bg-secondary">
                                    <?= (int) $trip['places_disponibles'] ?>
                                    place<?= (int) $trip['places_disponibles'] > 1 ? 's' : '' ?>
                                    disponible<?= (int) $trip['places_disponibles'] > 1 ? 's' : '' ?>
                                </span>
                            </div>

                            <dl class="row mb-0 mt-3">
                                <dt class="col-sm-5 text-body-secondary">Départ</dt>
                                <dd class="col-sm-7"><?= $e($formatDate($trip['date_depart'])) ?></dd>
                                <dt class="col-sm-5 text-body-secondary">Arrivée</dt>
                                <dd class="col-sm-7 mb-0"><?= $e($formatDate($trip['date_arrivee'])) ?></dd>
                            </dl>

                            <?php if (isset(
                                $trip['id_trajet'],
                                $trip['places_total'],
                                $trip['auteur_nom'],
                                $trip['auteur_prenom'],
                                $trip['auteur_email'],
                                $trip['auteur_telephone']
                            )): ?>
                                <?php $modalId = 'trip-modal-' . (int) $trip['id_trajet']; ?>
                                <button class="btn btn-outline-primary btn-sm mt-3" type="button"
                                        data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                    Détails
                                </button>

                                <div class="modal fade" id="<?= $modalId ?>" tabindex="-1"
                                     aria-labelledby="<?= $modalId ?>-title" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h2 class="modal-title h5" id="<?= $modalId ?>-title">Détails du trajet</h2>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5">Proposé par</dt>
                                                    <dd class="col-sm-7"><?= $e($trip['auteur_prenom'] . ' ' . $trip['auteur_nom']) ?></dd>
                                                    <dt class="col-sm-5">Téléphone</dt>
                                                    <dd class="col-sm-7"><?= $e($trip['auteur_telephone']) ?></dd>
                                                    <dt class="col-sm-5">E-mail</dt>
                                                    <dd class="col-sm-7"><?= $e($trip['auteur_email']) ?></dd>
                                                    <dt class="col-sm-5">Places au total</dt>
                                                    <dd class="col-sm-7 mb-0"><?= (int) $trip['places_total'] ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
