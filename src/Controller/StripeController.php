<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe/endpoint', name: 'app_stripe_endpoint', methods: ['POST'])]
    public function stripe_endpoint(Request $request): Response
    {
        $secret = $this->getParameter('stripe_webhook_secret');
        $header = $request->headers->get('HTTP_STRIPE_SIGNATURE');
        $payload = $request->getContent();

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $header, $secret
            );
        } 
        catch (\UnexpectedValueException $e) {
            // Payload invalide
            return new Response('invalid payload', Response::HTTP_BAD_REQUEST);
        }
        catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            return new Response('invalid signature', Response::HTTP_BAD_REQUEST);
        }

        switch ($event->type) {
            case '':
                # code...
                break;
            
            default:
                // Evenement non traité
                return new Response('unkown event', Response::HTTP_ACCEPTED);
                break;
        }

        return new Response();
    }

    #[Route('/stripe/portal', name: 'app_stripe_portal')]
    public function customerPortal(StripeService $stripe, UrlGeneratorInterface $urlGenerator): Response
    {
        /** @var User */
        $user = $this->getUser();

        if ($user === null) {
            return $this->redirectToRoute('app_usr_profile');
        }

        if ($user->getStripeCustomerId() === null) {
            $this->addFlash('danger', 'You are not a Stripe customer.');
            return $this->redirectToRoute('app_usr_profile');
        }

        $portal = $stripe->getClient()->billingPortal->sessions->create([
            'customer' => $user->getStripeCustomerId(),
            'return_url' => $urlGenerator->generate('app_usr_profile', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        return $this->redirect($portal->url);
    }
}
