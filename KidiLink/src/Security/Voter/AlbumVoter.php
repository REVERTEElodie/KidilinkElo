<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AlbumVoter extends Voter
{
    public const VIEW = 'album';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof \App\Entity\Album;
    }
    
    /**
     * @param Album $album
     */
    protected function voteOnAttribute(string $attribute, mixed $album, TokenInterface $token): bool
    {   
        /** @var User */
        $user = $token->getUser();
        $roles = $user->getRoles();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                $albumClasse = $album->getClasse();

                if(in_array('ROLE_ADMIN', $roles)) {
                    return true;
                } elseif(in_array('ROLE_MANAGER', $roles)) {
                    $managerClasses = $user->getClassesManaged();
                    return $managerClasses->contains($albumClasse);
                } else {
                    $userClasses = $user->getClasses();
                    return $userClasses->contains($albumClasse);
                }
            break;
        }

        return false;
    }
}
