<?php

namespace App\EventSubscriber;

use App\Entity\Product;
use App\Event\StripeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeProductEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'product.created' => [['processEvent', 0]],
            'product.updated' => [['processEvent', 0]],
            'product.deleted' => [['processEvent', 0]],
        ];
    }

    public function processEvent(StripeEvent $event)
    {
        /** @var \Stripe\Product */
        $eventProduct = $event->getObject();

        /** @var ?Product */
        $product = $this->doctrine->getRepository(Product::class)->find($eventProduct->id);

        if ($product === null) {
            $product = new Product($eventProduct->id);
        }

        $product
            ->setName($eventProduct->name)
            ->setDescription($eventProduct->description)
            ->setActive($eventProduct->active)
        ;
        
        $this->doctrine->persist($product);
        $this->doctrine->flush();
    }
}