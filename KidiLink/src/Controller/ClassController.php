<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Classe;
use App\Security\Voter\ClasseVoter;
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

    // Get all classes for current user
    #[Route('/api/classes', name: 'api_classes', methods: ['GET'])]
    public function classes(ClasseRepository $classeRepository) {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $classes = [];

        // Si je suis directeur, je dois pouvoir voir TOUTES les classes
        if(in_array('ROLE_ADMIN', $roles)) {
            $classes['managed'] = $classeRepository->findAll();
        }
        // Si je suis manager, je dois pouvoir voir les classes dont je suis l'encadrant
        else if(in_array('ROLE_MANAGER', $roles)) {
          
            $classes['managed'] = $user->getClassesManaged();
            $classes['parent'] = $user->getClasses();
        }
        // Si je suis parent, je dois pouvoir voir les classes auxquelles je suis assignée
        else {
            $classes['parent'] = $user->getClasses();
        }

        return $this->json($classes, 200, [], [
            'groups' => ['get_classes_collection']
        ]);
    }

    // Add a new classes, only for admin
    #[Route('/api/admin/classes/new', name: 'api_classes_add', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['name'], $jsonData['annee_scolaire'], $jsonData['manager'])) {
            return $this->json(['error' => 'Les champs name, annee_scolaire et manager sont requis.'], 400);
        }

        // Récupérer l'entité User correspondante au manager à partir de son ID
        $managerId = $jsonData['manager'];
        $manager = $entityManager->getRepository(User::class)->find($managerId);

        // Vérifier si le manager existe
        if (!$manager) {
            return $this->json(['error' => 'Le manager spécifié n\'existe pas.'], 400);
        }

        // Créer une nouvelle instance de la classe et lier le manager
        $classe = new Classe();
        $classe->setName($jsonData['name']);
        $classe->setAnneeScolaire($jsonData['annee_scolaire']);
        $classe->setManager($manager);

        // COMMIT EN PUSH SUR LA BDD
        $entityManager->persist($classe);
        $entityManager->flush();
        // retourner les infos au format json
        return $this->json(['message' => 'La classe est créée avec succès'], 201);
    }
       // Get one classe detail
    #[Route('/api/classes/{id}', name: 'api_classe_detail', methods: ['GET'])]
    public function classeDetail(ClasseRepository $classeRepository, int $id)
    {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $classe = $classeRepository->find($id);

        if (!$classe) {
            return $this->json(['error' => 'La classe n\'existe pas.'], 404);
        }

        // Si je suis directeur, je dois pouvoir voir TOUTES les classes
        if (in_array('ROLE_ADMIN', $roles)) {
            
        }
        // Si je suis manager, je dois pouvoir voir les classes dont je suis l'encadrant
        else if (in_array('ROLE_MANAGER', $roles)) {

          if(!$user->getClassesManaged()->contains($classe) && !$user->getClasses()->contains($classe)) {
            return $this->json(['error' => "Vous ne pouvez pas accéder aux infos de cette classe."], 403);
          }
        }
        // Si je suis parent, je dois pouvoir voir les classes auxquelles je suis assignée
        else {
            if(!$user->getClasses()->contains($classe)) {
            return $this->json(['error' => "Vous ne pouvez pas accéder aux infos de cette classe."], 403);
            }
        }

        return $this->json($classe, 200, [], [
            'groups' => ['get_class_item']
        ]);
    }



    // //Afficher toutes les classes
    // #[Route('/api/admin/classes', name: 'api_admin_classes', methods: ['GET'])]
    // #[Route('/api/manager/classes', name: 'api_manager_classes', methods: ['GET'])]
    // public function index(ClasseRepository $classeRepository): JsonResponse
    // {
    //     //Recup les données pour affichage des classes.
    //     $classes = $classeRepository->findAll();
    //     return $this->json($classes, 200, [], ['groups' => 'get_classes_collection', 'get_class_item']);
    // }

    // //Afficher une classe d'après son ID
    // #[Route('/api/admin/classes/{id<\d+>}', name: 'api_admin_classes_show', methods: ['GET'])]
    // #[Route('/api/manager/classes/{id<\d+>}', name: 'api_manager_classes_show', methods: ['GET'])]
    // public function show(int $id, ClasseRepository $classeRepository): JsonResponse
    // {
      
    //     // Récupérer la classe par son ID
    //     $classe = $classeRepository->find($id);

    //       $this->denyAccessUnlessGranted(ClasseVoter::CLASSE, $classe);

    //     // Vérifier si la classe existe
    //     if (!$classe) {
    //         return $this->json(['error' => 'Utilisateur inexistant.'], 404);
    //     }

    //     // Retourner les données de l'utilisateur au format JSON
    //     return $this->json($classe, 200, [], ['groups' => 'get_class_item']);
    // }

    // //création d'une classe
    // // Réaliser sa route en POST
    // #[Route('/api/admin/classes/new', name: 'api_admin_classes_nouveau', methods: ['POST'])]
    // public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     // Récupérer les données JSON de la requête
    //     $jsonData = json_decode($request->getContent(), true);

    //     // Valider les données (ex: vérifier si les champs requis sont présents)
    //     if (!isset($jsonData['name'], $jsonData['annee_scolaire'], $jsonData['manager'])) {
    //         return $this->json(['error' => 'Les champs name, annee_scolaire et manager sont requis.'], 400);
    //     }

    //     // Récupérer l'entité User correspondante au manager à partir de son ID
    //     $managerId = $jsonData['manager'];
    //     $manager = $entityManager->getRepository(User::class)->find($managerId);

    //     // Vérifier si le manager existe
    //     if (!$manager) {
    //         return $this->json(['error' => 'Le manager spécifié n\'existe pas.'], 400);
    //     }

    //     // Créer une nouvelle instance de la classe et lier le manager
    //     $classe = new Classe();
    //     $classe->setName($jsonData['name']);
    //     $classe->setAnneeScolaire($jsonData['annee_scolaire']);
    //     $classe->setManager($manager);

    //     // COMMIT EN PUSH SUR LA BDD
    //     $entityManager->persist($classe);
    //     $entityManager->flush();
    //     // retourner les infos au format json
    //     return $this->json(['message' => 'La classe est créée avec succès'], 201);
    // }

    // // Mise à jour d'une classe
    // #[Route('/api/admin/classes/{id}/edit', name: 'api_admin_classes_update', methods: ['PUT'])]
    // public function update(int $id, Request $request, ClasseRepository $classeRepository, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     // Récupérer la classe à mettre à jour
    //     $classe = $classeRepository->find($id);

    //     // Vérifier si la classe existe
    //     if (!$classe) {
    //         return $this->json(['error' => 'Classe non trouvée.'], 404);
    //     }

    //     // Récupérer les données JSON de la requête
    //     $jsonData = json_decode($request->getContent(), true);

    //     // Mettre à jour les champs de la classe
    //     if (isset($jsonData['name'])) {
    //         $classe->setName($jsonData['name']);
    //     }
    //     if (isset($jsonData['annee_scolaire'])) {
    //         $classe->setAnneeScolaire($jsonData['annee_scolaire']);
    //     }

    //     // Enregistrer les modifications
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Classe mise à jour avec succès'], 200);
    // }

    // //suppression d'une classe
    // #[Route('/api/admin/classes/{id}/delete', name: 'api_admin_classes_delete', methods: ['DELETE'])]
    // public function delete(int $id, ClasseRepository $classeRepository, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     $classe = $classeRepository->find($id);

    //     // Vérifier si la classe existe
    //     if (!$classe) {
    //         return $this->json(['error' => 'Classe non trouvée.'], 404);
    //     }

    //     // Supprimer la classe
    //     $entityManager->remove($classe);
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Classe supprimée avec succès'], 200);
    // }
}
