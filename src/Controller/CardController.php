<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
// use App\Service\TypeService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Game;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImageService;
use App\Service\GameObjectService;
use Doctrine\ORM\EntityManagerInterface;

final class CardController extends AbstractController
{
    #[Route('/api/game/{id}/card/uploadImage', name: 'card_image',methods: ['POST'])]
    public function addCardImage(Game $game, Request $request, ImageService $imageService , EntityManagerInterface $em, TranslatorInterface $translator): Response
    { 
        $assetsCards = $game->getAssetsCard(); 

        $metadataRaw = $request->request->get("metadata");
        if (!$metadataRaw) {
            return $this->json(['message' => 'Metadata manquante.'], 400);
        }
        $metadata = json_decode($metadataRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'Format JSON des metadata invalide.'], 400);
        }


        $cardId = $metadata["id"] ?? null;
        if (!$cardId) {
            return $this->json(['message' => 'ID de carte manquant dans les metadata.'], 400);
        }
        if (!empty($assetsCards[$cardId]["image"])) {
            $imageService->deleteImage($assetsCards[$cardId]["image"], 'cards');
        }

        $assetsCards[$cardId] = $metadata;

        $file = $request->files->get('file'); 
        if (!$file) {
            return $this->json(['message' => $translator->trans('file_not_found')], 400);
        }

        if (!$file->isValid()) {
           return $this->json([
                'message' => 'Erreur d\'upload : ' . $translator->trans($file->getErrorMessage()),
                'code' => $file->getError()
            ], 400);
        }

        $newFilename =  $imageService->uploadImage($file, 'cards');
        $assetsCards[$cardId]["image"] = $newFilename; 
         
        $game->setAssetsCard($assetsCards);
 
        $em->persist($game);
        $em->flush();
        return $this->json($imageService->getAssetsCards([ $assetsCards[$cardId]])[0] , 200, [], ['groups' => "games"] );
    }   
    #[Route("api/game/{id}/cards/uploadZip", name: 'card_zip', methods: ['POST'])]
    public function uploadCardZip(
        Game $game, 
        Request $request, 
        ImageService $imageService, 
        EntityManagerInterface $em, 
        GameObjectService $gameObjectService,
        TranslatorInterface $translator
    ): Response {

    $file = $request->files->get('file');
    if (!$file) {
        return $this->json(['message' => $translator->trans('file_not_found')], 400);
    } 
    
    if (!$file->isValid()) {
       return $this->json(['message' =>  $translator->trans($file->getErrorMessage())], 400);
    }  

    $uploadedFiles = [];
    try {
        // 1. On confie le traitement du ZIP au service global
        $uploadedFiles = $imageService->extractImagesFromZipToGetCards($file, 'cards');
    } catch (\Exception $e) {
        return $this->json(['message' => $e->getMessage()], 400);
    }

    // Mettre à jour et sauvegarder
    $game->setAssetsCard([$game->getAssetsCard(), $uploadedFiles]);
    $em->persist($game); 
    $em->flush();

    return $this->json(
        $imageService->getAssetsCards( $uploadedFiles) , 
        200, 
        [],
        [
            'groups' => "games",
            'json_encode_options' => JSON_FORCE_OBJECT // <--- INDISPENSABLE
        ]
    );    
    } 
    
    #[Route('api/game/{id}/edit/card/{cardId}', name: 'edit_card')]
    public function editCard(Game $game ,  $cardId,SerializerInterface $serializer, EntityManagerInterface $manager, Request $request ): Response
    {    
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }
    
        $assetsCards = $game->getAssetsCard() ?? []; 
    
        if (isset($assetsCards[$cardId])) { 
            $assetsCards[$cardId] = array_merge($assetsCards[$cardId], $data);
        } else { 
            $data['id'] = $cardId; 
            $assetsCards[$cardId] = $data;
        }
        $game->setAssetsCard($assetsCards); 
 
        $manager->getUnitOfWork()->computeChangeSets();  
        $manager->persist($game);
        $manager->flush();
        return $this->json($game,200, [],['groups'=>"games"] );
        
    }
      #[Route('api/game/{id}/get/cards', name: 'get_cards',methods: ['GET'])]
    public function getCards(Game $game, ImageService $imageService, GameObjectService $gameObjectService): Response
    {  
 
        return $this->json( $imageService->getAssetsCards($game->getAssetsCard()) ,200, [],['groups'=>"games"] );
    }
        
    #[Route('api/game/{id}/restore/cards', name: 'restore_card',methods: ['PUT'])]
    public function restoreCards(Game $game ,ImageService $imageService, EntityManagerInterface $manager): Response
    {    
         
$assetsCards = $game->getAssetsCard();

    if (is_array($assetsCards) && !empty($assetsCards)) {
        foreach ($assetsCards as $card) {
            if (!empty($card["image"])) {
                // Utilisation de la suppression globale
                $imageService->deleteImage($card["image"], 'cards');
            }
        }
    }

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
                        'couleur' => $colorName
                    ]
                ];
            }
        }

    $game->setAssetsCard($cardsConfig);

    // ... Reste de ta logique de génération de $cardsConfig ...
    $game->setAssetsCard($cardsConfig);
    $manager->persist($game);
    $manager->flush();

    return $this->json(["message" => "ok"], 200, [], ['groups' => "games"]);
   
 
    }
    
}
