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
`GET /api/classes` => voir mes classes en tant que directeur
`POST /api/classes/add` => ajouter une classe => ROLE_ADMIN
`POST /api/manager/add` => ajouter un encadrant
`POST /api/classes/encadrants/add` => ajouter un encadrant à une classe

## Routes manager
`GET /api/classes` => voir mes classes en tant qu'encadrant
`GET /api/classes/{id}` => récup les infos d'une classe
`GET /api/classes/{id}/albums` => voir mes albums d'une classes
`GET /api/album/{id}` => est lié à une classe, donc on peut vérif que l'encadrant a bien accès à cet album => retourne les infos de l'album + les photos
`POST /api/classes/{id}/albums/add` => ajouter un album pour une classe
`POST /api/albums/{id}/photos` => ajouter une photo pour une classe
`GET /api/classes/{id}/parents` => voir les parents d'une classe
`POST /api/classes/{id}/parents/add` => ajouter les parents à une classe (création de compte)
`PUT /api/classes/{id}/parents/assign` => assigner un parent EXISTANT à une classe
`GET /api/parents/search?term={searchTerm}` => chercher un parent par son email, son prenom, son nom => dnas le but de pouvoir l'assigner à une classe
`POST /api/photo/{id}/comments` => poster un commentaire

## Parent
`GET /api/classes` => voir mes classes 
`GET /api/classes/{id}` => voir les infos d'une classes
`GET /api/classes/{id}/albums` => voir mes albums d'une classes
`GET /api/album/{id}` => est lié à une classe, donc on peut vérif que le parent a bien accès à cet album => retourne les infos de l'album
`GET /api/album/{id}/photos` => retourne les photos d'un album
`GET /api/photo/{id}` => avoir le détil d'une photo
`GET /api/photo/{id}/comments` => voir les commentaires d'une photo
`POST /api/photo/{id}/comments` => poster un commentaire