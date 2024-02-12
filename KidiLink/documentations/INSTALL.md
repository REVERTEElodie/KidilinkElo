
# Installation de Symfony

Dépendances Symfony en utilisant Composer:

```bash

<<<<<<< HEAD

composer install

=======
composer install
>>>>>>> 2089b11f04f9b8c2971237acc5c038fa5539b99e

```

puis faire cd KidiLink pour être dans le projet

Injections des dépendances :

```bash
<<<<<<< HEAD
 
=======

>>>>>>> 2089b11f04f9b8c2971237acc5c038fa5539b99e
composer require webapp

```
NB: vous avez des messages comme quoi la database n'est pas créer c'est normal poursuivez les étapes ci dessous.

Création du fichier .env.local dans le dossier KidiLink

<<<<<<< HEAD
=======
Création database dans .env.local mettre :
>>>>>>> 2089b11f04f9b8c2971237acc5c038fa5539b99e
DATABASE_URL="mysql://root:root@127.0.0.1:3306/KidiLink?charset=utf8mb4"

pour créer la database afin de la voir dans Adminer :

```bash

<<<<<<< HEAD
phpbin/console doctrine:database:create
=======
php bin/console doctrine:database:create
>>>>>>> 2089b11f04f9b8c2971237acc5c038fa5539b99e

```

Ensuite pour mettre les données, les tables et les attributs dans adminer

```bash

bin/console make:migration

```

puis

```bash

bin/console doctrine:migrations:migrate

<<<<<<< HEAD
```

```bash

bin/console doctrine:fixtures:load (ou juste bin/console d:f:l)
```

Pour vérifier que vous avez les informations dans la Database se connecter avec les identifiants root et mdp root table KidiLink

Pour lancer le serveur : taper dans la ligne de commande :

```bash

php-S127.0.0.1:8000-tpublic
```
=======
```

```bash

bin/console doctrine:fixtures:load (ou juste bin/console d:f:l)
```

Pour vérifier que vous avez les informations dans la Database se connecter avec les identifiants root et mdp root table KidiLink

Pour lancer le serveur : taper dans la ligne de commande : 

```bash
php -S 127.0.0.1:8000 -t public
```
>>>>>>> 2089b11f04f9b8c2971237acc5c038fa5539b99e
