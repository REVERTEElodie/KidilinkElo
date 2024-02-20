<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhotoController extends AbstractController
{
    //Afficher toutes les photos
    #[Route('/api/admin/photos', name: 'api_admin_photos', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): JsonResponse
    {

        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }

    //Afficher une photo d'après son ID
    #[Route('/api/admin/photos/{id<\d+>}', name: 'api_admin_photos_show', methods: ['GET'])]
    public function show(int $id, PhotoRepository $photoRepository): JsonResponse
    {
        // Récupérer la photo par son ID
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo inexistante.'], 404);
        }

        // Retourner les données de la photo au format JSON
        return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    }

    //création d'une photo
    #[Route('/api/admin/photos/new', name: 'api_admin_photos_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['url'], $jsonData['album'])) {
            return $this->json(['error' => 'Les champs  Url,  album  sont requis.'], 400);
        }
        // Validate URL
        if (!filter_var($jsonData['url'], FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'URL invalide.'], 400);
        }
        //Gérer les clés étrangères
        // Récupérer l'album par son ID
        $albumId = $jsonData['album'];
        $album = $entityManager->getRepository(Album::class)->find($albumId);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'L\'album spécifié n\'existe pas.'], 400);
        }
        $photo = new Photo();
        $photo->setTitle($jsonData['title']);
        $photo->setDescription($jsonData['description']);
        $photo->setUrl($jsonData['url']);
        $photo->setLikes($jsonData['likes']);
        $photo->setAlbum($album);
        // Ajouter le commentaire s'il est présent
        // if (isset($jsonData['comment'])) {

        //     $comment = new Comment();
        //     $comment->setContent($jsonData['comment']);
        // }

        $entityManager->persist($photo);
        $entityManager->flush();


        return $this->json(['message' => 'Photo créée avec succès'], 201);
    }

    // Mise à jour d'une photo
    #[Route('/api/admin/photos/{id}/edit', name: 'api_admin_photos_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $photo = $entityManager->getRepository(Photo::class)->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo non trouvée.'], 404);
        }

        $jsonData = json_decode($request->getContent(), true);
        // Mettre à jour les champs de la photo si présents dans les données JSON
        if (isset($jsonData['title'])) {
            $photo->setTitle($jsonData['title']);
        }
        if (isset($jsonData['description'])) {
            $photo->setDescription($jsonData['description']);
        }
        if (isset($jsonData['url'])) {
            $photo->setUrl($jsonData['url']);
        }
        if (isset($jsonData['likes'])) {
            $photo->setLikes($jsonData['likes']);
        }
        // Mettre à jour le commentaire s'il est présent dans les données JSON
        if (isset($jsonData['comment'])) {
            $comment = $photo->getComment();
            if (!$comment) {
                $comment = new Comment();
                $photo->setComment($comment);
            }
            $comment->setContent($jsonData['comment']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Photo mise à jour avec succès'], 200);
    }

    //suppression d'une photo
    #[Route('/api/admin/photos/{id}/delete', name: 'api_admin_photos_delete', methods: ['DELETE'])]
    public function delete(int $id, PhotoRepository $photoRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo non trouvée.'], 404);
        }

        // Supprimer la photo
        $entityManager->remove($photo);
        $entityManager->flush();

        return $this->json(['message' => 'Photo supprimée avec succès'], 200);
    }

    //--------------------------------------- LES  ROUTES  POUR LES MANAGER -------------------------------------//
    //Afficher toutes les photos
    // TODO afficher les photos des albums de sa ou ses classes /api/manager/classes/{id}/albums/photos/


    #[Route('/api/manager/photos', name: 'api_manager_photos', methods: ['GET'])]
    public function indexManager(PhotoRepository $photoRepository): JsonResponse
    {

        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }

    //Afficher une photo d'après son ID
    // TODO afficher la photo des albums de sa ou ses classes /api/manager/classes/{id}/albums/photos//{id}

    #[Route('/api/manager/photos/{id<\d+>}', name: 'api_manager_photos_show', methods: ['GET'])]
    public function showManager(int $id, PhotoRepository $photoRepository): JsonResponse
    {
        // Récupérer la photo par son ID
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo inexistante.'], 404);
        }

        // Retourner les données de la photo au format JSON
        return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    }

    //création d'une photo
    #[Route('/api/manager/photos/new', name: 'api_manager_photos_new', methods: ['POST'])]
    public function createManager(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['description'], $jsonData['url'], $jsonData['likes'], $jsonData['album'])) {
            return $this->json(['error' => 'Les champs Title, Description, Url, Likes, album  sont requis.'], 400);
        }
        // Validate URL
        if (!filter_var($jsonData['url'], FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'URL invalide.'], 400);
        }
        //Gérer les clés étrangères
        // Récupérer l'album par son ID
        $albumId = $jsonData['album'];
        $album = $entityManager->getRepository(Album::class)->find($albumId);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'L\'album spécifié n\'existe pas.'], 400);
        }
        $photo = new Photo();
        $photo->setTitle($jsonData['title']);
        $photo->setDescription($jsonData['description']);
        $photo->setUrl($jsonData['url']);
        $photo->setLikes($jsonData['likes']);
        $photo->setAlbum($album);
        // Ajouter le commentaire s'il est présent
        // if (isset($jsonData['comment'])) {

        //     $comment = new Comment();
        //     $comment->setContent($jsonData['comment']);
        // }

        $entityManager->persist($photo);
        $entityManager->flush();


        return $this->json(['message' => 'Photo créée avec succès'], 201);
    }

    // Mise à jour d'une photo
    #[Route('/api/manager/photos/{id}/edit', name: 'api_manager_photos_update', methods: ['PUT'])]
    public function updateManager(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $photo = $entityManager->getRepository(Photo::class)->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo non trouvée.'], 404);
        }

        $jsonData = json_decode($request->getContent(), true);
        // Mettre à jour les champs de la photo si présents dans les données JSON
        if (isset($jsonData['title'])) {
            $photo->setTitle($jsonData['title']);
        }
        if (isset($jsonData['description'])) {
            $photo->setDescription($jsonData['description']);
        }
        if (isset($jsonData['url'])) {
            $photo->setUrl($jsonData['url']);
        }
        if (isset($jsonData['likes'])) {
            $photo->setLikes($jsonData['likes']);
        }
        // Mettre à jour le commentaire s'il est présent dans les données JSON
        if (isset($jsonData['comment'])) {
            $comment = $photo->getComment();
            if (!$comment) {
                $comment = new Comment();
                $photo->setComment($comment);
            }
            $comment->setContent($jsonData['comment']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Photo mise à jour avec succès'], 200);
    }

    //suppression d'une photo
    #[Route('/api/manager/photos/{id}/delete', name: 'api_manager_photos_delete', methods: ['DELETE'])]
    public function deleteManager(int $id, PhotoRepository $photoRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo non trouvée.'], 404);
        }

        // Supprimer la photo
        $entityManager->remove($photo);
        $entityManager->flush();

        return $this->json(['message' => 'Photo supprimée avec succès'], 200);
    }

    //--------------------------------------- LES  ROUTES  POUR  LES PARENTS -------------------------------------//

    //Afficher toutes les photos
    // TODO afficher les photos des albums de sa ou ses classes /api/parent/classes/{id}/albums/photos/


    #[Route('/api/parent/photos', name: 'api_parent_photos', methods: ['GET'])]
    public function indexParent(PhotoRepository $photoRepository): JsonResponse
    {

        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }
    //Afficher une photo d'après son ID
    #[Route('/api/parent/photos/{id<\d+>}', name: 'api_parent_photos_show', methods: ['GET'])]
    public function showParent(int $id, PhotoRepository $photoRepository): JsonResponse
    {
        // Récupérer la photo par son ID
        // TODO afficher les photos des albums de sa ou ses classes /api/parent/classes/{id}/albums/photos/
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo inexistante.'], 404);
        }

        // Retourner les données de la photo au format JSON
        return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    }
}
