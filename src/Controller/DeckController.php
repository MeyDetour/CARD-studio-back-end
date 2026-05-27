<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Deck;

final class DeckController extends AbstractController
{
      #[Route('/api/new/deck', name: 'new_game')]
    public function newDeck( DeckRepository $deckRepository,  EntityManagerInterface $manager): Response
    {   
        $deck = new Deck();
        $deck->setName("Default name");
        $deck->setAuthorName("Unknown");
        $deck->setIsPublished(false);
        $deck->setOwner($this->getUser());
        $deck->setCards([]);
        $deck->setParams([]);
        $manager->persist($deck);
        $manager->flush();
        return $this->render('deck/index.html.twig', [
            'controller_name' => 'DeckController',
        ]);
    }
}
