<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DeckRepository;
use App\Service\DeckObjectService;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Deck;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\TypeService;
use Symfony\Component\Filesystem\Filesystem;

final class DeckController extends AbstractController
{

      #[Route('/api/get/public/decks', name: 'get_public_decks')]
    public function getPublicDecks( DeckRepository $deckRepository,   DeckObjectService $deckObjectService, ImageService $imageService): Response
    {    
        $decks = $deckRepository->findBy(["isPublished"=>true]);
         
        $deckObjects = [];
        foreach ($decks as $deck) {
            if ($deck->getCards()==[] || $deck->getOwner() == $this->getUser() ){
               continue;
            }

            $deckObjects[] = $deckObjectService->getDeckObject($deck, $imageService);
        }
        return $this->json($deckObjects, 200, [], ['groups' => 'deck']);
    }    
#[Route('/api/new/deck', name: 'new_deck')]
    public function newDeck( DeckRepository $deckRepository,  EntityManagerInterface $manager, DeckObjectService $deckObjectService, ImageService $imageService): Response
    {   
        $deck = new Deck();
        $deck->setName("Default name");
        $deck->setAuthorName("Unknown");
        $deck->setIsPublished(false);
        $deck->setOwner($this->getUser());
        $deck->setUniqueId(uniqid().uniqid());
        $deck->setCards([]);
        $deck->setParams([]);
        $manager->persist($deck);
        $manager->flush();
       return $this->json($deckObjectService->getDeckObject($deck,$imageService), 200, [], ['groups' => 'deck']);
    }

     #[Route('api/deck/edit/{id}', name: 'edit_deck')]
    public function editDeck(Deck $deck ,  SerializerInterface $serializer, EntityManagerInterface $manager, Request $request , TypeService $typeService,Filesystem $filesystem,ImageService $imageService): Response
    {   
        $deckEdited = $serializer->deserialize($request->getContent(), Deck::class, 'json');
      
        if (!$typeService->verify($deckEdited->getName(), "string")) {
            return $this->json(["message" => "Invalid name. (field : name, accepted : string)"], 406);
        };
        if (!$typeService->verify($deckEdited->getAuthorName(), "string")) {
            return $this->json(["message" => "Invalid author name. (field : authorName, accepted : string)"], 406);
        }; 
 
         $currentCards = $deck->getCards() ?? []; 
        // new cards representent les nouvelles données (incluant aussi les données prééxistantes)
        $newCards =$deckEdited->getCards() ?? [];

        if ($newCards !== null) {
            foreach ($currentCards as $id => $cardData) {
                        $oldImage = $cardData['image'] ?? null;
                        $isCardRemoved = !isset($newCards[$id]);
                        $isImageChanged = isset($newCards[$id]) && ($newCards[$id]['image'] ?? null) !== $oldImage;

                        if ($oldImage && ($isCardRemoved || $isImageChanged)) {
                             $imageService->deleteImage($oldImage, 'cards');
                        }
                    }
                
                // 3. On applique les nouvelles cartes
                $deck->setCards($newCards);
        }
        if( $deckEdited->getParams() != null){
            $deck->setParams($deckEdited->getParams());
        };
        if($deckEdited->getName()){
            $deck->setName($deckEdited->getName());
        }    
        if($deckEdited->getAuthorName()){
            $deck->setAuthorName($deckEdited->getAuthorName());
        } 

        if ($deckEdited->isPublished() !== null){
        $deck->setIsPublished($deckEdited->isPublished());
        }
        $manager->persist($deck);
        $manager->flush();
       return $this->json($deck, 200, [], ['groups' => 'deck']);
    }

      #[Route('/api/my/decks', name: 'get_decks_of_user')]
    public function getMyDecks(  DeckRepository $deckRepository,  EntityManagerInterface $manager, DeckObjectService $deckObjectService, ImageService $imageService): Response
    {
         $decks = $deckRepository->findBy(["owner"=>$this->getUser()]);
            $decksToSend = [];
      
        foreach($decks as $deck){ 
            $deckToSend = $deckObjectService->getDeckObject($deck, $imageService);
           
            unset($deckToSend["params"]); 
            $decksToSend[] = $deckToSend;
        }
        return $this->json(  $decksToSend ,200, [],['groups'=>"decks"] );
  
     }

    #[Route('api/deck/{id}', name: 'get_deck')]
    public function getDeck(Deck $deck, DeckObjectService $deckObjectService, ImageService $imageService): Response
    {    
        if ($deck->getOwner() == $this->getUser()){     
             return $this->json($deckObjectService->getDeckObject($deck, $imageService) ,200, [],['groups'=>"deck"] );
        }
        return $this->json(  null ,200, [],['groups'=>"decks"] );
    }   
     #[Route('deck/public/{id}', name: 'get_deck_public')]
    public function getDeckWithId(Deck $deck, DeckObjectService $deckObjectService, ImageService $imageService): Response
    {  if (!$deck->isPublished()){
            return $this->json(  null ,200, [],['groups'=>"deck"] );
        }
          $deckToSend = $deckObjectService->getDeckObject($deck, $imageService);
           unset($deckToSend["owner"]);  
           return $this->json($deckToSend ,200, [],['groups'=>"deck"] );
    }

