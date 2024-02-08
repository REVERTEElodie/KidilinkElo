| URL                |  Méthode HTTP    | Contrôleur     | Méthode | Titre HTML           | Content                                                                                      |

| -------------------| -----------------------  | --------------------| ------------| ------------------------ | ---------------------------------------------------------------------------------------|

| /api             | GET  | MainController     | home  | Page d'Accueil    | Page d'accueil affichant les derniers albums ajoutés |  |  |  |
| ---------------- | ---- | ------------------ | ----- | ----------------- | ----------------------------------------------------- | - | - | - |
| /api/login_check | POST | SecurityController | login | Page de connexion | -                                                     |  |  |  |

/api/users		| GET    | UserController	      | index     | Affichage des utilisateurs | Récupération de la liste des utilisateurs

/api/users		| POST  | UserController	      | create     | Création d'un utilisateur | Récupération de la liste des utilisateurs

/api/albums	| GET    | AlbumController      | index     | Gestion des albums		| Récupération et créations d'albums

/api/albums	| POST  | AlbumController      | create    | Gestion des albums		| Récupération et créations d'albums

/api/albums{id}| GET    | AlbumController     | show     | Gestion des albums		| Récupération d'un album

/api/albums{id}| PUT    | AlbumController     | update  | Gestion des albums		| Mise à jour d'un album

/api/albums{id}| DELETE  | AlbumController | delete  | Gestion des albums		| Suppression d'un album

/api/photos	| GET  | PhotoController      | index    | Gestion des photos		| Récupération de photos

/api/photos	| POST | PhotoController      | create    | Gestion des photos		| Récupération d'une photo

/api/photos{id}| GET  | PhotoController      | show    | Gestion des photos		| Récupération de photos

/api/photos{id}| PUT  | PhotoController      | update    | Gestion des photos		| Mise à jour d'une photo

/api/photos{id}| DELETE | PhotoController  | delete    | Gestion des photos		| Suppression d'une photo

/api/classes	| GET  | ClassController      | index    | Gestion des classes		| Récupération des classes

/api/classes	| POST | ClassController      | create    | Gestion des classes		| Création de classes

/api/classes{id}| GET  | ClassController      | show    | Gestion des classes		| Récupération d'une classe

/api/classes	| PUT  | ClassController      | update    | Gestion des classes		| Mise à jour d'une classe

/api/classes	| DELETE  | ClassController | delete    | Gestion des classes		| Suppression d'une classe

/api/comments| GET  | ClassController      | index    | Gestion des commentaires	| Récupération des commentaires

/api/comments| POST | ClassController      | create  | Gestion des commentaires	| Création des commentaires

/api/comments{id}| GET  | ClassController      | show    | Gestion des commentaires	| Récupération d'un commentaire

/api/comments{id}| PUT  | ClassController      | update    | Gestion des commentaires	| Mise à jour d'un commentaire

/api/comments{id}| DELETE  | ClassController | delete    | Gestion des commentaires	| Suppression d'un commentaire