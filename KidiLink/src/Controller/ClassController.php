<?php

namespace App\Controller;

use App\Repository\ClasseRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class ClassController extends AbstractController
{

    #[Route('/api/classes', name: 'api_classes', methods: ['GET'])]
    public function index(ClasseRepository $classeRepository): JsonResponse
    {
        //Recup les données pour affichage des classes.
        $classes = $classeRepository->findAll();
        return $this->json($classes, 200, [], ['groups' => 'get_classes_collection', 'get_class_item']);
    }

    }



