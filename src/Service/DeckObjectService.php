<?php

namespace App\Service;

use App\Entity\Deck;

class DeckObjectService
{
    public function __construct( )
    {

    }
    public function getDeckObject(Deck $deck, ImageService $imageService): array{
        
 
        $cards = $imageService->getAssetsCards($deck->getCards());
            
        return   [
        "id"=>$deck->getId(),  
        "uniqueId"=>$deck->getUniqueId(),
        "name"=>$deck->getName(),
        "authorName"=>$deck->getAuthorName(),
        "owner"=>$deck->getOwner() ? $deck->getOwner()->getUsername() : null,
        "params"=>$deck->getParams()??[],
        "cards"=>$cards,
        "isPublished"=>$deck->isPublished(), 
        "usageCount"=>count($deck->getGames()),
        
        ] ;
    }

  public function settDefaultDeckCards(Deck $deck): Deck{
   
    $cardsConfig = [];
    $colors = [
            'pique'   => range(1, 13),
            'trefle' => range(14, 26),
            'coeur'   => range(27, 39),
            'carreau' => range(40, 52),
        ];
    foreach ($colors as $colorName => $range) {
        foreach ($range as $index => $id) {
            // La valeur de la carte va de 1 à 13 pour chaque couleur
            $value = $index + 1; 

            $cardsConfig[$id] = [
                'id' => $id,
                'name' => $value . " de " . $colorName,
                'type' => "french_standard",
                'addedAttributs' => [

                    'value' => $value,
                    'symbol' => $colorName,
                    'color' => $colorName === 'coeur' || $colorName === 'carreau' ? 'red' : 'black',
                ]
            ];
        }
    }
    $params = $deck->getParams();
    $params["addedAttributs"] = [
        "value" =>"",
        "symbol" => "",
        "color" => ""
    ];
    $deck->setParams($params);
    $deck->setCards($cardsConfig);

    return $deck;
  }
   
}
