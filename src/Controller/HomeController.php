<?php

namespace App\Controller;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ContactType;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, ManagerRegistry $doctrine): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){

                $email = (new TemplatedEmail())
                    ->to('contact@example.com')
                    ->replyTo($form->get('email')->getData())
                    ->subject($form->get('sujet')->getData())
                    ->htmlTemplate('emails/contact.html.twig')
                    ->context([
                        'nom'=> $form->get('nom')->getData(),
                        'sujet'=> $form->get('sujet')->getData(),
                        'message'=> $form->get('message')->getData()
                    ]);
              
                try {
                    $mailer->send($email);
                    $this->addFlash('success','Message envoyé');

                    $contact->setDate(new \DateTimeImmutable());
                    $doctrine->getManager()->persist($contact);
                    $doctrine->getManager()->flush();

                    return $this->redirectToRoute('app_contact');
                } catch (TransportException $e) {
                    $this->addFlash('warning','une erreur est survenue veiller réessayer');
                }
            }
        }

        return $this->renderForm('home/contact.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/mentions-legales', name: 'app_mentions')]
    public function mentions(): Response
    {
        return $this->render('home/mentions.html.twig');
    }

    #[Route('/conditions-generales-vente', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('home/cgv.html.twig');
    }
}
