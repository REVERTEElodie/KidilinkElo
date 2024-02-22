<?php

namespace App\Controller;

use App\Entity\User;
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
    // Affichage des utilisateurs
    #[Route('/api/admin/users', name: 'api_admin_users', methods: ['GET'])]
    #[Route('/api/manager/users', name: 'api_manager_users', methods: ['GET'])]
    #[Route('/api/parent/users', name: 'api_parent_users', methods: ['GET'])]

    public function index(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    //Afficher un utilisateur d'après son ID
    #[Route('/api/admin/users/{id<\d+>}', name: 'api_admin_users_show', methods: ['GET'])]
public function show(int $id, UserRepository $userRepository): JsonResponse
{
    // Récupérer l'utilisateur par son ID
    $user = $userRepository->find($id);

    // Vérifier si l'utilisateur existe
    if (!$user) {
        return $this->json(['error' => 'Utilisateur inexistant.'], 404);
    }

    // Retourner les données de l'utilisateur au format JSON
    return $this->json($user, 200, [], ['groups' => 'get_user_item']);
}

    //afficher une liste d'utilisateur par son rôle
    #[Route('/api/admin', name: 'show_admin')]
    public function showAdmin(UserRepository $userRepository): JsonResponse
    {
        $usersWithRole = $userRepository->findByRoles('ROLE_MANAGER');


        return $this->json($usersWithRole, 200, [], ['groups' => 'get_user_item']);
    }


    
    // Création d'un utilisateur
    #[Route('/api/admin/users/new', name: 'api_admin_users_new', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse 
    {
        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($jsonData[$field])) {
                return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
            }
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
        
        return $this->json(['message' => 'Utilisateur créé avec succès'], 201);
    }

    // Mise à jour d'un utilisateur // voir mettre le PATCH après le PUT ?
#[Route('/api/admin/users/{id}/edit', name: 'api_admin_users_update', methods: ['PUT'])]
public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
{
    // Récupérer l'utilisateur à mettre à jour
    $user = $userRepository->find($id);

    // Vérifier si l'utilisateur existe
    if (!$user) {
        return $this->json(['error' => 'Utilisateur inexistant.'], 404);
    }

    // Récupérer les données JSON de la requête
    $jsonData = json_decode($request->getContent(), true);

    // Mettre à jour les champs de l'utilisateur
    if (isset($jsonData['firstname'])) {
        $user->setFirstName($jsonData['firstname']);
    }
    if (isset($jsonData['lastname'])) {
        $user->setLastName($jsonData['lastname']);
    }
    if (isset($jsonData['email'])) {
        $user->setEmail($jsonData['email']);
    }
    if (isset($jsonData['password'])) {
        // Hachage du nouveau mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);
    }
    if (isset($jsonData['role'])) {
        // Définition des nouveaux rôles
        $user->setRoles([$jsonData['role']]);
    }

    // Enregistrement des modifications
    $entityManager->flush();

    return $this->json(['message' => 'Utilisateur mis à jour avec succès'], 200);
}

    //suppression d'un utilisateur
    #[Route('/api/admin/users/{id}/delete', name: 'api_admin_users_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);

        // Vérifier si la photo existe
        if (!$user) {
            return $this->json(['error' => 'User inexistant.'], 404);
        }

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur supprimée avec succès'], 200);
    }


}

