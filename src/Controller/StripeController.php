<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\StripeEvent;
use App\Service\StripeService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe/endpoint', name: 'app_stripe_endpoint', methods: ['POST'])]
    public function stripe_endpoint(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        /**
         * The signature from the headers
         * @var string
         */
        $signature_header = $request->headers->get('STRIPE_SIGNATURE');

        /**
         * The payload of the request
         * @var string
         */
        $payload = $request->getContent();

        /**
         * The webhook signing secret
         * @var string
         * */ 
        $secret = $this->getParameter('stripe_webhook_secret');

        try {
            // Use the Stripe lib to validate and build the event from the request
            $event = \Stripe\Webhook::constructEvent(
                $payload, $signature_header, $secret
            );

        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return new Response('Unexpected Value', Response::HTTP_BAD_REQUEST);

        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new Response('Signature Verification Failed', Response::HTTP_BAD_REQUEST);
        }

        // Create the event to be dispatched
        $stripeEvent = new StripeEvent($event);

        /**
         * The event after processing
         * @var StripeEvent
         */
        $return = $eventDispatcher->dispatch($stripeEvent, $event->type);

        if ($return->isFailed()) {
            // The event processing has been marked as failed durring processing, respond with HTTP 500 error and given message
            return new Response($return->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // The event has been processed respond with HTTP 200 and given message
        return new Response($return->getMessage(), Response::HTTP_OK);
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
