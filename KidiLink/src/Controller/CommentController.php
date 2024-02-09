<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{

    #[Route('/api/comments', name: 'api_comments', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        //Recup les données pour affichage des Comments.
        $comments = $commentRepository->findAll();
        return $this->json($comments, 200, [], ['groups' => 'get_comments_collection', 'get_comment_item']);
    }

    }



