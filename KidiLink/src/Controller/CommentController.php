<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Comment;
use App\Repository\PhotoRepository;
use App\Repository\CommentRepository;
use App\Security\Voter\PhotoVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    //--------------------------------------- LES  ROUTES  POUR  L ADMIN -------------------------------------//
    //Afficher tous les commentaires
    #[Route('/api/admin/comments', name: 'api_admin_comments', methods: ['GET'])]
    #[Route('/api/manager/comments', name: 'api_manager_comments', methods: ['GET'])]
    #[Route('/api/parent/comments', name: 'api_parent_comments', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        // Récupérer les données pour affichage des commentaires.
        $comments = $commentRepository->findAll();
        return $this->json($comments, 200, [], ['groups' => 'get_comments_collection', 'get_comment_item']);
    }

    //Afficher une photo d'après son ID
    #[Route('/api/admin/comments/{id<\d+>}', name: 'api_admin_comments_show', methods: ['GET'])]
    #[Route('/api/manager/comments/{id<\d+>}', name: 'api_manager_comments_show', methods: ['GET'])]
    #[Route('/api/parent/comments/{id<\d+>}', name: 'api_parent_comments_show', methods: ['GET'])]
    public function show(int $id, CommentRepository $commentRepository): JsonResponse
    {
        // Récupérer le commentaire par son ID
        $comment = $commentRepository->find($id);

        // Vérifier si le commentaire existe
        if (!$comment) {
            return $this->json(['error' => 'Commentaire inexistant.'], 404);
        }

        // Retourner les données du commentaire au format JSON
        return $this->json($comment, 200, [], ['groups' => 'get_comment_item']);
    }

    // Création d'un commentaire
    #[Route('/api/admin/comments/new', name: 'api_admin_comments_new', methods: ['POST'])]
    #[Route('/api/manager/comments/new', name: 'api_manager_comments_new', methods: ['POST'])]
    #[Route('/api/parent/comments/new', name: 'api_parent_comments_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['content'], $jsonData['photo'])) {
            return $this->json(['error' => 'Le champ Content et photo sont requis.'], 400);
        }

        // Gérer les clés étrangères
        // Récupérer la photo par son ID
        $photoId = $jsonData['photo'];
        $photo = $entityManager->getRepository(Photo::class)->find($photoId);

        // on vérifie si le parent a le droit de commenter la photo
        $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $photo);

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
    #[Route('/api/admin/comments/{id}/edit', name: 'api_admin_comments_update', methods: ['PUT'])]
    #[Route('/api/manager/comments/{id}/edit', name: 'api_manager_comments_update', methods: ['PUT'])]
    #[Route('/api/parent/comments/{id}/edit', name: 'api_parent_comments_update', methods: ['PUT'])]
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
    #[Route('/api/admin/comments/{id}/delete', name: 'api_admin_comments_delete', methods: ['DELETE'])]
    #[Route('/api/manager/comments/{id}/delete', name: 'api_manager_comments_delete', methods: ['DELETE'])]
    #[Route('/api/parent/comments/{id}/delete', name: 'api_parent_comments_delete', methods: ['DELETE'])]
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

    // #[Route('/api/pudding', name: 'api_pudding', methods: ['GET'])]
    // #[Route('/api/arsenic', name: 'api_arsenic', methods: ['GET'])]
    // public function testDuPuddingALArsenic()
    // {
    //     return $this->json([
    //         'un peu de poivre en grain' => 'NOOOOOON',
    //         'un peu de sucre en poudre' => 'NOOOOOON',
    //         'un peu de vitriole' => 'NOOON... OUIIIIII'
    //     ]);
    // }
}
