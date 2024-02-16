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
    #[Route('/api/photos', name: 'api_photos', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): JsonResponse
    {

        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }

       //Afficher une photo d'après son ID
       #[Route('/api/photos/{id<\d+>}', name: 'api_photos_show', methods: ['GET'])]
       public function show(int $id, PhotoRepository $photoRepository): JsonResponse
       {
           // Récupérer l'utilisateur par son ID
           $photo = $photoRepository->find($id);
       
           // Vérifier si l'utilisateur existe
           if (!$photo) {
               return $this->json(['error' => 'Photo inexistante.'], 404);
           }
       
           // Retourner les données de l'utilisateur au format JSON
           return $this->json($photo, 200, [], ['groups' => 'get_user_item']);
       }

    //création d'une photo
    #[Route('/api/photos/nouveau', name: 'api_photos_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['description'], $jsonData['url'], $jsonData['likes'], $jsonData['album'], $jsonData['comment'])) {
            return $this->json(['error' => 'Les champs Title, Description, Url, Likes, album et comment sont requis.'], 400);
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
        if (isset($jsonData['comment'])) {

            $comment = new Comment();
            $comment->setContent($jsonData['comment']);
        }

        $entityManager->persist($photo);
        $entityManager->flush();


        return $this->json(['message' => 'Photo créée avec succès'], 201);
    }

        // Mise à jour d'une photo
        #[Route('/api/photos/{id}/edit', name: 'api_photos_update', methods: ['PUT'])]
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
    #[Route('/api/photos/{id}', name: 'api_photos_delete', methods: ['DELETE'])]
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
}
