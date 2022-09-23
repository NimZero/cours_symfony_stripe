<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/contacts', name: 'app_adm_contacts')]
    public function adm_contacts(ManagerRegistry $doctrine): Response
    {
        $contacts = $doctrine->getRepository(Contact::class)->findAll();

        return $this->render('admin/contacts.html.twig', [
            'contacts' => $contacts
        ]);
    }

    #[Route('/users', name: 'app_adm_users')]
    public function adm_users(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }
}
