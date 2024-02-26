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

    #[Route('/api/albums/{id}/photos', name: 'api_photos', methods: ['GET'])]
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

        foreach($photos as $photo) {
            if (!strpos($photo->getUrl(), 'http', 0)) {
                // ajouter l'url du serveur avant le nom de l'image
                $photoUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . "/uploads/images/" . $photo->getUrl();
                $photo->setUrl($photoUrl);
            }
        }

        return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    }

    #[Route('/api/photos/{id}', name: 'api_photo_detail', methods: ['GET'])]
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
        if (!strpos($photo->getUrl(), 'http', 0)) {
            // ajouter l'url du serveur avant le nom de l'image
            $photoUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . "/uploads/images/" . $photo->getUrl();
            $photo->setUrl($photoUrl);
        }

        return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    }

    //création d'une photo
    #[Route('/api/manager/album/{id}/photos/new', name: 'api_photos_create', methods: ['POST'])]
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



    // //Afficher toutes les photos
    // /*
    // En tant qu'encadrant je veux ajouter un album et y ajouter des photos
    // En tant que parent, voir les albums d'une classe
    // En tant que parent je dois pouvoir voir les photos d'un album
    // */

    // /*
    // '/api/classes/2/albums'
    // => Vont être affichés tous les albums correspondant à cette classe
    // '/api/albums/2323'
    // => Vont être affichées toutes les photos correspondant à cet album
    // 'POST /api/classes/15/albums/new` 
    // => On peut ajouter un album
    // 'POST /api/albums/2323/photos/add`
    // => On peut ajouter une photo pour un album
    // */

    // #[Route('/api/admin/photos', name: 'api_admin_photos', methods: ['GET'])]
    // #[Route('/api/manager/photos', name: 'api_manager_photos', methods: ['GET'])]
    // #[Route('/api/parent/photos', name: 'api_parent_photos', methods: ['GET'])]
    // public function index(PhotoRepository $photoRepository): JsonResponse
    // {

    //     $photos = $photoRepository->findAll();
    //     $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $photos);
    //     return $this->json($photos, 200, [], ['groups' => 'get_photos_collection', 'get_photos_item']);
    // }

    // //Afficher une photo d'après son ID
    // #[Route('/api/admin/photos/{id<\d+>}', name: 'api_admin_photos_show', methods: ['GET'])]
    // #[Route('/api/manager/photos/{id<\d+>}', name: 'api_manager_photos_show', methods: ['GET'])]
    // #[Route('/api/parent/photos/{id<\d+>}', name: 'api_parent_photos_show', methods: ['GET'])]
    // public function show(int $id, PhotoRepository $photoRepository): JsonResponse
    // {
    //     // Récupérer la photo par son ID
    //     $photo = $photoRepository->find($id);
    //     $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $photo);

    //     // Vérifier si la photo existe
    //     if (!$photo) {
    //         return $this->json(['error' => 'Photo inexistante.'], 404);
    //     }

    //     // Retourner les données de la photo au format JSON
    //     return $this->json($photo, 200, [], ['groups' => 'get_photo_item']);
    // }

    // //création d'une photo
    // #[Route('/api/admin/photos/new', name: 'api_admin_photos_nouveau', methods: ['POST'])]
    // #[Route('/api/manager/photos/new', name: 'api_manager_photos_nouveau', methods: ['POST'])]
    // public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    // {

    //     $jsonData = json_decode($request->getContent(), true);
    //     // Valider les données (ex: vérifier si les champs requis sont présents)
    //     if (!isset($jsonData['url'], $jsonData['album'])) {
    //         return $this->json(['error' => 'Les champs  Url,  album  sont requis.'], 400);
    //     }
    //     // Validate URL
    //     if (!filter_var($jsonData['url'], FILTER_VALIDATE_URL)) {
    //         return $this->json(['error' => 'URL invalide.'], 400);
    //     }
    //     //Gérer les clés étrangères
    //     // Récupérer l'album par son ID
    //     $albumId = $jsonData['album'];
    //     $album = $entityManager->getRepository(Album::class)->find($albumId);
    //     $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $album);
    //     // Vérifier si l'album existe
    //     if (!$album) {
    //         return $this->json(['error' => 'L\'album spécifié n\'existe pas.'], 400);
    //     }
    //     $photo = new Photo();
    //     $photo->setTitle($jsonData['title']);
    //     $photo->setDescription($jsonData['description']);
    //     $photo->setUrl($jsonData['url']);
    //     $photo->setLikes($jsonData['likes']);
    //     $photo->setAlbum($album);

    //     // Ajouter le commentaire s'il est présent
    //     // if (isset($jsonData['comment'])) {

    //     //     $comment = new Comment();
    //     //     $comment->setContent($jsonData['comment']);
    //     // }

    //     $entityManager->persist($photo);
    //     $entityManager->flush();


    //     return $this->json(['message' => 'Photo créée avec succès'], 201);
    // }
    //  //on veut implémenter la logique de l'upload des photos.
    // #[Route('/images/photos', name: 'app_images_photos')]
    // public function new(Request $request, SluggerInterface $slugger): Response
    // {
    //     $product = new Photo();
    //     $form = $this->createForm(PhotoType::class, $product);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         /** @var UploadedFile $brochureFile */
    //         $brochureFile = $form->get('brochure')->getData();

    //         // this condition is needed because the 'brochure' field is not required
    //         // so the PDF file must be processed only when a file is uploaded
    //         if ($brochureFile) {
    //             $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
    //             // this is needed to safely include the file name as part of the URL
    //             $safeFilename = $slugger->slug($originalFilename);
    //             $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

    //             // Move the file to the directory where brochures are stored
    //             try {
    //                 $brochureFile->move(
    //                     $this->getParameter('brochures_directory'),
    //                     $newFilename
    //                 );
    //             } catch (FileException $e) {
    //                 // ... handle exception if something happens during file upload
    //             }

    //             // updates the 'brochureFilename' property to store the PDF file name
    //             // instead of its contents
    //             $product->setBrochureFilename($newFilename);
    //         }

    //         // ... persist the $product variable or any other work

    //         return $this->redirectToRoute('app_product_list');
    //     }

    //     return $this->render('product/new.html.twig', [
    //         'form' => $form,
    //     ]);
    // }


    // // Mise à jour d'une photo
    // #[Route('/api/admin/photos/{id}/edit', name: 'api_admin_photos_update', methods: ['PUT'])]
    // #[Route('/api/manager/photos/{id}/edit', name: 'api_manager_photos_update', methods: ['PUT'])]
    // public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     $photo = $entityManager->getRepository(Photo::class)->find($id);
    //     $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $photo);

    //     // Vérifier si la photo existe
    //     if (!$photo) {
    //         return $this->json(['error' => 'Photo non trouvée.'], 404);
    //     }

    //     $jsonData = json_decode($request->getContent(), true);
    //     // Mettre à jour les champs de la photo si présents dans les données JSON
    //     if (isset($jsonData['title'])) {
    //         $photo->setTitle($jsonData['title']);
    //     }
    //     if (isset($jsonData['description'])) {
    //         $photo->setDescription($jsonData['description']);
    //     }
    //     if (isset($jsonData['url'])) {
    //         $photo->setUrl($jsonData['url']);
    //     }
    //     if (isset($jsonData['likes'])) {
    //         $photo->setLikes($jsonData['likes']);
    //     }
    //     // Mettre à jour le commentaire s'il est présent dans les données JSON
    //     if (isset($jsonData['comment'])) {
    //         $comment = $photo->getComment();
    //         if (!$comment) {
    //             $comment = new Comment();
    //             $photo->setComment($comment);
    //         }
    //         $comment->setContent($jsonData['comment']);
    //     }

    //     $entityManager->flush();

    //     return $this->json(['message' => 'Photo mise à jour avec succès'], 200);
    // }

    // //suppression d'une photo
    // #[Route('/api/admin/photos/{id}/delete', name: 'api_admin_photos_delete', methods: ['DELETE'])]
    // #[Route('/api/manager/photos/{id}/delete', name: 'api_manager_photos_delete', methods: ['DELETE'])]
    // public function delete(int $id, PhotoRepository $photoRepository, EntityManagerInterface $entityManager): JsonResponse
    // {
    //     $photo = $photoRepository->find($id);
    //     $this->denyAccessUnlessGranted(PhotoVoter::COMMENT, $photo);

    //     // Vérifier si la photo existe
    //     if (!$photo) {
    //         return $this->json(['error' => 'Photo non trouvée.'], 404);
    //     }

    //     // Supprimer la photo
    //     $entityManager->remove($photo);
    //     $entityManager->flush();

    //     return $this->json(['message' => 'Photo supprimée avec succès'], 200);
    // }
}
