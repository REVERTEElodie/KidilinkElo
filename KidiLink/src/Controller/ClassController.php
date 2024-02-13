<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Classe;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassController extends AbstractController
{   #[Route('/api/classes', name: 'api_classes', methods: ['GET'])]
    public function index(ClasseRepository $classeRepository): JsonResponse
    {
        //Recup les données pour affichage des classes.
        $classes = $classeRepository->findAll();
        return $this->json($classes, 200, [], ['groups' => 'get_classes_collection', 'get_class_item']);
    }

     //création d'une classe
     // Réaliser sa route en POST
     #[Route('/api/classes/nouveau', name: 'api_classes_nouveau', methods: ['POST'])]
     public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
     {
    // Récupérer les données JSON de la requête
     $jsonData = json_decode($request->getContent(), true);
     
     // Valider les données (ex: vérifier si les champs requis sont présents)
     if (!isset($jsonData['name'], $jsonData['annee_scolaire'])) {
         return $this->json(['error' => 'Les champs name, description et album sont requis.'], 400);
     }

         $classe = new Classe();
         $classe->setName($jsonData['name']);
         $classe->setAnneeScolaire($jsonData['annee_scolaire']);

 // COMMIT EN PUSH SUR LA BDD
    $entityManager->persist($classe);
    $entityManager->flush();
    // retourner les infos au format json
         return $this->json(['message' => 'La classe est créée avec succès'], 201);
     }

    }



