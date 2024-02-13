<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Comment;
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

    #[Route('/api/photos', name: 'api_photos', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): JsonResponse
    {
        //Recup les données pour affichage des classes.
        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }
     
     //création d'une photo
     // Réaliser sa route en POST
     #[Route('/api/photos/nouveau', name: 'api_photos_nouveau', methods: ['POST'])]
     public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse 
     {
    // Récupérer les données JSON de la requête
     $jsonData = json_decode($request->getContent(), true);
 
     // Valider les données (ex: vérifier si les champs requis sont présents)
     if (!isset($jsonData['title'], $jsonData['description'], $jsonData['url'], $jsonData['likes'], $jsonData['album'], $jsonData['comment'])) {
         return $this->json(['error' => 'Les champs Title, Description, Url et Likes sont requis.'], 400);
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
    // créer une nouvelle instance de l'entité Comment
    $comment = new Comment();
    $comment->setContent($jsonData['comment']);
}
        
 // COMMIT EN PUSH SUR LA BDD
    $entityManager->persist($photo);
    $entityManager->flush();

    // retourner les infos au format json
         
         return $this->json(['message' => 'Commentaire créé avec succès'], 201);
     }
    }



