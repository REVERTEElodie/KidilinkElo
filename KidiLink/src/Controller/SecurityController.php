<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/api/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
    
        $data = [
            'last_username' => $lastUsername,
            'error' => $error ? $error->getMessage() : null
        ];


        return new JsonResponse($data);
    }

    #[Route(path: '/api/logout', name: 'api_logout')]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $session->invalidate();

        // TODO renseigner la route de rediction valide, à voir avec le front 
        return $this->redirectToRoute('api_albums');
        // throw new \LogicException('Veuillez entrez un utilisateur ou mot de passe valide');
    }
    
}
