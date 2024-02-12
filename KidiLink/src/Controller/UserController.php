<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        //Recup les données pour affichage des users.
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    
    }



