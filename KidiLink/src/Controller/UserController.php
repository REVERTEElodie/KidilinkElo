<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\User;
use App\Repository\ClasseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    /**
     * List user for admin
     */
    #[Route('/api/admin/users', name: 'api_users', methods: ['GET'])]
    public function allUsers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

        /**
     * List admin for admin
     */
    #[Route('/api/admin/admins', name: 'api_admin', methods: ['GET'])]
    public function allAdmins(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllAdmins();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * List managers for admin
     */
    #[Route('/api/admin/managers', name: 'api_manager', methods: ['GET'])]
    public function allManagers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllManagers();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * List parents for admin
     */
    #[Route('/api/admin/parents', name: 'api_parents', methods: ['GET'])]
    public function allParents(UserRepository $userRepository): JsonResponse
    {
        // Récupérer les données pour l'affichage des utilisateurs
        $users = $userRepository->findAllParents();
        return $this->json($users, 200, [], ['groups' => 'get_users_collection', 'get_user_item']);
    }

    /**
     * Create a new manager for admin
     */
    #[Route('/api/admin/manager/new', name: 'api_managers_create', methods: ['POST'])]
    public function addManager(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($jsonData[$field])) {
                return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
            }
        }

        $alreadyExists = $userRepository->findBy(['email' => $jsonData['email']]);

        if ($alreadyExists) {
            return $this->json(['message' => "Le manager avec l'email {$jsonData['email']} existe déjà."], 403);
        }

        $user = new User();
        $user->setFirstName($jsonData['firstname']);
        $user->setLastName($jsonData['lastname']);
        $user->setEmail($jsonData['email']);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);

        // Définition des rôles
        $user->setRoles([$jsonData['role']]);

        // Enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Manager créé avec succès'], 201);
    }

    /**
     * Create a new parent for admin or manager
     * it needs classe to set the classe for the parent
     */
    #[Route('/api/manager/parent/new', name: 'api_parents_create', methods: ['POST'])]
    public function addParent(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $jsonData = json_decode($request->getContent(), true);

        // Valider les données (ex: vérifier si les champs requis sont présents et non vides)
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'classe'];
        foreach ($requiredFields as $field) {
            if (empty($jsonData[$field])) {
                return $this->json(['error' => 'Le champ ' . $field . ' est requis.'], 400);
            }
        }

        $alreadyExists = $userRepository->findBy(['email' => $jsonData['email']]);

        if ($alreadyExists) {
            return $this->json(['message' => "Le parent avec l'email {$jsonData['email']} existe déjà."], 403);
        }

        $classeId = $jsonData['classe'];
        $classe = $entityManager->getRepository(Classe::class)->find($classeId);

        if (!$classe) {
            return $this->json(['message' => "Cette classe n'existe pas"], 403);
        }


        $user = new User();
        $user->setFirstName($jsonData['firstname']);
        $user->setLastName($jsonData['lastname']);
        $user->setEmail($jsonData['email']);
        $user->addClass($classe);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $jsonData['password']);
        $user->setPassword($hashedPassword);

        // Définition des rôles
        $user->setRoles(['ROLE_PARENT']);

        // Enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();


        return $this->json(['message' => 'Manager créé avec succès'], 201);
    }

    /**
     * Assign a parent to a classe, the parent needs to exists
     * It's useful when a parent is already created and has already one classe
     */
    #[Route('/api/manager/classe/{id}/parent', name: 'api_classe_parent', methods: ['POST'])]
    public function assignParentToClass(ClasseRepository $classeRepository, int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em)
    {
        /** @var \App\Entity\user $user */
        $user = $this->getUser();

        $classe = $classeRepository->find($id);

        if(!$classe) {
            return $this->json(['message' => "Cette classe n'existe pas"], 403);
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            if (!$user->getClassesManaged()->contains($classe)) {
                return $this->json(['message' => "Vous n'êtes pas autorisé à ajouter des parents à cette classe."], 403);
           }
        }

        $jsonData = json_decode($request->getContent(), true);
        if (empty($jsonData['parent'])) {
            return $this->json(['error' => 'Le champ "parent" est requis.'], 400);
        }

        $parent = $userRepository->find($jsonData['parent']);
        if (!$parent ) {
            return $this->json(['error' => "Ce parent n'existe pas."], 400);
        }

        if($parent->getClasses()->contains($classe)) {
            return $this->json(['error' => "Ce parent est déjà assigné à la classe."], 400);
        }

        $classe->addParent($parent);
        $em->persist($classe);
        $em->flush();
        
        return $this->json(['message' => "{$parent->getFirstname()} a bien été assigné à la classe {$classe->getName()}"], 201);
    }
    
    #[Route('/api/users/{id}', name: 'api_classe_parent', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em) {
        // On récup le user à supprimer
        $userToDelete = $userRepository->find($id);

        if (!$userToDelete) {
            return $this->json(['error' => "Cet utilisateur n'existe pas."], 400);
        }
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $currentUserRoles = $currentUser->getRoles();

        // Si l'user qu'on veut supprimer est un admin, seul un autre admin peut le supprimer
        // MAIS aussi si c'est un rôle manager, seul un admin peut le supprimer
        if (in_array('ROLE_ADMIN', $userToDelete->getRoles()) || in_array('ROLE_MANAGER', $userToDelete->getRoles())) {
            // Donc, si le currentUser n'est pas admin, on renvoi une erreur
            if(!in_array('ROLE_ADMIN', $currentUser->getRoles())) {
                return $this->json(['error' => "Vous ne pouvez pas supprimer cet utilisateur. (vous n'êtes pas admin)"], 403);
            }
        // Si c'est un parent
        } else {
            if(in_array('ROLE_ADMIN', $currentUser->getRoles())) {
               // On fait rien, on laisse passer
               
               // Puis si l'utilisateur courant est un manager on doit vérifier si il est le manager de la classe de l'user qu'il veut supprimer.
            } elseif(in_array('ROLE_MANAGER', $currentUser->getRoles())) {
                // On récupère les classes gérées par le manager
                $currentManagerClasses = $currentUser->getClassesManaged();

                // On récupère les classes du parent
                $parentToDeleteClasses = $userToDelete->getClasses();

                // On parcours chaque classe du user à supprimer et on vérifie si la classe est contenue dans les classesManaged du manager
                // on prépare une variable pour mettre true si y a un matching de classe (=le parent est dans une classe gérée par le currentUser connecté (manager))
                $match = false;
                foreach($parentToDeleteClasses as $parentClasse) {
                    if($currentManagerClasses->contains($parentClasse)) {
                        $match = true;
                        // pas la peine de parcourir les autres classes, y a déjà un match, donc il a le droit de supprimer le user
                        break;
                    }
                }

                // Si y a pas de match, il a donc pas le droit de le supprimer
                if(!$match) {
                    return $this->json(['error' => "Vous ne pouvez pas supprimer cet utilisateur. (vous êtes manager et ne gérez pas la classe du parent à supprimer)"], 403);
                }
            } else {
                return $this->json(['error' => "Vous ne pouvez pas supprimer cet utilisateur. (vous êtes seulement un parent)"], 403);
            }
        }

        // Récupérer le premier admin qu'on trouve 
        // Lui assigner toutes les classes du manager 

        // Si l'utilisateur qu'on veut supprimer est un manager, il faut parcourir toutes les classes qu'il managed pour les assigner au premier admin qu'on trouve
        if(in_array('ROLE_MANAGER', $userToDelete->getRoles())) {
            $admin = $userRepository->findOneAdmin();

            if(!$admin) {
                return $this->json(['error' => "Impossible de supprimer ce manager, aucun admin trouvé en bdd, contactez les développeurs d'urgence."], 403);
            }

            $userToDeleteClassesManaged = $userToDelete->getClassesManaged();

            // On redéfini le manager des classes comme étant l'admin pour pas péter la bdd
            foreach($userToDeleteClassesManaged as $classe) {
                $classe->setManager($admin);
            }
            $em->flush();
        }


        // On a pas été dégagé, on peut donc remove
        $em->remove($userToDelete);
        $em->flush();

        return $this->json(['message' => "{$userToDelete->getFirstname()} a bien été supprimé."], 201); 
    }

}
