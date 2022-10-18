<?php

namespace App\EventSubscriber;

use App\Event\UserValidatedEvent;
use App\Service\StripeService;
use Doctrine\Persistence\ManagerRegistry;

class UserValidatedEventSubscriber
{
  private StripeService $stripe;
  private ManagerRegistry $doctrine;

  function __construct(StripeService $stripe, ManagerRegistry $doctrine)
  {
    $this->stripe = $stripe;
    $this->doctrine = $doctrine;
  }

  public static function getSubscribedEvents(): array
    {
        return [
          UserValidatedEvent::NAME => [['onAppUserValidated', 0]],
        ];
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