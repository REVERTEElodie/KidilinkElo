<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/api/comments', name: 'api_comments', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        // Récupérer les données pour affichage des commentaires.
        $comments = $commentRepository->findAll();
        return $this->json($comments, 200, [], ['groups' => 'get_comments_collection', 'get_comment_item']);
    }

    // Création d'un commentaire
    #[Route('/api/comments/nouveau', name: 'api_comments_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);
        
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['content'], $jsonData['photo'])) {
            return $this->json(['error' => 'Le champ Content est requis.'], 400);
        }
        
        // Gérer les clés étrangères
        // Récupérer la photo par son ID
        $photoId = $jsonData['photo'];
        $photo = $entityManager->getRepository(Photo::class)->find($photoId);
        
        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
        }
        
        // Créer un nouveau commentaire
        $comment = new Comment();
        $comment->setContent($jsonData['content']);
        $comment->setPhoto($photo);

        // Enregistrer le commentaire
        $entityManager->persist($comment);
        $entityManager->flush();
        
        // Retourner les informations au format JSON
        return $this->json(['message' => 'Le commentaire a été ajouté avec succès'], 201);
    }
}
