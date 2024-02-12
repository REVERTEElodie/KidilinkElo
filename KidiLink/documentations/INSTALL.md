
# Installation de Symfony

Dépendances Symfony en utilisant Composer:

```bash


composerinstall


```

puis faire cd KidiLink pour être dans le projet

Injections des dépendances :

```bash


composerrequirewebapp


```

NB: vous avez des messages comme quoi la database n'est pas créer c'est normal poursuivez les étapes ci dessous.

Création du fichier .env.local dans le dossier KidiLink

Création database dans .env.local mettre :

DATABASE_URL="mysql://root:root@127.0.0.1:3306/KidiLink?charset=utf8mb4"

pour créer la database afin de la voir dans Adminer :

```bash


phpbin/consoledoctrine:database:create


```

Ensuite pour mettre les données, les tables et les attributs dans adminer

```bash


bin/consolemake:migration


```

puis

```bash


bin/consoledoctrine:migrations:migrate


```

```bash


bin/consoledoctrine:fixtures:load (ou justebin/consoled:f:l)

```

Pour vérifier que vous avez les informations dans la Database se connecter avec les identifiants root et mdp root table KidiLink

Pour lancer le serveur : taper dans la ligne de commande :

```bash

php-S127.0.0.1:8000-tpublic

```
