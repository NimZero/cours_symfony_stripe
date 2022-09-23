<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/my/profile', name: 'app_usr_profile')]
    public function index(): Response
    {
        return $this->render('user/profile.html.twig');
    }
}
