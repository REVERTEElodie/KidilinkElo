# Installation de Symfony

Crée un nouveau projet Symfony nommé "KidiLink" en utilisant Composer:

```bash

composer create-project symfony/skeleton:"6.3*" KidiLink

```

puis faire cd KidiLink pour être dans le projet

Injections des dépendances :

```bash
composer require webapp
```

Création database : dans .env.local mettre :

DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

pour créer la database afin de la voir dans Adminer :

```bash
php bin/console doctrine:database:create
```

Création entité User :

```bash
bin/console make:user
```

Ensuite pour mettre les données, les tables et les attributs dans adminer

```bash
bin/console make:migration
```

puis

```bash
bin/console doctrine:migrations:migrate
```
