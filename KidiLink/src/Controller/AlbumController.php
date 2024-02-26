<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Classe;
use App\Repository\AlbumRepository;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlbumController extends AbstractController
{

    private function userDeniedOrAllowToAccessClass($classe, $message)
    {
        /** @var \App\Entity\User $user */
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

    #[Route('/api/classe/{id}/albums', name: 'api_classes_albums', methods: ['GET'])]
    public function allAlbums(int $id, ClasseRepository $classeRepository, AlbumRepository $albumRepository): JsonResponse
    {
        // Récupérer la classe par son ID
        $classe = $classeRepository->find($id);

        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe inexistante.'], 404);
        }
        
        // Contraintes d'accès 
        $message = "Vous ne pouvez pas accéder aux albums de cette classe.";
        $response = $this->userDeniedOrAllowToAccessClass($classe, $message);
        if ($response) {
            return $response;
        }
        
        // Récupérer les albums
        $albums = $classe->getAlbums();

        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection']);
    }

    #[Route('/api/album/{id}', name: 'api_album', methods: ['GET'])]
    public function album(int $id, AlbumRepository $albumRepository): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        // Récupérer l'album par son ID
        $album = $albumRepository->find($id);

        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }
        
        // Contraintes d'accès
        $message = "Vous ne pouvez pas accéder à cet album.";
        $response = $this->userDeniedOrAllowToAccessClass($album->getClasse(), $message);
        if ($response) {
            return $response;
        }

        return $this->json($album, 200, [], ['groups' => 'get_album_item']);
    }
    
    #[Route('/api/album/new', name: 'api_manager_album_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['classe'])) {
            return $this->json(['error' => 'Les champs Title et classe sont requis.'], 400);
        }

        // Gérer les clés étrangères
        // Récupérer la classe par son ID
        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);

        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
        }
        
        // Contraintes d'accès
        $message = "Vous ne pouvez pas accéder à cet album.";
        $response = $this->userDeniedOrAllowToAccessClass($classe, $message);
        if ($response) {
            return $response;
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

    #[Route('/api/album/{id}/edit', name: 'api_album_update', methods: ['PUT'])]
    public function update(int $id, Request $request, AlbumRepository $albumRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer l'album à mettre à jour
        $album = $albumRepository->find($id);

        // Vérifier si le album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }

        // Contraintes d'accès
        $message = "Vous ne pouvez pas accéder à cet album.";
        $response = $this->userDeniedOrAllowToAccessClass($album->getClasse(), $message);
        if ($response) {
            return $response;
        }

        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Mettre à jour les champs du album
        if (isset($jsonData['title'])) {
            $album->setTitle($jsonData['title']);
        }
        if (isset($jsonData['description'])) {
            $album->setDescription($jsonData['description']);
        }

        // Enregistrer les modifications
        $entityManager->flush();

        return $this->json(['message' => 'Album mis à jour avec succès'], 200);
    }

    #[Route('/api/album/{id}/delete', name: 'api_album_delete', methods: ['DELETE'])]
    public function delete(int $id, AlbumRepository $albumRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $album = $albumRepository->find($id);
        // Vérifier si l album existe
        if (!$album) {
            return $this->json(['error' => 'Album non trouvée.'], 404);
        }

        // Contraintes d'accès
        $message = "Vous ne pouvez pas accéder à cet album.";
        $response = $this->userDeniedOrAllowToAccessClass($album->getClasse(), $message);
        if ($response) {
            return $response;
        }

        // Supprimer l album
        $entityManager->remove($album);
        $entityManager->flush();
        return $this->json(['message' => 'Album supprimée avec succès'], 200);
    }
}
