
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

NB: vous avez des messages comme quoi la database n'est pas créer c'est normal poursuivez les étapes ci dessous.

Création du fichier .env.local dans le dossier KidiLink

Création database dans .env.local mettre :

DATABASE_URL="mysql://root:root@127.0.0.1:3306/KidiLink?charset=utf8mb4"

pour créer la database afin de la voir dans Adminer :

```bash

php bin/console doctrine:database:create

```

Ensuite pour mettre les données, les tables et les attributs dans adminer

```bash

bin/console make:migration  (répondre yes)

```

puis

```bash

bin/console doctrine:migrations:migrate (répondre yes)


```

```bash

bin/console doctrine:fixtures:load (ou juste bin/console d:f:l) (répondreyes)

```

Pour vérifier que vous avez les informations dans la Database se connecter avec les identifiants root et mdp root table KidiLink

Pour lancer le serveur : taper dans la ligne de commande :

```bash

php -S 127.0.0.1:8000 -t public

```

Pouvoir récupérer et générer les clés public / privé.

```bash

php bin/console lexik:jwt:generate-keypair

```

<!-- config/packages/security.yaml
access_control:
    - { path: ^/login_check, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/api/me/classes, roles: IS_AUTHENTICATED_FULLY }

 #[Route('/api/me/classes', methods:['GET'])]
public function getParentClasses() {
    // Je peux récupérer l'utilisateur courant via
    $user = $this->getUser();

    $classes = $user->getClasses();

   // return json response etc.
} -->
