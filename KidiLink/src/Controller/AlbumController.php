<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Classe;

use App\Repository\AlbumRepository;
use App\Repository\ClasseRepository;
use App\Security\Voter\AlbumVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlbumController extends AbstractController
{

    //Afficher la liste des albums
    #[Route('/api/admin/albums', name: 'api_admin_albums', methods: ['GET'])]
    #[Route('/api/manager/albums', name: 'api_manager_albums', methods: ['GET'])]
    #[Route('/api/parent/albums', name: 'api_parent_albums', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): JsonResponse
    {
       
        // Récupérer les données pour affichage des albums.
        $albums = $albumRepository->findAll();
        // findAllByUser($this->getUser());
        // SELECT * FROM album
        // INNER JOIN classe ON album.classe_id = classes.id
        // INNER JOIN user_classe ON classes.id = users_classes.classe_id
        // WHERE users_classes.user_id = $this->getUser()->getId()
        // $this->denyAccessUnlessGranted(AlbumVoter::VIEW,$albums);
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }

    //Afficher un album d'après son ID
    #[Route('/api/admin/albums/{id<\d+>}', name: 'api_admin_albums_show', methods: ['GET'])]
    #[Route('/api/manager/albums/{id<\d+>}', name: 'api_manager_albums_show', methods: ['GET'])]
    #[Route('/api/parent/albums/{id<\d+>}', name: 'api_parent_albums_show', methods: ['GET'])]
    public function show(int $id, AlbumRepository $albumRepository): JsonResponse
    {
        // Récupérer l'album par son ID
        $album = $albumRepository->find($id);
        // $this->denyAccessUnlessGranted(AlbumVoter::VIEW,$album);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'Album inexistant.'], 404);
        }

        // Retourner les données de l'utilisateur au format JSON
        return $this->json($album, 200, [], ['groups' => 'get_album_item']);
    }

    //création d'un album
    #[Route('/api/admin/albums/new', name: 'api_admin_albums_nouveau', methods: ['POST'])]
    #[Route('/api/manager/albums/new', name: 'api_manager_albums_nouveau', methods: ['POST'])]
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
        // $this->denyAccessUnlessGranted(AlbumVoter::VIEW,$classe);
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

    //Editer les albums
    #[Route('/api/admin/albums/{id}/edit', name: 'api_admin_albums_update', methods: ['PUT'])]
    #[Route('/api/manager/albums/{id}/edit', name: 'api_manager_albums_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, AlbumRepository $albumRepository): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);

        $album = $albumRepository->find($id);
        // $this->denyAccessUnlessGranted(AlbumVoter::VIEW,$album);

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
    #[Route('/api/manager/albums/{id}/delete', name: 'api_manager_albums_delete', methods: ['DELETE'])]
    public function delete(int $id, AlbumRepository $albumRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $album = $albumRepository->find($id);
        // $this->denyAccessUnlessGranted(AlbumVoter::VIEW,$album);

        // Vérifier si l'album est présent
        if (!$album) {
            return $this->json(['error' => 'Album non trouvée.'], 404);
        }

        // Supprimer la photo
        $entityManager->remove($album);
        $entityManager->flush();

        return $this->json(['message' => 'l\'Album supprimé avec succès'], 200);
    }



}
