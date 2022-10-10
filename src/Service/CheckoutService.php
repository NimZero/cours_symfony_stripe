<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\ShippingRate;
use Doctrine\Persistence\ManagerRegistry;

class CheckoutService
{
  private ManagerRegistry $doctrine;
  private string $stripe_api_key;

  public function __construct(ManagerRegistry $doctrine, string $stripe_api_key)
  {
    $this->doctrine = $doctrine;
    $this->stripe_api_key = $stripe_api_key;
  }

  public function cartToOrder(Cart $cart): Order
  {
    $orderRepo = $this->doctrine->getRepository(Order::class);

    /** @var ?Order */
    $order = $orderRepo->findOneBy(['cart' => $cart]);

    if ($order === null) {
      $order = (new Order())
        ->setCart($cart)
        ->setUser($cart->getUser())
      ;

      $this->doctrine->getManager()->persist($order);
      $this->doctrine->getManager()->flush();
    }

    return $order;
  }

  public function getCheckout(Order $order): \Stripe\Checkout\Session
  {
    $shippings = $this->doctrine->getRepository(ShippingRate::class)->findBy(['active' => true]);

    $rates = [];
    foreach ($shippings as $shipping) {
      $rates[] = [
        'shipping_rate' => $shipping->getId(),
      ];
    }

    $items = [];
    foreach ($order->getCart()->getItems() as $item) {
      $items[] = [
        'price' => $item->getPrice()->getId(),
        'quantity' => $item->getQuantity(),
      ];
    }

    $stripe = new \Stripe\StripeClient($this->stripe_api_key);

    $session = $stripe->checkout->sessions->create([
      'client_reference_id' => json_encode(['order' => $order->getId()]),
      'customer' => $order->getUser()->getStripeCustomerId(),
      'success_url' => 'https://example.com/success',
      'cancel_url' => 'https://example.com/cancel',
      'line_items' => $items,
      'mode' => 'payment',
      'payment_method_types' => ['card'],
      'allow_promotion_codes' => true,
      'shipping_address_collection' => [
        'allowed_countries' => [
          'FR'
        ]
      ],
      'shipping_options' => $rates,
      'payment_intent_data' => [
        'receipt_email' => $order->getUser()->getEmail(),
      ],
    ]);

    return $session;
  }
}