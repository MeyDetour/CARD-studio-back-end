<?php

namespace App\Controller;

use App\Service\TypeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('api/me', name: 'app_user', methods: ['GET'])]
    public function getCurrentUser(): Response
    {
     return $this->json($this->getUser(),200,[],['groups'=>"profile"] );
    }
       #[Route('api/edit/me', name: 'editProfile', methods: ['PUT'])]
    public function changeErrorVisibility(Request $request, EntityManagerInterface $entityManager , TypeService $typeService): Response
    {    
        $data = json_decode($request->getContent(), true);
        if (!isset($data["lang"])
             || !$typeService->verify($data["lang"], "string")) {
            return $this->json(["message" => "Key Value must be defined. (field :lang, value : string)"], 406);
        };if (!isset($data["displayErrors"])
             || !$typeService->verify($data["displayErrors"], "bool")) {
            return $this->json(["message" => "Key Value must be defined. (field :displayErrors, value : bool)"], 406);
        };
        
        $this->getUser()->setLang($data["lang"]);
        $this->getUser()->setDisplayErrors($data["displayErrors"]);
        $entityManager->persist($this->getUser());
        $entityManager->flush();
        
        return $this->json(["message"=>"ok"],200, [],['groups'=>"game"] );
    }
}
