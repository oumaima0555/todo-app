# Migrations - Notes

Cette note documente la migration récemment générée et appliquée pour l'entité `User` ainsi que l'ajustement de configuration nécessaire pour l'introspection MariaDB.

## Contexte
- Problème rencontré : lors de l'exécution de `php bin/console make:migration` et `doctrine:migrations:migrate`, Doctrine échouait avec une erreur d'introspection liée à la colonne `FULL_COLLATION_NAME` dans `information_schema` sur MariaDB.
- Version du serveur : `10.4.32-MariaDB` (vérifié en DB avec `SELECT VERSION()`)

## Corrections appliquées
1. Mise à jour temporaire de la configuration Doctrine afin de forcer l'identification MariaDB :
   - Fichier modifié : `config/packages/doctrine.yaml`
   - Valeur ajoutée : `server_version: 'mariadb-10.4.32'`
   - Ou via `DATABASE_URL` dans `.env` : `serverVersion=mariadb-10.4.32`
2. Génération de la migration : `php bin/console make:migration`
   - Fichier créé : `migrations/Version20251231032020.php`
3. Une sauvegarde complète de la base a été réalisée avant d'appliquer la migration.
   - Script utilisé : `scripts/backup_db.php`
   - Fichier créé : `var/backups/backup_YYYYMMDD_HHMMSS.sql` (ex : `backup_20251231_042209.sql`)
4. Application de la migration en mode transactionnel :
   - `php bin/console doctrine:migrations:migrate --all-or-nothing --no-interaction`

## Contenu principal de la migration
- Opérations : modification d'index, réajout de contrainte FK, ajustements des index uniques et des colonnes `messenger_messages` (types/auto_increment).
- Voir le fichier : `migrations/Version20251231032020.php` pour le SQL exact.

## Tests
- Suite de tests exécutée : `php bin/phpunit` → **OK** (2 tests, 2 assertions).

## Recommandations
- Committer ces changements (config, migration, script de backup) dans une branche dédiée avec un message clair.
- Garder la variable `server_version` en configuration pour ce projet si le serveur MariaDB est utilisé en production. Si le serveur est mis à jour vers une version plus récente, la valeur peut être ajustée.
- Pour restaurer la base en cas de besoin, utiliser le fichier de backup généré.

## Rollback
- La migration possède une méthode `down()` ; pour rollback :
  - `php bin/console doctrine:migrations:migrate <previous-version>`
- Si nécessaire, restaurer le dump SQL situé dans `var/backups/`.

---
_Fait automatiquement par l'outil d'assistance le 2025-12-31._
