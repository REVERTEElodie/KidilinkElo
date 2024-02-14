<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: '/api/login', name: 'app_login', methods:['POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Vérification si les données sont présentes
        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Veuillez fournir un email et un mot de passe'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Récupération de l'utilisateur depuis la base de données
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Email ou mot de passe incorrect'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Connexion réussie, retourner les informations de l'utilisateur
        return new JsonResponse(['username' => $user->getUsername()], JsonResponse::HTTP_OK);
    }

    #[Route(path: '/api/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $session->invalidate();

        //TODO Il faut renseigner la route de redirection valide, à voir avec le FRONT
        return $this->redirectToRoute('/admin/users');
    }
<<<<<<< HEAD
}
=======
    
}
>>>>>>> 9868947c2da562bf577251fb34db571f231b5816
