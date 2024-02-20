<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClasseVoter extends Voter
{
    public const CLASSE = "classe";

    protected function supports(string $attribute, mixed $subject): bool
    {
        $actions = [self::CLASSE];

        return in_array($attribute, $actions) && $subject instanceof \App\Entity\Classe;
    }

    protected function voteOnAttribute(string $attribute, mixed $classe, TokenInterface $token): bool
    {   
        /** @var User */
        $user = $token->getUser();
        $userIsAuthentified = ($user instanceof UserInterface);
        
        // if the user is anonymous, do not grant access
        if ($user instanceof UserInterface) {
            /**
             * On peut voir une classe si :
             * - on est connecté
             * ET si :
             * la classe fait partie des classes dont l'utilisateur a accès.
             */
            switch ($attribute) {
                case self::CLASSE:
                  
                    $userClasses = $user->getClasses();
                    $userClassesManaged = $user->getClassesManaged();

                    // Vérifier si la classe fait partie des classes auxquelles l'utilisateur a accès
                    return (
                        $userIsAuthentified && (
                            $userClasses->contains($classe) || // l'utilisateur est parent de la classe
                            $userClassesManaged->contains($classe) || // l'utilisateur est enseignant de la classe
                            in_array('ROLE_ADMIN', $user->getRoles()) // l'utilisateur est admin
                        )
                    );
            }
        }

        return false;
    }
}
