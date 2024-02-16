<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Classe;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlbumController extends AbstractController
{

    #[Route('/api/albums', name: 'api_albums', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): JsonResponse
    {
        // Récupérer les données pour affichage des albums.
        $albums = $albumRepository->findAll();
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }

    //création d'un album
    #[Route('/api/albums/nouveau', name: 'api_albums_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['description'], $jsonData['photos'], $jsonData['classe'])) {
            return $this->json(['error' => 'Les champs Title, Description,  photos, classe  sont requis.'], 400);
        }

        //Gérer les clés étrangères
        // Récupérer la photo par son ID
        $photoId = $jsonData['photos'];
        $photo = $entityManager->getRepository(Album::class)->find($photoId);
        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
        }
        
        //Récupérer la classe par son ID
        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Album::class)->find($classeId);
        // Vérifier si la photo existe
        if (!$classe) {
            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
        }

        $album = new Album();
        $album->setTitle($jsonData['title']);
        $album->setDescription($jsonData['description']);
        $album->setClasse($classe);

        if (isset($jsonData['photos'])) {
            foreach ($jsonData['photos'] as $photoId) {
                $photo = $entityManager->getRepository(Photo::class)->find($photoId);
                if (!$photo) {
                    return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
                }
                $album->addPhoto($photo);
            }
        }

        $entityManager->persist($album);
        $entityManager->flush();


        return $this->json(['message' => 'Album créé avec succès'], 201);
    }
    #[Route('/api/albums/{id}/edit', name: 'api_albums_update', methods: ['PUT'])]
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
    #[Route('/api/albums/{id}', name: 'api_albums_delete', methods: ['DELETE'])]
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
    
}
//     @Route("/{id}/edit", name="album_edit")
//     */
//    public function editAction(Request $request, $id, EntityManagerInterface $em)
//    {
//        $album = $em->getRepository(Album::class)->find($id);

//        // Décoder les données JSON
//        $jsonData = $this->get('serializer')->decode($request->getContent(), 'json');

//        // Vérifier la présence des champs obligatoires
//        if (!isset($jsonData['title']) || !isset($jsonData['description']) || !isset($jsonData['classe'])) {
//            return $this->json(['error' => 'Des champs obligatoires sont manquants.'], 400);
//        }

//        // Mettre à jour les champs de l'album
//        $album->setTitle($jsonData['title']);
//        $album->setDescription($jsonData['description']);

//        // Trouver la classe
//        $classe = $em->getRepository(Classe::class)->find($jsonData['classe']);
//        if (!$classe) {
//            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
//        }
//        $album->setClasse($classe);

//        // Supprimer les photos sélectionnées
//        foreach ($jsonData['photosToRemove'] as $photoId) {
//            $photo = $em->getRepository(Photo::class)->find($photoId);
//            if (!$photo) {
//                return $this->json(['error' => 'La photo spécifiée n\'existe pas.'], 400);
//            }
//            $em->remove($photo);
//        }

//        // Définir les nouvelles photos
//        $album->setPhotos($jsonData['photos']);

//        // Enregistrer les modifications
//        $em->flush();

//        // Encoder la réponse JSON
//        $response = $this->get('serializer')->serialize($album, 'json');

//        return $this->json($response);
//    }
// }