     #[Route('/decks', name: 'get_decks')]
    public function getDecks( DeckRepository $deckRepository, DeckObjectService $deckObjectService, ImageService $imageService): Response
    {
        $decks = $deckRepository->findBy(["isPublished" => true]);
        $decksToSend = [];
        foreach($decks as $deck){ 
             $deckToSend = $deckObjectService->getDeckObject($deck, $imageService);
             unset($deckToSend["owner"]);   
             $decksToSend[] = $deckToSend;
        }
        return $this->json(  $decksToSend ,200, [],['groups'=>"decks"] );
    } 
    #[Route('/api/deck/{id}/card/uploadImage', name: 'deck_card_image',methods: ['POST'])]
    public function addCardDeckImage(Deck $deck,  Request $request,ImageService $imageService , EntityManagerInterface $em, TranslatorInterface $translator): Response
    { 
        $assetsCards = $deck->getCards(); 
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
         
        $deck->setCards($assetsCards);
 
        $em->persist($deck);
        $em->flush();
        return $this->json($imageService->getAssetsCards([ $assetsCards[$cardId]])[0] , 200, [], ['groups' => "decks"] );
    }   
      #[Route("api/deck/{id}/cards/uploadZip", name: 'card_zip', methods: ['POST'])]
    public function uploadCardZip(
        Deck $deck, 
        Request $request, 
        ImageService $imageService, 
        EntityManagerInterface $em
    ): Response {

    $file = $request->files->get('file');
    if (!$file) {
        return $this->json(['message' => 'File not found'], 400);
    } 
    
    if (!$file->isValid()) {
       return $this->json(['message' =>  $file->getErrorMessage()], 400);
    }  

    $uploadedFiles = [];
    try {
        // 1. On confie le traitement du ZIP au service global
        $uploadedFiles = $imageService->extractImagesFromZipToGetCards($file, 'cards');
    } catch (\Exception $e) {
        return $this->json(['message' => $e->getMessage()], 400);
    }

    // Mettre à jour et sauvegarder
    $deck->setCards([$deck->getCards(), $uploadedFiles]);
    $em->persist($deck); 
    $em->flush();

    return $this->json(
        $imageService->getAssetsCards( $uploadedFiles) , 
        200, 
        [],
        [
            'groups' => "decks",
            'json_encode_options' => JSON_FORCE_OBJECT // <--- INDISPENSABLE
        ]
    );    
    } 
    #[Route('api/deck/{id}/edit/card/{cardId}', name: 'edit_card_deck')]
    public function editCard(Deck $deck ,  $cardId,SerializerInterface $serializer, EntityManagerInterface $manager, Request $request ): Response
    {    
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }
    
        $assetsCards = $deck->getCards() ?? []; 
    
        if (isset($assetsCards[$cardId])) { 
            $assetsCards[$cardId] = array_merge($assetsCards[$cardId], $data);
        } else { 
            $data['id'] = $cardId; 
            $assetsCards[$cardId] = $data;
        }
        $deck->setCards($assetsCards); 
 
        $manager->getUnitOfWork()->computeChangeSets();  
        $manager->persist($deck);
        $manager->flush();
        return $this->json($deck,200, [],['groups'=>"decks"] );
        
    }
        
    #[Route('api/deck/{id}/restore/cards', name: 'restore_deck',methods: ['PUT'])]
    public function restoreCards(Deck $deck ,ImageService $imageService, EntityManagerInterface $manager): Response
    {    
         
$assetsCards = $deck->getCards();

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

    // ... Reste de ta logique de génération de $cardsConfig ...
    $deck->setCards($cardsConfig);
    $manager->persist($deck);
    $manager->flush();

    return $this->json($deck, 200, [], ['groups' => "deck"]);
   
 
    }
    #[Route('api/deck/remove/{id}', name: 'remove_deck', methods: ['DELETE'])]
    public function removeGame(Deck $deck, EntityManagerInterface $manager): Response
    {    
        if (!$deck) {
            return $this->json(["message" => "Deck not found."], 404);
        }
        if ($deck->getOwner() == $this->getUser()){     
            foreach ($deck->getGames() as $game) {
                $game->setDeckUsed(null);
                $manager->persist($game);
            }
             $manager->remove($deck);
             $manager->flush();
             return $this->json( ["message"=>"ok"] ,200 );
        }
        return $this->json(  null ,200, [],['groups'=>"games"] );
    }   
}

