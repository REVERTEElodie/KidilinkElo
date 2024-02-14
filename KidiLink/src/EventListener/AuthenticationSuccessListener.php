<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
  /**
   * @param AuthenticationSuccessEvent $event
   */
  public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
  {
    $data = $event->getData();
    $user = $event->getUser();

    if (!$user instanceof User) {
      return;
    }

    $data['data'] = array(
      'roles' => $user->getRoles(),
      'firstname' => $user->getFirstName(),
      'lastname' => $user->getLastName(),
    );

    $event->setData($data);
  }
}
