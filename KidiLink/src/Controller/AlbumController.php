<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class AlbumController extends AbstractController
{

    #[Route('/api/albums', name: 'api_albums', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): JsonResponse
    {
        //Recup les données pour affichage des albums.
        $albums = $albumRepository->findAll();
        return $this->json($albums, 200, [], ['groups' => 'get_albums_collection', 'get_album_item']);
    }

    

    }



