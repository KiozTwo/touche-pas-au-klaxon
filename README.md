# Touche pas au klaxon

Application de covoiturage sur l'intranet d'une entreprise, réalisée en PHP 8.2, MVC, PDO/MySQL (ou MariaDB), Bootstrap 5 et Sass. Les employés consultent et proposent des trajets entre agences ; l'administrateur gère aussi les agences et tous les trajets.

## Prérequis

- PHP 8.2+ avec extensions `pdo_mysql` et `mbstring`, Composer 2 ; MySQL 8.0+ ou MariaDB 10.6+.
- Accès réseau au CDN Bootstrap (`cdn.jsdelivr.net`) dans cette version ; pour un intranet isolé, compiler Sass et servir Bootstrap localement.
- Serveur web pointant exclusivement vers `public/` ; ne jamais exposer `.env`, `sql/`, `src/` ou `vendor/`.

## Installation en local

```bash
cp .env.example .env
# Modifier .env : DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
composer install
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/seed.sql
php -S 127.0.0.1:8000 -t public public/router.php
```

Créer un utilisateur MySQL avec des droits `SELECT, INSERT, UPDATE, DELETE` sur `klaxon.*` puis mettre ses identifiants dans `.env`. Les scripts SQL sont appliqués avec un compte capable de créer la base. Visiter `http://127.0.0.1:8000/`. L'horloge PHP utilise `APP_TIMEZONE=Europe/Paris` et la connexion SQL reprend le décalage horaire courant. Le jeu d'essai crée deux trajets futurs et charge les 12 villes et 20 employés fournis. Rejouer `seed.sql` insère deux trajets supplémentaires ; importer dans une base neuve pour retrouver exactement deux trajets.

Les comptes administrateur et employé de démonstration sont décrits dans le livrable PDF remis séparément. Leurs mots de passe initiaux forts ne figurent pas dans le dépôt. Les autres comptes importés ont chacun un mot de passe initial aléatoire inconnu ; une réinitialisation par l’équipe informatique serait nécessaire en usage réel.

## Utilisation et règles

- Visiteur : liste chronologique des trajets à venir avec au moins une place libre ; pas de coordonnées personnelles.
- Employé connecté : détails du contact dans une fenêtre modale ; création de trajet et modification/suppression de ses seuls trajets.
- Administrateur : liste des utilisateurs en lecture seule (données RH), liste complète des trajets et gestion des agences. Une agence utilisée par un trajet ne peut pas être supprimée (clé étrangère).
- Les noms et coordonnées du contact proviennent toujours de l'utilisateur authentifié, jamais du formulaire. Les agences doivent être distinctes ; départ dans le futur ; arrivée après le départ ; entre 1 et 50 places au total, et de 0 à ce total disponibles.
- Toutes les écritures s'effectuent par POST avec jeton CSRF ; les pages appliquent les droits côté serveur ; requêtes préparées PDO et échappement HTML des sorties.

## Structure MVC

`public/index.php` : point d'entrée et routage ; `src/Controllers/` : actions HTTP et autorisations ; `src/Repositories/` : accès PDO ; `src/Support/` : connexion et règles métier ; `views/` : présentations ; `sql/` : schéma, données initiales et fichiers sources ; `docs/` : MCD, MLD et maquettes annexées.

## Tests et analyse

```bash
composer test
composer analyse
```

`TripValidatorTest` teste les rejets et l'acceptation des règles métier. `RepositoryWriteTest` exécute les créations, mises à jour et suppressions d'agences et de trajets dans une transaction annulée en fin de test. Créer d'abord une **base dédiée**, par exemple `klaxon_test`, avec le schéma (modifier la première ligne `USE klaxon;` en `USE klaxon_test;` et créer cette base), puis définir `TEST_DB_NAME=klaxon_test` pour lancer le test d'intégration. Le test est ignoré sans cette variable. Installer les dépendances de développement avec `composer install` (sans `--no-dev`). PHPUnit et PHPStan nécessitent PHP et Composer ; les commandes n'ont pas été exécutées dans l'environnement de création qui ne les fournit pas.

## Sass

`public/assets/style.scss` définit les variables Bootstrap avant son import. Pour compiler après `npm install` :

```bash
npm run build:css
```

La feuille `public/assets/style.css` incluse dans le dépôt fournit déjà les surcharges de la palette. La compilation Sass est recommandée avant production pour obtenir le CSS Bootstrap local et éliminer la dépendance au CDN (retirer alors la balise CDN dans `views/layout.php`).

## Modèle des données

Le MCD est dans `docs/MCD.pdf` et le MLD textuel dans `docs/MLD.txt`. Une agence participe à 0..N trajets comme départ et à 0..N trajets comme arrivée ; chaque trajet possède exactement un départ, une arrivée et un auteur. Les employés ne sont jamais modifiables via l'application. La réservation de place n'est pas dans le périmètre de cette première version.

## Mise en ligne du dépôt

Créer un dépôt GitHub, y publier le dossier de ce projet (sans `.env` ni `vendor/`) et remplacer le champ « lien du dépôt » dans le livrable PDF. Aucun dépôt distant n'a été créé automatiquement.
