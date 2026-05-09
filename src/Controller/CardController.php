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
    #[Route('/api/game/{id}/card/{cardId}/uploadImage', name: 'card_image',methods: ['POST'])]
    public function addCardImage(Game $game, $cardId, Request $request, ImageService $imageService, EntityManagerInterface $em, TranslatorInterface $translator, Filesystem $filesystem): Response
    { 
        $assetsCards = $game->getAssetsCard(); 
        if (!isset($assetsCards[$cardId])) {
            return $this->json(['message' => 'Carte non trouvée.'], 404);
        }
        
        $oldImage = $assetsCards[$cardId]["image"] ?? null;
        
        $folder = $this->getParameter('images_directory') . '/card';
        if (!empty($oldImage)) {
            $oldPath = $folder . '/' . $oldImage;
            if ($filesystem->exists($oldPath)) {
                $filesystem->remove($oldPath);
            }
        }

        $file = $request->files->get('file'); 
        if (!$file) {
            return $this->json(['message' => $translator->trans('file_not_found')], 400);
        }

        if (!$file->isValid()) {
            // On récupère le message d'erreur de PHP/Symfony et on le traduit
            $errorMessage = $translator->trans($file->getErrorMessage());
            
            return $this->json([
                'message' => 'Erreur d\'upload : ' . $errorMessage,
                'code' => $file->getError()
            ], 400);
        }

        $newFilename = uniqid() . '.' . $file->guessExtension();
 
        $file->move($folder, $newFilename);
        $assetsCards[$cardId]["image"] = $newFilename;
         
        $game->setAssetsCard($assetsCards);

        // 6. Sauvegarde
        $em->persist($game);
        $em->flush();
            return $this->json([
                'message' => 'Image ajoutée avec succès',
                'filename' => $newFilename,
                'url' => $imageService->getImageUrl($newFilename, "card", 'card_image')
            ]);
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
    //dd(php_ini_loaded_file(), ini_get('upload_max_filesize'));
    if (!$file->isValid()) {
        // C'est ici que tu auras la vraie réponse (ex: "The file is too large")
       return $this->json(['message' =>  $file->getErrorMessage()], 400);
    }  
    $newAssetsCards = [];
    $folder = $this->getParameter('images_directory') . '/card';
 

    $zip = new \ZipArchive();
    $res = $zip->open($file->getRealPath()); 
    if ($res) { 
   
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $fileInfo = pathinfo($filename); 
            // On ne traite que les images
            if (isset($fileInfo['extension']) && in_array(strtolower($fileInfo['extension']), ['jpg', 'jpeg', 'png', 'webp'])) {
                
                $imageContent = $zip->getFromIndex($i);
                $newName = uniqid() . '_' . $fileInfo['basename'];
                
                // On utilise l'ID (nom du fichier sans extension) pour trouver la carte
                $cardId = uniqid(); // Par défaut, on génère un ID unique
                // Si la carte existe dans ton tableau associatif
               
                // Sauvegarde physique
                if (!is_dir($folder)) {
                    mkdir($folder, 0775, true);
                }
                file_put_contents($folder . '/' . $newName, $imageContent);
                // Mise à jour de l'image pour cette clé précise
                $newAssetsCards[$cardId] = [
                    'id' => $cardId,
                    'name'=>$fileInfo['basename'],
                    'image' => $newName
                ]; 
             
            }
        }
        $zip->close();
    }
 
        if (!$res) {
            $errorMap = [
                \ZipArchive::ER_EXISTS => "Le fichier existe déjà.",
                \ZipArchive::ER_INCONS => "L'archive zip est inconsistante.",
                \ZipArchive::ER_INVAL => "Argument invalide.",
                \ZipArchive::ER_MEMORY => "Erreur de mémoire.",
                \ZipArchive::ER_NOENT => "Le fichier n'existe pas.",
                \ZipArchive::ER_NOZIP => "Ce n'est pas une archive zip valide.",
                \ZipArchive::ER_OPEN => "Impossible d'ouvrir le fichier.",
                \ZipArchive::ER_READ => "Erreur de lecture.",
                \ZipArchive::ER_SEEK => "Erreur de positionnement."
            ];

            $message = $errorMap[$res] ?? "Erreur inconnue code : " . $res;
            return $this->json(['message' => $message], 400);
        }

        // Mettre à jour et sauvegarder
        $game->setAssetsCard([$game->getAssetsCard(), $newAssetsCards]);
        $em->persist($game);
        $em->flush();

    
        return $this->json(
          $gameObjectService->getAssetsCards($newAssetsCards,$imageService) , 
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
    public function getCards(Game $game, ImageService $imageService): Response
    {  

        $cards = $game->getAssetsCard() ?? [];
    
        foreach ($cards as $id => $card) {
            if (isset($card['image']) && $card['image']) {
                $cards[$id]['image'] = $imageService->getImageUrl(
                    $card['image'], 
                    "card", 
                    'card_image'
                );
            } else {
                $cards[$id]['image'] = null;
            }
        }
        return $this->json( $cards ,200, [],['groups'=>"games"] );
    }
        
    #[Route('api/game/{id}/restore/cards', name: 'restore_card',methods: ['PUT'])]
    public function restoreCards(Game $game ,  Filesystem $filesystem, EntityManagerInterface $manager): Response
    {    
         
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $this->json(['message' => 'Données JSON invalides'], 400);
    }
 
    try {
        $folder = $this->getParameter('images_directory') . '/card';
    } catch (\Exception $e) {
        return $this->json(['message' => 'Configuration du dossier d\'images manquante'], 500);
    }
 
    $assetsCards = $game->getAssetsCard();
 
    if (!is_array($assetsCards) || empty($assetsCards)) {
        return $this->json(['message' => 'Aucune carte à restaurer pour ce jeu'], 200);
    }

    foreach ($assetsCards as $card) {
        $oldImage = $card["image"] ?? null;
 
        if (!empty($oldImage)) {
            $oldPath = $folder . '/' . $oldImage;

            try {
                if ($filesystem->exists($oldPath)) {
                    $filesystem->remove($oldPath);
                }
            } catch (\Exception $e) { 
                continue; 
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
                    'value' => $value,
                    'type' => "french_standard",
                    'addedAttributs' => [
                        'couleur' => $colorName
                    ]
                ];
            }
        }

    $game->setAssetsCard($cardsConfig);

    $manager->persist($game);
    $manager->flush();
    return $this->json( ["message"=>"ok"],200, [],['groups'=>"games"] );
            
      
        
    }
}
