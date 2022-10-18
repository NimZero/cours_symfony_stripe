<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Service\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/my/profile', name: 'app_usr_profile')]
    public function userProfile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/my/cart', name: 'app_usr_cart')]
    public function userCart(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        /** @var User */
        $user = $this->getUser();
        /** @var Cart */
        $cart = $user->getCarts()->last();

        $rem = $request->query->get('remove');
        if ($rem) {
            foreach ($cart->getItems() as $item) {
                if ($item->getId() == $rem) {
                    $rem = $item;
                    break;
                }
            }

            if (is_a($item, CartItem::class)) {
                $cart->removeItem($item);
                $entityManagerInterface->remove($item);
                $entityManagerInterface->flush();

                return $this->redirectToRoute('app_usr_cart');
            }
        }

        return $this->render('user/cart.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/my/cart/pay', name: 'app_usr_cart_pay')]
    public function userCartPay(CheckoutService $check): Response
    {
        /** @var User */
        $user = $this->getUser();
        /** @var Cart */
        $cart = $user->getCarts()->last();

        $order = $check->cartToOrder($cart);
        $checkout = $check->getCheckout($order);

        return $this->redirect($checkout->url);
    }
}
