<?php

namespace App\Service;

use App\Entity\Deck;

class DeckObjectService
{
    public function __construct( )
    {

    }
    public function getDeckObject(Deck $deck, ImageService $imageService): array{
        
 
        $cards = $imageService->getAssetsCards($deck->getCards(), $imageService);
            
        return   [
        "id"=>$deck->getId(),  
        "name"=>$deck->getName(),
        "authorName"=>$deck->getAuthorName(),
        "owner"=>$deck->getOwner() ? $deck->getOwner()->getUsername() : null,
        "params"=>$deck->getParams(),
        "cards"=>$cards,
        "isPublished"=>$deck->isPublished(), 
        "usageCount"=>count($deck->getGames()),
        
        ] ;
    }
 
}
