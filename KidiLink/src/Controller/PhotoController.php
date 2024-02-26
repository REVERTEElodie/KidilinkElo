<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Entity\Comment;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use App\Security\Voter\PhotoVoter;
use App\Repository\PhotoRepository;
use App\Security\Voter\AlbumVoter;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhotoController extends AbstractController
{
    //Afficher les photos d'un album
    #[Route('/api/album/{id}/photos', name: 'api_photos', methods: ['GET'])]
    public function photos(int $id, AlbumRepository $albumRepository, Request $request): JsonResponse
    {
        /** @var User */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $album = $albumRepository->find($id);

        if (!$album) {
            return $this->json(['error' => "Cet album n'existe pas"], 404);
        }

        $albumClasse = $album->getClasse();

        if (in_array('ROLE_ADMIN', $roles)) {
        } elseif (in_array('ROLE_MANAGER', $roles)) {
            $managerClasses = $user->getClassesManaged();
            if (!$managerClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        } else {
            $userClasses = $user->getClasses();
            if (!$userClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        }

        $photos = $album->getPhotos();

        foreach ($photos as $photo) {
            if (strpos($photo->getUrl(), 'http') !== 0) {
                // ajouter l'url du serveur avant le nom de l'image
                $photoUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . "/uploads/images/" . $photo->getUrl();
                $photo->setUrl($photoUrl);
            }
        }

        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }
    //Afficher une photo par son ID.
    #[Route('/api/photo/{id}', name: 'api_photo_detail', methods: ['GET'])]
    public function photo(int $id, PhotoRepository $photoRepository, Request $request): JsonResponse
    {
        /** @var User */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $photo = $photoRepository->find($id);

        if (!$photo) {
            return $this->json(['error' => "Cet photo n'existe pas"], 404);
        }

        $photoAlbum = $photo->getAlbum();
        $albumClasse = $photoAlbum->getClasse();

        if (in_array('ROLE_ADMIN', $roles)) {
        } elseif (in_array('ROLE_MANAGER', $roles)) {
            $managerClasses = $user->getClassesManaged();
            if (!$managerClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        } else {
            $userClasses = $user->getClasses();
            if (!$userClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        }
        if (strpos($photo->getUrl(), 'http') !== 0) {
            // ajouter l'url du serveur avant le nom de l'image
            $photoUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . "/uploads/images/" . $photo->getUrl();
            $photo->setUrl($photoUrl);
        }

        return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    }

    //création d'une photo
    #[Route('/api/photo/new', name: 'api_photo_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, AlbumRepository $albumRepository, int $id, ImageHandler $imageHandler): JsonResponse
    {
        /** @var User */
        $user = $this->getUser();
        $roles = $user->getRoles();

        $jsonData = json_decode($request->getContent(), true);
        // Valider les données (ex: vérifier si les champs requis sont présents)
        if (!isset($jsonData['image'])) {
            return $this->json(['error' => 'Le champ image est obligatoire'], 400);
        }
        // Validate URL
        // Récupérer l'album par son ID
        $album = $entityManager->getRepository(Album::class)->find($id);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'L\'album spécifié n\'existe pas.'], 400);
        }

        $albumClasse = $album->getClasse();
        if (in_array('ROLE_ADMIN', $roles)) {
        } elseif (in_array('ROLE_MANAGER', $roles)) {
            $managerClasses = $user->getClassesManaged();
            if (!$managerClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        }


        $photo = new Photo();
        $photo->setTitle($jsonData['title']);
        $photo->setDescription($jsonData['description']);
        // Traitement de l'image
        $imageBase64 = $jsonData['image'];

        // TRAITER l'IMAGE
        $imageName = $imageHandler->processUploadBase64($imageBase64, 'photo');

        $photo->setUrl($imageName);
        // Fin traitement

        $photo->setLikes(0);
        $photo->setAlbum($album);

        // Ajouter le commentaire s'il est présent
        // if (isset($jsonData['comment'])) {

        //     $comment = new Comment();
        //     $comment->setContent($jsonData['comment']);
        // }

        $entityManager->persist($photo);
        $entityManager->flush();


        return $this->json(['message' => 'Photo créée avec succès'], 201);
    }

    // Mise à jour d'une photo
    #[Route('/api/photo/{id}/edit', name: 'api_photo_update', methods: ['PUT'])]
    public function update(int $id, Request $request, PhotoRepository $photoRepository, EntityManagerInterface $entityManager): JsonResponse
    {   
                /** @var User */
                $user = $this->getUser();
                $roles = $user->getRoles();

        // Récupérer la photo à mettre à jour
        $photo = $photoRepository->find($id);
        // $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $comment);

        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo inexistante.'], 404);
        }

        // Récupérer les données JSON de la requête
        $jsonData = json_decode($request->getContent(), true);
        
                // Validate URL
        // Récupérer l'album par son ID
        $album = $entityManager->getRepository(Album::class)->find($id);
        // Vérifier si l'album existe
        if (!$album) {
            return $this->json(['error' => 'L\'album spécifié n\'existe pas.'], 400);
        }
        $albumClasse = $album->getClasse();
        if (in_array('ROLE_ADMIN', $roles)) {
        } elseif (in_array('ROLE_MANAGER', $roles)) {
            $managerClasses = $user->getClassesManaged();
            if (!$managerClasses->contains($albumClasse)) {
                return $this->json(['error' => "Vous n'avez pas le droit d'accéder aux photos de cette classe"], 403);
            }
        }

        // Mettre à jour la photo
        if (isset($jsonData['title'])) {
            $photo->setTitle($jsonData['title']);
        }
        if (isset($jsonData['description'])) {
            $photo->setDescription($jsonData['description']);
        }
         if (isset($jsonData['url'])) {
            $photo->setUrl($jsonData['url']);
        }
        if (isset($jsonData['likes'])) {
            $photo->setLikes($jsonData['likes']);
        }

        // Enregistrer les modifications
        $entityManager->flush();

        return $this->json(['message' => 'Photo mise à jour avec succès'], 200);
    }

    //suppression d'une photo
    #[Route('/api/photo/{id}/delete', name: 'api_photo_delete', methods: ['DELETE'])]
    public function delete(int $id, PhotoRepository $photoRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $photo = $photoRepository->find($id);
        // Vérifier si la photo existe
        if (!$photo) {
            return $this->json(['error' => 'Photo non trouvée.'], 404);
        }
        // Supprimer la photo
        $entityManager->remove($photo);
        $entityManager->flush();
        return $this->json(['message' => 'Photo supprimée avec succès'], 200);
    }

    
}
