<?php

namespace App\EventListener;

use App\Event\UserValidatedEvent;
use App\Service\StripeService;
use Doctrine\Persistence\ManagerRegistry;

class UserValidatedEventListener
{
  private StripeService $stripe;
  private ManagerRegistry $doctrine;

  function __construct(StripeService $stripe, ManagerRegistry $doctrine)
  {
    $this->stripe = $stripe;
    $this->doctrine = $doctrine;
  }

  public function onAppUserValidated(UserValidatedEvent $event): void
  {
    $user = $event->getUser();
    
    $customer = $this->stripe->getClient()->customers->create([
      'email' => $user->getEmail(),
      'name' => strtoupper($user->getLastname()).' '.ucfirst(strtolower($user->getFirstname()))
    ]);

    $user->setStripeCustomerId($customer->id);

    $this->doctrine->getManager()->persist($user);
    $this->doctrine->getManager()->flush();
  }
}