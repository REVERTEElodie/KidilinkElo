<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AlbumVoter extends Voter
{
    public const VIEW = 'ALBUM_VIEW';

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

        if (!$user instanceof UserInterface) {
            return false;
        }

        $userClasses = $user->getClasses();

        // Vérifier si l'utilisateur appartient à au moins une classe de l'album
        foreach ($album->getClasse() as $albumClass) {
            if ($userClasses->contains($albumClass)) {
                return true;
            }
        }

        return false;
    }
}
