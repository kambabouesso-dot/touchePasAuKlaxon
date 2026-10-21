# Touche pas au klaxon

Application intranet de covoiturage entre les agences de l'entreprise (PHP, architecture MVC, MySQL/MariaDB, Bootstrap et Sass).

## État d'avancement

- Schéma de base de données : `database/schema.sql`.
- Agences : `database/seed.sql`.
- Employés et compte administrateur : `database/seed_users.php`.
- Page publique listant les trajets futurs qui ont encore des places.

## Prérequis

- PHP 8.2 ou supérieur avec `pdo_mysql`.
- MySQL 8.0.16+ ou MariaDB 10.5+.
- Composer, Node.js et npm.

## Installation

1. Créer la base : `database/schema.sql`. Attention : ce script supprime les tables existantes avant de les recréer.
2. Charger les agences : `database/seed.sql`.
3. Importer les employés (fichier `nom,prenom,telephone,email`, 20 lignes) :

   ```sh
   php database/seed_users.php chemin/vers/users.txt
   ```

   Variables requises : `DB_USER`, `DB_PASSWORD`, `SEED_INITIAL_PASSWORD` (12 caractères minimum).
4. Installer les dépendances et compiler les styles :

   ```sh
   composer install
   npm install
   npm run build
   ```

5. Définir `DB_USER` et `DB_PASSWORD` (et, si besoin, `DB_HOST`, `DB_PORT`, `DB_NAME`), puis lancer :

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

## Qualité

```sh
composer test
composer analyse
```
