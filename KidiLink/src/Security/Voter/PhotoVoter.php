<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PhotoVoter extends Voter
{
    public const COMMENT = 'photo.comment';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $actions = [self::COMMENT];

        return in_array($attribute, $actions) && $subject instanceof \App\Entity\Photo;
    }

    /**
     * @param Photo $photo
     */
    protected function voteOnAttribute(string $attribute, mixed $photo, TokenInterface $token): bool
    {
        /** @var App\Entity\User */
        $user = $token->getUser();
        
        $userIsAuthentified = ($user instanceof UserInterface);

        /**
         * On peut commenter une photo si :
         * - l'utilisateur est connecté
         * ET si :
         *  - la classe de l'album de la photo fait partie des classes dont l'utilisateur est parent
         * OU
         * - la classe de l'album de la photo fait partie des classes dont l'utilisateur est enseignant
         */

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::COMMENT:
                $classe = $photo->getAlbum()->getClasse();
                return (
                    $userIsAuthentified // l'utilisateur est connecté
                    && (
                        $user->getClasses()->contains($classe) // l'utilisateur est parent de la classe
                        ||
                        $user->getClassesManaged()->contains($classe) // l'utilisateur est enseignant de la classe
                        ||
                        in_array('ROLE_ADMIN', $user->getRoles()) // l'utilisateur est admin
                    )
                );
                break;
        }

        return false;
    }
}
