<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\StripeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriptionEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'customer.subscription.created' => [['processEvent', 0]],
            'customer.subscription.updated' => [['processEvent', 0]],
            'customer.subscription.deleted' => [['processEvent', 0]],
        ];
    }

    public function processEvent(StripeEvent $event)
    {
        /** @var \Stripe\Subscription */
        $subscription = $event->getObject();

        /** @var ?User */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['stripeCustomerId' => $subscription->customer]);

        if ($user === null) {
            $event->setMessage('No User');
            return;
        }

        $user->setSubscriptionStatus($subscription->status);
        $user->setSubscriptionProduct($subscription->items->first()->price->product);
        
        $this->doctrine->persist($user);
        $this->doctrine->flush();
    }
}