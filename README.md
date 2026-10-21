# Touche pas au klaxon

Application intranet de covoiturage entre les agences de l'entreprise (PHP 8, architecture MVC, MySQL/MariaDB, Bootstrap et Sass).

## Fonctionnalités

- **Visiteur** : consulte les trajets futurs ayant encore des places et se connecte.
- **Utilisateur connecté** : voit les détails d'un trajet (auteur, contact, places), crée un trajet, modifie ou supprime ses propres trajets.
- **Administrateur** : consulte les utilisateurs, gère les agences (création, modification, suppression) et supprime n'importe quel trajet.

## Modélisation

- MCD : [docs/MCD.png](docs/MCD.png)
- MLD : [docs/MLD.txt](docs/MLD.txt)

## Prérequis

- PHP 8.2 ou supérieur avec `pdo_mysql`.
- MySQL 8.0.16+ ou MariaDB 10.5+.
- Composer, Node.js et npm.

## Installation

1. Créer la base : exécuter `database/schema.sql`. Attention : ce script supprime les tables existantes avant de les recréer.
2. Charger les agences : exécuter `database/seed.sql`.
3. Importer les employés (fichier `nom,prenom,telephone,email`, 20 lignes) et créer l'administrateur :

   ```sh
   php database/seed_users.php chemin/vers/users.txt
   ```

   Variables requises : `DB_USER`, `DB_PASSWORD`, `SEED_INITIAL_PASSWORD` (12 caractères minimum). Les mots de passe sont stockés hachés (bcrypt).
4. Installer les dépendances et compiler les styles :

   ```sh
   composer install
   npm install
   npm run build
   ```

5. Définir `DB_USER` et `DB_PASSWORD` (et, si besoin, `DB_HOST`, `DB_PORT`, `DB_NAME`), puis lancer le serveur :

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

   L'application est disponible sur http://127.0.0.1:8000.

## Comptes de démonstration

Tous les comptes partagent le mot de passe défini dans `SEED_INITIAL_PASSWORD` lors de l'étape 3. À communiquer séparément et à changer en production.

| Rôle | E-mail |
|------|--------|
| Administrateur | `admin@touche-pas-au-klaxon.test` |
| Utilisateur | `alexandre.martin@email.fr` |

## Qualité

```sh
composer test      # PHPUnit
composer analyse   # PHPStan
```

Les tests unitaires couvrent notamment les écritures en base (création, modification, suppression de trajets et d'agences, avec un PDO simulé), la validation des trajets, l'authentification et le routeur.

## Sécurité

Requêtes préparées PDO, hachage des mots de passe, protection CSRF sur tous les formulaires POST, échappement systématique des sorties, cookies de session `HttpOnly` et `SameSite`, contrôle des droits (auteur ou administrateur) côté serveur.
