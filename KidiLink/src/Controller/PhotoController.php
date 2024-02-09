<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhotoController extends AbstractController
{

    #[Route('/api/photos', name: 'api_photos', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): JsonResponse
    {
        //Recup les données pour affichage des classes.
        $photos = $photoRepository->findAll();
        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }

    }



