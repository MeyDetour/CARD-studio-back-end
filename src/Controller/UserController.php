<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('api/me', name: 'app_user')]
    public function getCurrentUser(): Response
    {
     return $this->json($this->getUser());
    }
}
