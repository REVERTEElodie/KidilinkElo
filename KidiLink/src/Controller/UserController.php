<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\User;
use App\Repository\ClasseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    /**
     * List user for admin
     */
    #[Route('/api/admin/users', name: 'api_users', methods: ['GET'])]
    public function allUsers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * List managers for admin
     */
    #[Route('/api/admin/managers', name: 'api_managers', methods: ['GET'])]
    public function allManagers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllManagers();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * Create a new manager for admin
     */
    #[Route('/api/admin/managers', name: 'api_managers_create', methods: ['POST'])]
    public function addManager(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($jsonData[$field])) {
                return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
            }
        }

        $alreadyExists = $userRepository->findBy(['email' => $jsonData['email']]);

        if ($alreadyExists) {
            return $this->json(['message' => "Le manager avec l'email {$jsonData['email']} existe déjà."], 403);
        }

        $user = new User();
        $user->setFirstName($jsonData['firstname']);
        $user->setLastName($jsonData['lastname']);
        $user->setEmail($jsonData['email']);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);

        // Définition des rôles
        $user->setRoles(['ROLE_MANAGER']);

        // Enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        // TODO: Envoyer un email au manager pour lui indiquer ou se logger, avec quel identfiant (email) et mdp

        return $this->json(['message' => 'Manager créé avec succès'], 201);
    }

    /**
     * Create a new parent for admin or manager
     * it needs classe to set the classe for the parent
     */
    #[Route('/api/manager/parents', name: 'api_parents_create', methods: ['POST'])]
    public function addParent(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'classe'];
        foreach ($requiredFields as $field) {
            if (empty($jsonData[$field])) {
                return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
            }
        }

        $alreadyExists = $userRepository->findBy(['email' => $jsonData['email']]);

        if ($alreadyExists) {
            return $this->json(['message' => "Le parent avec l'email {$jsonData['email']} existe déjà."], 403);
        }

        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);

        if (!$classe) {
            return $this->json(['message' => "Cette classe n'existe pas"], 403);
        }


        $user = new User();
        $user->setFirstName($jsonData['firstname']);
        $user->setLastName($jsonData['lastname']);
        $user->setEmail($jsonData['email']);
        $user->addClass($classe);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);

        // Définition des rôles
        $user->setRoles(['ROLE_PARENT']);

        // Enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        // TODO: Envoyer un email au manager pour lui indiquer ou se logger, avec quel identfiant (email) et mdp

        return $this->json(['message' => 'Manager créé avec succès'], 201);
    }

    /**
     * Assign a parent to a classe, the parent needs to exists
     * It's useful when a parent is already created and has already one classe
     */
    #[Route('/api/manager/classes/{id}/parents', name: 'api_users', methods: ['POST'])]
    public function assignParentToClass(ClasseRepository $classeRepository, int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em)
    {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();

        $classe = $classeRepository->find($id);

        if(!$classe) {
            return $this->json(['message' => "Cette classe n'existe pas"], 403);
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            if (!$user->getClassesManaged()->contains($classe)) {
                return $this->json(['message' => "Vous n'êtes pas autorisé à ajouter des parents à cette classe."], 403);
           }
        }

        $jsonData = json_decode($request->getContent(), true);
        if (empty($jsonData['parent'])) {
            return $this->json(['error' => 'Le champ "parent" est requis.'], 400);
        }

        $parent = $userRepository->find($jsonData['parent']);
        if (!$parent ) {
            return $this->json(['error' => "Ce parent n'existe pas."], 400);
        }

        if($parent->getClasses()->contains($classe)) {
            return $this->json(['error' => "Ce parent est déjà assigné à la classe."], 400);
        }

        $classe->addParent($parent);
        $em->persist($classe);
        $em->flush();
        
        return $this->json(['message' => "{$parent->getFirstname()} a bien été assigné à la classe {$classe->getName()}"], 201);
    }


    // // Affichage des utilisateurs
    // #[Route('/api/admin/users', name: 'api_admin_users', methods: ['GET'])]
    // #[Route('/api/manager/users', name: 'api_manager_users', methods: ['GET'])]
    // #[Route('/api/parent/users', name: 'api_parent_users', methods: ['GET'])]

    // public function index(UserRepository $userRepository): JsonResponse
    // {
    //     // Récupérer les données pour l'affichage des utilisateurs
    //     $users = $userRepository->findAll();
    //     return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    // }

    // //Afficher un utilisateur d'après son ID
    // #[Route('/api/admin/users/{id<\d+>}', name: 'api_admin_users_show', methods: ['GET'])]
    // public function show(int $id, UserRepository $userRepository): JsonResponse
    // {
    //     // Récupérer l'utilisateur par son ID
    //     $user = $userRepository->find($id);

    //     // Vérifier si l'utilisateur existe
    //     if (!$user) {
    //         return $this->json(['error' => 'Utilisateur inexistant.'], 404);
    //     }

    //     // Retourner les données de l'utilisateur au format JSON
    //     return $this->json($user, 200, [], ['groups' => 'get_user_item']);
    // }

    // //afficher une liste d'utilisateur par son rôle admin
    // #[Route('/api/listeAdmins', name: 'show_admin')]
    // public function showAdmin(UserRepository $userRepository): JsonResponse
    // {
    //     $usersWithRole = $userRepository->findByAdmin('ROLE_ADMIN');


    //     return $this->json($usersWithRole, 200, [], ['groups' => 'get_user_item']);
    // }

    // //afficher une liste d'utilisateur par son manager
    // #[Route('/api/listeManagers', name: 'show_manager')]
    // public function showManager(UserRepository $userRepository): JsonResponse
    // {
    //     $usersWithRole = $userRepository->findByManager('ROLE_MANAGER');


    //     return $this->json($usersWithRole, 200, [], ['groups' => 'get_user_item']);
    // }

    // //afficher une liste d'utilisateur par son rôle parent (donc user)
    // #[Route('/api/listeParents', name: 'show_parent')]
    // public function showParent(UserRepository $userRepository): JsonResponse
    // {
    //     $usersWithRole = $userRepository->findByParent('ROLE_USER');


    //     return $this->json($usersWithRole, 200, [], ['groups' => 'get_user_item']);
    // }

    // // Création d'un utilisateur
    // #[Route('/api/admin/users/new', name: 'api_admin_users_new', methods: ['POST'])]
    // public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    // {
    //     // Récupérer les données JSON de la requête
    //     $jsonData = json_decode($request->getContent(), true);

    //     // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
    //     $requiredFields = ['lastname', 'firstname', 'email', 'password', 'role'];
    //     foreach ($requiredFields as $field) {
    //         if (empty($jsonData[$field])) {
    //             return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
    //         }
    //     }

    //     $user = new User();
    //     $user->setFirstName($jsonData['firstname']);
    //     $user->setLastName($jsonData['lastname']);
    //     $user->setEmail($jsonData['email']);

    //     // Hachage du mot de passe
    //     $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
    //     $user->setPassword($hashedPassword);

    //     // Définition des rôles
    //     $user->setRoles([$jsonData['role']]);

    //     // Enregistrement de l'utilisateur
    //     $entityManager->persist($user);
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Utilisateur créé avec succès'], 201);
    // }

    // // Mise à jour d'un utilisateur // voir mettre le PATCH après le PUT ?
    // #[Route('/api/admin/users/{id}/edit', name: 'api_admin_users_update', methods: ['PUT'])]
    // public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    // {
    //     // Récupérer l'utilisateur à mettre à jour
    //     $user = $userRepository->find($id);

    //     // Vérifier si l'utilisateur existe
    //     if (!$user) {
    //         return $this->json(['error' => 'Utilisateur inexistant.'], 404);
    //     }

    //     // Récupérer les données JSON de la requête
    //     $jsonData = json_decode($request->getContent(), true);

    //     // Mettre à jour les champs de l'utilisateur
    //     if (isset($jsonData['firstname'])) {
    //         $user->setFirstName($jsonData['firstname']);
    //     }
    //     if (isset($jsonData['lastname'])) {
    //         $user->setLastName($jsonData['lastname']);
    //     }
    //     if (isset($jsonData['email'])) {
    //         $user->setEmail($jsonData['email']);
    //     }
    //     if (isset($jsonData['password'])) {
    //         // Hachage du nouveau mot de passe
    //         $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
    //         $user->setPassword($hashedPassword);
    //     }
    //     if (isset($jsonData['role'])) {
    //         // Définition des nouveaux rôles
    //         $user->setRoles([$jsonData['role']]);
    //     }

    //     // Enregistrement des modifications
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Utilisateur mis à jour avec succès'], 200);
    // }

    // //suppression d'un utilisateur
    // #[Route('/api/admin/users/{id}/delete', name: 'api_admin_users_delete', methods: ['DELETE'])]
    // public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     $user = $userRepository->find($id);

    //     // Vérifier si la photo existe
    //     if (!$user) {
    //         return $this->json(['error' => 'User inexistant.'], 404);
    //     }

    //     // Supprimer l'utilisateur
    //     $entityManager->remove($user);
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Utilisateur supprimée avec succès'], 200);
    // }
}
