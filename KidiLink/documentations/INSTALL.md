# Installation de Symfony

Dépendances Symfony en utilisant Composer:

```bash


composer install


```

puis faire cd KidiLink pour être dans le projet

Injections des dépendances :

```bash

composer require webapp

```

Création database : dans .env.local mettre :

DATABASE_URL="mysql://root:root@127.0.0.1:3306/KidiLink?charset=utf8mb4"

pour créer la database afin de la voir dans Adminer :

```bash

php bin/console doctrine:database:create

```

Ensuite pour mettre les données, les tables et les attributs dans adminer

```bash

bin/console make:migration

```

puis

```bash

bin/console doctrine:migrations:migrate

```

```bash

bin/console doctrine:fixtures:load (ou juste bin/console d:f:l)
```