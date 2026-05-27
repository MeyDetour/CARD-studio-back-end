<?php

namespace App\Service;

use App\Entity\Game;

class GameObjectService
{
    public function __construct( )
    {

    }
    public function getGameObject(Game $game, ImageService $imageService): array{
        $averageNotes = 0; 
        $notes = [];
        foreach ($game->getNotes() as $note){
            $averageNotes += $note->getRate();
         $notes[] = [
            "id"=>$note->getId(),
            "rate"=>$note->getRate(),
            "comment"=>$note->getDescription(),
            "date"=>$note->getDate()->format("Y-m-d"),
         ] ;
        }
        if ($averageNotes > 0 ){
                $averageNotes = round($averageNotes/count($game->getNotes()) , 1) ;
        }
 
        $cards = $imageService->getAssetsCards($game->getAssetsCard(), $imageService);
            
        return   [
        "id"=>$game->getId(), 
        "requestDate"=>new \DateTime(),
        "name"=>$game->getName(),
        "image"=>$game->getImage()? $imageService->getImageUrl($game->getImage(),    "game" ,'game_image') : null,
        "isPublic"=>$game->isPublic(),
        "description"=>$game->getDescription(),
        "averageNotes"=>$averageNotes, 
        "playerCount"=>$game->getPlayerCount(),
        "notes"=>$notes,
        "gameCount"=>$game->getGameCount(),
        "types"=>$game->getTypes(), 
        "editionHistory"=>$game->getEditionHistory(),
        "globalValue"=>$game->getglobalValue(),
        "globalValueStatic"=>$game->getglobalValueStatic() ?? [],
        "playerGlobalValue"=>$game->getPlayerGlobalValue(),
        "params"=>$game->getParams(),
        "events"=>[ 
            "triggers"=>$game->getEventTriggers(),
            "events"=>$game->getEventEvents(),
            "win"=>$game->getEventWin(),
            "loose"=>$game->getEventLoose(),
            "withValueEvent"=>$game->getEventWithValueEvents()
        ],
        "assets"=>[
            "cards"=>$cards,
            "gains"=>$game->getAssetsGain(),
            "roles"=>$game->getRoles(),
        ]
        ] ;
    }

}
