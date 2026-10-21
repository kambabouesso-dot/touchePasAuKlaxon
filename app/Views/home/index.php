<?php

/**
 * @var list<array{
 *     date_depart: string,
 *     date_arrivee: string,
 *     places_disponibles: int|string,
 *     agence_depart: string,
 *     agence_arrivee: string
 * }> $trips
 */
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
                                    <?= htmlspecialchars($trip['agence_depart'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    <span class="text-body-secondary" aria-hidden="true">&rarr;</span>
                                    <?= htmlspecialchars($trip['agence_arrivee'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </h2>
                                <span class="badge text-bg-secondary">
                                    <?= (int) $trip['places_disponibles'] ?>
                                    place<?= (int) $trip['places_disponibles'] > 1 ? 's' : '' ?>
                                    disponible<?= (int) $trip['places_disponibles'] > 1 ? 's' : '' ?>
                                </span>
                            </div>

                            <dl class="row mb-0 mt-3">
                                <dt class="col-sm-5 text-body-secondary">Départ</dt>
                                <dd class="col-sm-7">
                                    <?= htmlspecialchars(
                                        (new DateTimeImmutable($trip['date_depart']))->format('d/m/Y à H:i'),
                                        ENT_QUOTES | ENT_SUBSTITUTE,
                                        'UTF-8'
                                    ) ?>
                                </dd>
                                <dt class="col-sm-5 text-body-secondary">Arrivée</dt>
                                <dd class="col-sm-7 mb-0">
                                    <?= htmlspecialchars(
                                        (new DateTimeImmutable($trip['date_arrivee']))->format('d/m/Y à H:i'),
                                        ENT_QUOTES | ENT_SUBSTITUTE,
                                        'UTF-8'
                                    ) ?>
                                </dd>
                            </dl>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
