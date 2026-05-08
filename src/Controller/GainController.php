<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\ImageService;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
final class GainController extends AbstractController
{

    #[Route('api/game/{id}/edit/gain/{gainId}', name: 'edit_gain')]
    public function editGain(Game $game , $gainId, SerializerInterface $serializer, EntityManagerInterface $manager, Request $request ): Response
    {    
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }
    
        $assetsGains = $game->getAssetsGain() ?? []; 
    
       $assetsGains = array_filter($assetsGains, function($gain) use ($gainId) {
       return isset($gain['id']) && $gain['id'] != $gainId;
       });
        $data['id'] = $gainId; 
        $assetsGains[] = $data;

        $assetsGains = array_values($assetsGains);
        $game->setAssetsGain([]); 
        $game->setAssetsGain($assetsGains);
 
        $manager->getUnitOfWork()->computeChangeSets();  
        $manager->persist($game);
        $manager->flush();
        return $this->json($game,200, [],['groups'=>"games"] );
        
    }
    #[Route('/api/game/{id}/gain/{gainId}/uploadImage', name: 'gain_image',methods: ['POST'])]
    public function addGainImage(Game $game, $gainId, Request $request, ImageService $imageService, EntityManagerInterface $em, TranslatorInterface $translator, Filesystem $filesystem): Response
    { 
        
        $assetsGains = $game->getAssetsGain() ?? []; 
        $targetIndex = null;
 
        foreach ($assetsGains as $index => $gain) {
            if (isset($gain['id']) && $gain['id'] == $gainId) {
                $targetIndex = $index;
                break;
            }
        }

        if ($targetIndex === null) {
            return $this->json(['message' => 'Gain non trouvé.'], 404);
        }
 
        $oldImage = $assetsGains[$targetIndex]["image"] ?? null;
        $folder = $this->getParameter('images_directory') . '/gain';
        
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
            return $this->json([
                'message' => 'Erreur d\'upload : ' . $translator->trans($file->getErrorMessage()),
                'code' => $file->getError()
            ], 400);
        }
 
        $newFilename = uniqid() . '.' . $file->guessExtension();
        $file->move($folder, $newFilename);
 
        $assetsGains[$targetIndex]["image"] = $newFilename;
         
        $game->setAssetsGain($assetsGains);

        $em->persist($game);
        $em->flush();
        return $this->json([
            'message' => 'Image ajoutée avec succès',
            'filename' => $newFilename,
            'url' => $imageService->getImageUrl($newFilename, "gain", 'gain_image')
        ]);
    }   
    

}
