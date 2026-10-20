# Application WacDo

## Prérequis

* PHP +8.3
    * ext-ctype
    * ext-iconv
* Mysql/Mariadb 12.1.2-MariaDB

## Installation

### Ajouter et amender le fichier .env.local avec vos informations serveurs

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=REMPLACER_PAR_UN_SECRET_ALEATOIRE_LONG
DATABASE_URL="mysql://user_name:MOT_DE_PASSE_URL_ENCODE@127.0.0.1:3306/base_name?serverVersion=12.1.2-MariaDB&charset=utf8mb4"
```
**Important :** `serverVersion=12.1.2-MariaDB` est seulement un exemple MariaDB ; l'adapter à la version réelle de MySQL ou MariaDB et à la configuration Doctrine du projet. Encoder les caractères spéciaux du mot de passe dans l'URL (par exemple `@` → `%40`). Conserver toute autre variable requise par les fichiers `.env` et `config/` du projet.

**Générer un secret, par exemple :**

```bash
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

### Installer les dépendances

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

### Vérification de la configuration de l'environnement

```bash
php bin/console about
```
### Créer la base de données

#### SQL

```sql
CREATE DATABASE base_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'user_name'@'localhost' IDENTIFIED BY 'REMPLACER_PAR_UN_MOT_DE_PASSE_LONG';
GRANT ALL PRIVILEGES ON base_name.* TO 'user_name'@'localhost';
```

### Lancer les migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Créer un utilisateur au profil Administrateur

```bash
php bin/console app:create-user admin@example.fr 'mot_de_passe_fort' Nom Prénom ROLE_ADMIN
```

La commande définit automatiquement la date de recrutement, hache le mot de passe via `UserPasswordHasherInterface`, valide l'entité avec le groupe `user:create-command`, puis enregistre l'utilisateur avec Doctrine.

Rôles reconnus par le code : `ROLE_ADMIN`, `ROLE_COLLAB`, `ROLE_RESTAURANT_OWNER`.


## Assets (si nécessaire)

**Les assets sont compilés lors de la préparation du livrable. Aucune compilation n'est nécessaire sur le serveur de production si les fichiers générés sont inclus et correspondent à la version déployée.**

```bash
php bin/console tailwind:build --minify
php bin/console asset-map:compile
```

## Vider le cache symfony

```bash
php bin/console cache:clear
```

## Vérification

* Connexion
* Accès à l'application
* Création fonctions, collaborateurs, Owner, restaurants, affectations
* Listing
* Rendu responsive.
