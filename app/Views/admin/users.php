<?php

/**
 * @var list<array{id_utilisateur: int|string, nom: string, prenom: string, email: string, telephone: string, role: string}> $users
 */
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<section aria-labelledby="page-heading">
    <h1 class="h2 mb-4" id="page-heading">Utilisateurs</h1>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Rôle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $e($user['nom']) ?></td>
                        <td><?= $e($user['prenom']) ?></td>
                        <td><?= $e($user['email']) ?></td>
                        <td><?= $e($user['telephone']) ?></td>
                        <td><?= $e($user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
