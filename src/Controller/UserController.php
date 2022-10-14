<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/my/profile', name: 'app_usr_profile')]
    public function index(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/my/profile/subscribe', name: 'app_usr_subscribe')]
    public function subscribe(Request $request, StripeService $stripe): Response
    {
        $sub = $request->query->get('tier');
        if ($sub !== null && in_array($sub, [1, 2])) {
            $sub = $this->getParameter('subscription.price')[$sub];
            $items[] = [
                'price' => $sub,
                'quantity' => 1,
            ];

            $session = $stripe->getClient()->checkout->sessions->create([
                'customer' => $this->getUser()->getStripeCustomerId(),
                'success_url' => 'https://example.com/success',
                'cancel_url' => 'https://example.com/cancel',
                'line_items' => $items,
                'mode' => 'subscription',
                'payment_method_types' => ['card'],
                'allow_promotion_codes' => true,
            ]);
            
            return $this->redirect($session->url);
        }

        return $this->render('user/subscribe.html.twig');
    }
}
