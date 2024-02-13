<?php

namespace App\Controller;

use App\Entity\Album;
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
    
    // Création d'un album
    #[Route('/api/albums/nouveau', name: 'api_albums_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['title'], $jsonData['description'], $jsonData['classe'])) {
            return $this->json(['error' => 'Les champs Title, Description et Classe sont requis.'], 400);
        }

        // Gérer les clés étrangères
        // Récupérer la classe par son ID
        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);
        
        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'La classe spécifiée n\'existe pas.'], 400);
        }

        // Créer un nouvel album
        $album = new Album();
        $album->setTitle($jsonData['title']);
        $album->setDescription($jsonData['description']);
        $album->setClasse($classe);

        // Enregistrer l'album
        $entityManager->persist($album);
        $entityManager->flush();
        
        // Retourner les informations au format JSON
        return $this->json(['message' => 'L\'album est créé avec succès'], 201);
    }
}
