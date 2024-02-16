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
{ 
    //Afficher toutes les classes
    #[Route('/api/classes', name: 'api_classes', methods: ['GET'])]
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

    // Mise à jour d'une classe
    #[Route('/api/classes/{id}/edit', name: 'api_classes_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, ClasseRepository $classeRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer la classe à mettre à jour
        $classe = $classeRepository->find($id);

        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe non trouvée.'], 404);
        }

        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Mettre à jour les champs de la classe
        if (isset($jsonData['name'])) {
            $classe->setName($jsonData['name']);
        }
        if (isset($jsonData['annee_scolaire'])) {
            $classe->setAnneeScolaire($jsonData['annee_scolaire']);
        }

        // Enregistrer les modifications
        $entityManager->flush();

        return $this->json(['message' => 'Classe mise à jour avec succès'], 200);
    }

    //suppression d'une classe
    #[Route('/api/classes/{id}', name: 'api_classes_delete', methods: ['DELETE'])]
    public function delete(int $id, ClasseRepository $classeRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $classe = $classeRepository->find($id);

        // Vérifier si la classe existe
        if (!$classe) {
            return $this->json(['error' => 'Classe non trouvée.'], 404);
        }

        // Supprimer la classe
        $entityManager->remove($classe);
        $entityManager->flush();

        return $this->json(['message' => 'Classe supprimée avec succès'], 200);
    }

    // Retourner les classes pour l'utilisateur  parent
    //le me fait référence à l'utilisateur connecté donc qui a reçu le token. (convention de nommage)
    #[Route('/api/me/classes', name: 'api_classes_parents', methods: ['GET'])]
    public function userClasses(ClasseRepository $classeRepository): JsonResponse
    {   
        //ouvrir le blockcode et y mettre ce code pour permettre l'autocomplétion.
        /** @var App\Entity\User $user */

        //permet de recueillir les informations de l'utilisateur
        $user = $this->getUser();
        
        //permet de recueillir les infos de la classe selon l'utilisateur (ici parents)
        $classes = $user->getClasses();
        
        return $this->json($classes, 200, [], ['groups' => 'get_classes_collection', 'get_class_item']);
    }

    // Retourner la ou les classes de l'utilisateur encadrant
    #[Route('/api/me/classes-managed', name: 'api_classes_managed', methods: ['GET'])]
    public function managerClasses(ClasseRepository $classeRepository): JsonResponse
    {
        /** @var App\Entity\User $user */
        //permet de recueillir les informations de l'utilisateur
        $user = $this->getUser();
        
        //permet de recueillir les infos de la classe selon l'utilisateur (ici encadrant)
        $classes = $user->getClassesManaged();
        
        return $this->json($classes, 200, [], ['groups' => 'get_classes_collection', 'get_class_item']);
    }
}
