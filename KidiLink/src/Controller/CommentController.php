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
    //Afficher tous les commentaires
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

    // Mise à jour d'un commentaire
#[Route('/api/comments/{id}/edit', name: 'api_comments_update', methods: ['PUT'])]
public function update(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): JsonResponse
{
    // Récupérer le commentaire à mettre à jour
    $comment = $commentRepository->find($id);

    // Vérifier si le commentaire existe
    if (!$comment) {
        return $this->json(['error' => 'Commentaire inexistant.'], 404);
    }

    // Récupérer les données JSON de la requête
    $jsonData = json_decode($request->getContent(), true);

    // Mettre à jour les champs du commentaire
    if (isset($jsonData['content'])) {
        $comment->setContent($jsonData['content']);
    }

    // Enregistrer les modifications
    $entityManager->flush();

    return $this->json(['message' => 'Commentaire mis à jour avec succès'], 200);
}


    //suppression d'un commentaire
    #[Route('/api/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    public function delete(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);

        // Vérifier si le commentaire existe
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvée.'], 404);
        }

        // Supprimer le commentaire
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire supprimé avec succès'], 200);
    }
}
