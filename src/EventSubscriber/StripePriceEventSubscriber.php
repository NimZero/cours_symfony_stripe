<?php

namespace App\EventSubscriber;

use App\Entity\Price;
use App\Entity\Product;
use App\Event\StripeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripePriceEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'price.created' => [['processEvent', 0]],
            'price.updated' => [['processEvent', 0]],
            'price.deleted' => [['processEvent', 0]],
        ];
    }

    public function processEvent(StripeEvent $event)
    {
        /** @var \Stripe\Price */
        $eventPrice = $event->getObject();

        /** @var ?Price */
        $price = $this->doctrine->getRepository(Price::class)->find($eventPrice->id);

        if ($price === null) {
            $price = new Price($eventPrice->id);
        }

        /** @var ?Product */
        $product = $this->doctrine->getRepository(Product::class)->find($eventPrice->product);

        if ($product === null) {
            $product = new Product($eventPrice->product);
        }

        $price
            ->setActive($eventPrice->active)
            ->setCurrency($eventPrice->currency)
            ->setUnitAmount($eventPrice->unit_amount)
            ->setProduct($product)
        ;
        
        $this->doctrine->persist($product);
        $this->doctrine->persist($price);
        $this->doctrine->flush();
    }
}