# Inventaire

C'est un site délivré chez le client. Donc une instance par établissement. Important à noter car un directeur aura accès à TOUS ce qu'il y a dans la bdd. 


En tant qu'admin je dois pouvoir
- voir des classes x
- ajouter des classes x
- créer des comptes encadrants x
- assigner des encadrants à ces classes x => oui quuand on créé une classe on est obligé de passer un encadrant, donc c'est ok.

En tant qu'encadrant je dois pouvoir : 
- voir mes classes x
- voir mes albums   A FAIRE x
- voir mes photos d'albums x
- ajouter des albums à mes classes A FAIRE x
- ajouter des photos a mes albums x
- ajouter des utilisateurs parents (création de compte pour eux) x
- les assigner à une classe x

En tant que parent je dois pouvoir : 
- Voir mes classes x
- Voir mes albums  A FAIRE x
- Voir mes photos x
- Voir une photo pour : x
  - voir les commentaires d'une photos
  - voir la photo en grand x
  - mettre un commentaire sur une photo

Routes : 

## Routes admin
`GET /api/classes` => voir mes classes en tant que directeur   OK
`POST /api/classe/new` => ajouter une classe => ROLE_ADMIN     OK
`POST /api/admin/manager/new` => ajouter un encadrant          OK
`POST /api/classes/encadrants/add` => ajouter un encadrant à une classe ( ça on l'a pas encore)
`GET /api/admin/managers` => recupère tous les managers        OK
`GET /api/admin/admins` => recupère tous les admins            OK
`GET /api/admin/parents` => recupère tous les parents          OK

## Routes manager
`GET /api/classes` => voir mes classes en tant qu'encadrant    OK
`GET /api/classe/{id}` => récup les infos d'une classe         OK
`GET /api/classe/{id}/albums` => voir mes albums d'une classes OK
`GET /api/album/{id}` => est lié à une classe, donc on peut vérif que l'encadrant a bien accès à cet album => retourne les infos de l'album + les photos                                        OK
`POST /api/album/new` => ajouter un album pour une classe      OK
`POST /api/manager/album/{id}/photos/new` => ajouter une photo pour une classe     OK
`POST /api/manager/classe/{id}/parent` => ajouter les parents à une classe (création de compte) OK
<!-- `PUT /api/classe/{id}/parents/assign` => assigner un parent EXISTANT à une classe    -->
<!-- `GET /api/parents/search?term={searchTerm}` => chercher un parent par son email, son prenom, son nom => dnas le but de pouvoir l'assigner à une classe (ça on l'a pas encore) -->
`POST /api/photo/{id}/comments` => afficher les commentaires d'une photo OK
`POST /api/comment/new` => créer un commentaire  OK

## Parent
`GET /api/classes` => voir mes classes  OK
`GET /api/classe/{id}` => voir les infos d'une classes  OK
`GET /api/classe/{id}/albums` => voir mes albums d'une classes  OK
`GET /api/album/{id}` => est lié à une classe, donc on peut vérif que le parent a bien accès à cet album => retourne les infos de l'album OK
`GET /api/album/{id}/photos` => retourne les photos d'un album   OK
`GET /api/photo/{id}` => avoir le détail d'une photo              OK
`GET /api/photo/{id}/comments` => voir les commentaires d'une photo   OK
`POST /api/photo/{id}/comments` => poster un commentaire            OK






## Users

### All users
<!-- `GET /api/admin/users` => récupère tous les users du site -->

### All managers
`GET /api/admin/managers` => recupère tous les managers  OK

### All admins
`GET /api/admin/admins` => recupère tous les admins      OK

### All parents
`GET /api/admin/parents` => recupère tous les parents    OK


### Create a new manager
`POST /api/admin/managers` => créé un nouveau manager si on est {  OK
  "lastname": string,
  "firstname": string,
  "email": string,
  "password": string
}

### Create a new parent
`POST /api/manager/parents` => créé un nouveau parent si on est manager ou admin  OK
{
  "lastname": string,
  "firstname": string,
  "email": string,
  "password": string,
  "classe": int // id de la classe
}

### Assign parent to a class
`POST /api/manager/classes/{id}/parents` => assigne un parent existant à une classe   OK
{
  "parent": idParent
}

## Classe

### User Classes
`GET /api/classes` => récup toutes les classes de l'utilisateur connecté. Si c'est un directeur, il les verra TOUTES, si c'est un encadrant, il verra celles ou il est parents et celles qu'il encadre. Si c'est un parent, il verra que celles ou il est parent.  OK

### Create new class    
`POST /api/admin/classe/new`                OK
{
  "name": string,
  "annee_scolaire": date,
  "manager": int // id du manager
}

### Get one classe details
`GET /api/classe/{id}`                     OK

### Update a classe
`PUT /api/classe/{id}/edit`                OK

## Album

### Get albums from a classe
`GET /api/classe/{id}/albums`              OK

### Get one album
`GET /api/album/{id}`                     OK

### Create an album
`POST /api/album/new`                     OK
{
  "classe": int // id de la classe,
  "title": string,
  "description": ?string
}

## Photo

### Get all photos from one album
`GET /api/album/{id}/photos`               OK

### Get one photo
`GET /api/photo/{id}`                     OK

### Create one photo
`POST /api/manager/album/{id}/photos/new`       OK
{
  "image": string (base64),
  "title": string,
  "description": string
}