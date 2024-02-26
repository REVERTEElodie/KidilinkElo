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
     * List admin for admin
     */
    #[Route('/api/admin/admins', name: 'api_admin', methods: ['GET'])]
    public function allAdmins(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllAdmins();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * List managers for admin
     */
    #[Route('/api/admin/managers', name: 'api_manager', methods: ['GET'])]
    public function allManagers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllManagers();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * List parents for admin
     */
    #[Route('/api/admin/parents', name: 'api_parents', methods: ['GET'])]
    public function allParents(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllParents();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * Create a new manager for admin
     */
    #[Route('/api/admin/manager/new', name: 'api_managers_create', methods: ['POST'])]
    public function addManager(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'role'];
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
        $user->setRoles([$jsonData['role']]);

        // Enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

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


}
