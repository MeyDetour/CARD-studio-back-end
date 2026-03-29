<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Note;
use App\Service\TypeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class NoteController extends AbstractController
{
    #[Route('/note', name: 'app_note')]
    public function index(): Response
    {
        return $this->render('note/index.html.twig', [
            'controller_name' => 'NoteController',
        ]);
    }
    
    #[Route('/new/note/game/{id}', name: 'new_note')]
    public function newNote(Game $game, SerializerInterface $serializer, TypeService $typeService , EntityManagerInterface $manager , Request $request): Response
    {
        $note = $serializer->deserialize($request->getContent(), Note::class, 'json');
     
        if (!$typeService->verify($note->getDescription(), "string")) {
            return $this->json(["message" => "Invalid description. (field : description, accepted : string)"], 406);
        }
        if (!$typeService->verify($note->getRate(), "number")) {
            return $this->json(["message" => "Invalid rate. (field : rate, accepted : number)"], 406);
        }

        $note->setDate(new \DateTime());
       $game->addNote($note);

        $manager->persist($note);
        $manager->persist($game);
        $manager->flush();
        return $this->json(["message"=>"ok"],200);
    }
}
