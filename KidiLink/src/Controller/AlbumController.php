<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Classe;

use App\Repository\AlbumRepository;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlbumController extends AbstractController
{
    //--------------------------------------- LES  ROUTES  POUR  L ADMIN -------------------------------------//
    //Afficher la liste des albums
    #[Route('/api/admin/albums', name: 'api_admin_albums', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): JsonResponse
    {
        // Récupérer les données pour affichage des albums.
        $albums = $albumRepository->findAll();
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }

    //Afficher un album d'après son ID
    #[Route('/api/admin/albums/{id<\d+>}', name: 'api_admin_albums_show', methods: ['GET'])]
    public function show(int $id, AlbumRepository $albumRepository): JsonResponse
    {
        // Récupérer l'album par son ID
        $album = $albumRepository->find($id);

        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }

        // Retourner les données de l'utilisateur au format JSON
        return $this->json($album, 200, [], ['groups' => 'get_album_item']);
    }

    //création d'un album
    #[Route('/api/admin/albums/new', name: 'api_admin_albums_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['classe'])) {
            return $this->json(['error' => 'Les champs Title et classe sont requis.'], 400);
        }

        //Gérer les clés étrangères
        //Récupérer la classe par son ID
        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
        }

        $description = $jsonData['description'] ?? null;

        $album = new Album();
        $album->setTitle($jsonData['title']);
        $album->setDescription($description);
        $album->setClasse($classe);

        $entityManager->persist($album);
        $entityManager->flush();


        return $this->json(['message' => 'Album créé avec succès'], 201);
    }


    #[Route('/api/admin/albums/{id}/edit', name: 'api_admin_albums_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, AlbumRepository $albumRepository): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);

        $album = $albumRepository->find($id);

        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album non trouvé.'], 404);
        }

        // Mettre à jour les données de l'album
        if (isset($jsonData['title'])) {
            $album->setTitle($jsonData['title']);
        }

        if (isset($jsonData['description'])) {
            $album->setDescription($jsonData['description']);
        }

        // Mettre à jour les photos de l'album
        if (isset($jsonData['photos'])) {
            // Supprimer toutes les photos actuelles de l'album
            foreach ($album->getPhotos() as $photo) {
                $album->removePhoto($photo);
            }
            foreach ($jsonData['photos'] as $photoId) {
                $photo = $entityManager->getRepository(Photo::class)->find($photoId);
                if (!$photo) {
                    return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
                }
                $album->addPhoto($photo);
            }
        }

        $entityManager->flush();

        return $this->json(['message' => 'Album mis à jour avec succès'], 200);
    }


    //suppression d'un album
    #[Route('/api/admin/albums/{id}/delete', name: 'api_admin_albums_delete', methods: ['DELETE'])]
    public function delete(int $id, AlbumRepository $albumRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $album = $albumRepository->find($id);

        // Vérifier si l'album est présent
        if (!$album) {
            return $this->json(['error' => 'Album non trouvée.'], 404);
        }

        // Supprimer la photo
        $entityManager->remove($album);
        $entityManager->flush();

        return $this->json(['message' => 'l\'Album supprimé avec succès'], 200);
    }
    //...............................LES ROUTES POUR LES MANAGER.................................//
    //Afficher la liste des albums
    //TODO afficher des albums pour une classe /api/manager/classes/{id}/album

    private ClasseRepository $classeRepository;

    public function __construct(ClasseRepository $classeRepository)
    {
        $this->classeRepository = $classeRepository;
    }

    #[Route('/api/manager/classes/{id}/albums', name: 'api_manager_classe_albums', methods: ['GET'])]
    public function indexManagerForClasse(int $id): JsonResponse
    {
        // Récupérer la classe par son ID
        $classe = $this->classeRepository->find($id);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe inexistante.'], 404);
        }
        // Récupérer les albums de la classe
        $albums = $classe->getAlbums();
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }

    //Afficher un album d'après son ID
    //TODO l'album pour une classe en particulier /api/manager/classes/{id}/albums/{id}
    #[Route('/api/manager/classes/{id}/albums/{albumId}', name: 'api_manager_classe_album_show', methods: ['GET'])]
    public function showManagerForClasse(int $id, int $albumId): JsonResponse
    {
        // Récupérer la classe par son ID
        $classe = $this->classeRepository->find($id);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe inexistante.'], 404);
        }
        // Récupérer l'album de la classe par son ID
        $album = $classe->getAlbumById($albumId);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }
        return $this->json($album, 200, [], ['groups' => 'get_album_item']);
    }

    //création d'un album
    #[Route('/api/manager/albums/new', name: 'api_manager_albums_nouveau', methods: ['POST'])]
    public function createManager(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['description'], $jsonData['photos'], $jsonData['classe'])) {
            return $this->json(['error' => 'Les champs Title, Description, photos, classe sont requis.'], 400);
        }

        // Vérifier si 'photos' est un tableau
        if (!is_array($jsonData['photos'])) {
            return $this->json(['error' => 'La clé photos doit être un tableau.'], 400);
        }

        //Gérer les clés étrangères
        //Récupérer la classe par son ID
        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
        }

        $album = new Album();
        $album->setTitle($jsonData['title']);
        $album->setDescription($jsonData['description']);
        $album->setClasse($classe);

        foreach ($jsonData['photos'] as $photoId) {
            $photo = $entityManager->getRepository(Photo::class)->find($photoId);
            if (!$photo) {
                return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
            }
            $album->addPhoto($photo);
        }

        $entityManager->persist($album);
        $entityManager->flush();


        return $this->json(['message' => 'Album créé avec succès'], 201);
    }

    //Editer l'album
    #[Route('/api/manager/albums/{id}/edit', name: 'api_manager_albums_update', methods: ['PUT'])]
    public function updateManager(int $id, Request $request, EntityManagerInterface $entityManager, AlbumRepository $albumRepository): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);

        $album = $albumRepository->find($id);

        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album non trouvé.'], 404);
        }

        // Mettre à jour les données de l'album
        if (isset($jsonData['title'])) {
            $album->setTitle($jsonData['title']);
        }

        if (isset($jsonData['description'])) {
            $album->setDescription($jsonData['description']);
        }

        // Mettre à jour les photos de l'album
        if (isset($jsonData['photos'])) {
            // Supprimer toutes les photos actuelles de l'album
            foreach ($album->getPhotos() as $photo) {
                $album->removePhoto($photo);
            }
            foreach ($jsonData['photos'] as $photoId) {
                $photo = $entityManager->getRepository(Photo::class)->find($photoId);
                if (!$photo) {
                    return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
                }
                $album->addPhoto($photo);
            }
        }

        $entityManager->flush();

        return $this->json(['message' => 'Album mis à jour avec succès'], 200);
    }


    //suppression d'un album
    #[Route('/api/manager/albums/{id}/delete', name: 'api_manager_albums_delete', methods: ['DELETE'])]
    public function deleteManager(int $id, AlbumRepository $albumRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $album = $albumRepository->find($id);

        // Vérifier si l'album est présent
        if (!$album) {
            return $this->json(['error' => 'Album non trouvée.'], 404);
        }

        // Supprimer la photo
        $entityManager->remove($album);
        $entityManager->flush();

        return $this->json(['message' => 'l\'Album supprimé avec succès'], 200);
    }

    //--------------------------------------- LES  ROUTES  POUR  LES PARENTS -------------------------------------//
   //Afficher la liste des albums
    //TODO Afficher les albums d'une classe /api/parent/classes/{id}/albums

    #[Route('/api/parent/classes/{id}/albums', name: 'api_parent_classe_albums', methods: ['GET'])]
    public function indexParentForClasse(int $id): JsonResponse
    {
        // Récupérer la classe par son ID
        $classe = $this->classeRepository->find($id);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe inexistante.'], 404);
        }
        // Récupérer les albums de la classe
        $albums = $classe->getAlbums();
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }
    //Afficher un album d'après son ID
    //TODO Afficher l'album d'une classe /api/parent/classes/{id}/albums/{id}
    #[Route('/api/parent/classes/{id}/albums/{albumId}', name: 'api_parent_classe_album_show', methods: ['GET'])]
    public function showParentForClasse(int $id, int $albumId): JsonResponse
    {
        // Récupérer la classe par son ID
        $classe = $this->classeRepository->find($id);
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe inexistante.'], 404);
        }
        // Récupérer l'album de la classe par son ID
        $album = $classe->getAlbumById($albumId);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }
        return $this->json($album, 200, [], ['groups' => 'get_album_item']);
    }



}
