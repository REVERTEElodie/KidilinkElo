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
    private function userDeniedOrAllowToAccessClass($classe, $message)
    {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        // Si l'utilisateur est un admin
        if (in_array('ROLE_ADMIN', $roles)) {
            // rien puisqu'il a accès à tout
        }
        //si l'utilisateur est un manager/encadrant
        else if (in_array('ROLE_MANAGER', $roles)) {
            if (!$user->getClassesManaged()->contains($classe) && !$user->getClasses()->contains($classe)) {
                return $this->json(['error' =>  $message], 403);
            }
        } else {
            // Si l'utilisateur est un parent
            // Vérifier si l'utilisateur est associé à la classe spécifiée
            if (!$user->getClasses()->contains($classe)) {
                return $this->json(['error' => $message], 403);
            }
        }
    }
    //Afficher tous les commentaires d'une photo
    #[Route('/api/photo/{id}/comments', name: 'api_comments_albums', methods: ['GET'])]
    public function allComments(int $id, PhotoRepository $photoRepository, CommentRepository $commentRepository): JsonResponse
    {
        // Récupérer la photo par son ID
        $photo = $photoRepository->find($id);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo inexistante.'], 404);
        }

        $this->userDeniedOrAllowToAccessClass($photo, "Vous ne pouvez pas accéder aux commentaires de cette photo.");

        $comments = $photo->getComments();

        return $this->json($comments, 200, [], ['groups' => 'get_comments_collection']);
    }
    //Affichage d'un commentaire
    #[Route('/api/comment/{id}', name: 'api_album', methods: ['GET'])]
    public function comment(int $id, CommentRepository $commentRepository): JsonResponse
    {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        // Récupérer le commentaire par son ID
        $comment = $commentRepository->find($id);

        // Vérifier si le commentaire existe
        if (!$comment) {
            return $this->json(['error' => 'Commentaire inexistant.'], 404);
        }

        $this->userDeniedOrAllowToAccessClass($comment->getPhoto(), "Vous ne pouvez pas accéder à ce commentaire.");

        return $this->json($comment, 200, [], ['groups' => 'get_comment_item']);
    }

    // Création d'un commentaire
    #[Route('/api/comment/new', name: 'api_comment_new', methods: ['POST'])]
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
    #[Route('/api/comment/{id}/edit', name: 'api_comment_update', methods: ['PUT'])]
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
    #[Route('/api/comment/{id}/delete', name: 'api_comment_delete', methods: ['DELETE'])]

    public function delete(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);
        // $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $comment);

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
