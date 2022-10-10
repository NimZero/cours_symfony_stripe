<?php

namespace App\EventSubscriber;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\ShippingRate;
use App\Event\StripeEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeCheckoutEventSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'checkout.session.completed' => [['processEvent', 0]],
            // 'checkout.session.expired' => [['processException', 0]],
        ];
    }

    public function processEvent(StripeEvent $event)
    {
        /** @var \Stripe\Checkout\Session */
        $session = $event->getObject();

        $orderId = json_decode($session->client_reference_id, true)['order'];
        /** @var ?Order */
        $order = $this->doctrine->getRepository(Order::class)->find($orderId);

        
        $shipOptionId = $session->shipping_cost->shipping_rate;
        /** @var ?ShippingRate */
        $shipOption = $this->doctrine->getRepository(ShippingRate::class)->find($shipOptionId);

        if ($session->payment_status !== 'paid') {
            $event->setMessage('Session not paid');
            return;
        }

        if ($order === null) {
            $event->setMessage('Order not found');
            return;
        }

        if ($shipOption === null) {
            $event->setMessage('ShippingRate not found');
            return;
        }

        $order
            ->setPaid(true)
            ->setShippingDetails($session->shipping_details->toArray())
            ->setShippingOption($shipOption)
        ;

        $cart = new Cart;
        $cart->setUser($order->getUser());

        $this->doctrine->getManager()->persist($cart);
        $this->doctrine->getManager()->flush();
    }
}