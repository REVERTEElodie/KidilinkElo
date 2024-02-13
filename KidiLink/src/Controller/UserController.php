<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    
    //affichage des users
    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        //Recup les données pour affichage des users.
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }
    
    
    //création d'un user
    #[Route('/api/users/nouveau', name: 'api_users_nouveau', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse 
    {
            // Récupérer les données JSON de la requête
    $jsonData = json_decode($request->getContent(), true);

    // Valider les données (ex: vérifier si les champs requis sont présents)
    if (!isset($jsonData['lastname'], $jsonData['firstname'], $jsonData['email'], $jsonData['password'], $jsonData['role'])) {
        return $this->json(['error' => 'Les champs Nom, Prenom, email et mot de passe sont requis.'], 400);
    }
        $user = new User();
        $user->setFirstName($jsonData['firstname']);
        $user->setLastName($jsonData['lastname']);
        $user->setEmail($jsonData['email']);
        $user->setPassword($jsonData['password']);
        $user->setRoles($jsonData['role']);

        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();
        
        return $this->json(['message' => 'Utilisateur créé avec succès'], 201);
    }
    }


    // public function buildForm(FormBuilderInterface $builder, array $options): void
    // {
    //     $builder
    //         ->add('email', EmailType::class, [
    //             'label' => 'Courriel',
    //             'empty_data'    => '',
    //         ])
    //         ->add('roles', ChoiceType::class, [
    //             'multiple'      => false,
    //             'expanded'      => true,
    //             'choices'       => [
    //                 'administrateur'    => 'ROLE_ADMIN',
    //                 'manager'           => 'ROLE_MANAGER',
    //                 'utilisateur'       => 'ROLE_USER',
    //             ],
    //             'empty_data'    => '',
    //             'label_attr'    => [
    //                 'class'     => 'checkbox-inline',
    //             ],

