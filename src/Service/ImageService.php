<?php

namespace App\Service; 
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{ 

    private string $imageDomain;
    private Filesystem $filesystem;private string $imagesDirectory;

    public function __construct(string $imageDomain, Filesystem $filesystem,string $imagesDirectory)
    {
        $this->imageDomain = rtrim($imageDomain, '/');
        $this->filesystem = $filesystem;
        $this->imagesDirectory = rtrim($imagesDirectory, '/');
    }
    public function getImageUrl(string $imageName, string $folder): string
    {
        $imageName = trim($imageName, '/');
        return $this->imageDomain . '/images/' . $folder . '/' . $imageName;
    }

  /**
     * Sauvegarde une image à partir de données binaires (ex: contenu extrait d'un ZIP)
     */
    public function saveImageFromBinary(string $binaryContent, string $originalName, string $subFolder): string
    {
        $targetFolder = $this->imagesDirectory . '/' . $subFolder;

        if (!$this->filesystem->exists($targetFolder)) {
            $this->filesystem->mkdir($targetFolder, 0775);
        }

        $fileInfo = pathinfo($originalName);
        $newFilename = uniqid() . '_' . $fileInfo['basename'];
        
        file_put_contents($targetFolder . '/' . $newFilename, $binaryContent);

        return $newFilename;
    }

    /**
     * Supprime une image d'un sous-dossier spécifique
     */
    public function deleteImage(string $imageName, string $subFolder): void
    {
        $fullPath = $this->imagesDirectory . '/' . $subFolder . '/' . $imageName;

        if ($this->filesystem->exists($fullPath)) {
            $this->filesystem->remove($fullPath);
        }
    }
    
    public function uploadImage(UploadedFile $file, string $subFolder): string
    {
        $targetFolder = $this->imagesDirectory . '/' . $subFolder;
        
        if (!$this->filesystem->exists($targetFolder)) {
            $this->filesystem->mkdir($targetFolder, 0775);
        }

        $newFilename = uniqid() . '.' . $file->guessExtension();
        $file->move($targetFolder, $newFilename);

        return $newFilename;
    }
    /**
 * Extrait toutes les images d'un fichier ZIP et les sauvegarde.
 * * @param \Symfony\Component\HttpFoundation\File\UploadedFile $zipFile
 * @param string $subFolder Le dossier de destination (ex: 'cards', 'avatars')
 * @return array Tableau associatif [ 'nom_origine.png' => 'nom_genere.png' ]
 * @throws \Exception Si l'archive ZIP ne peut pas être ouverte
 */
public function extractImagesFromZipToGetCards(\Symfony\Component\HttpFoundation\File\UploadedFile $zipFile, string $subFolder): array
{
    $extractedImages = [];
    $zip = new \ZipArchive();
    $res = $zip->open($zipFile->getRealPath());

    if ($res !== true) {
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
        throw new \Exception($errorMap[$res] ?? "Erreur ZIP inconnue code : " . $res);
    }

    $count = 1;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $fileInfo = pathinfo($filename);

        // On ne filtre que les extensions d'images valides
        if (isset($fileInfo['extension']) && in_array(strtolower($fileInfo['extension']), ['jpg', 'jpeg', 'png', 'webp'])) {
            $imageContent = $zip->getFromIndex($i);
            
            
            // On réutilise notre méthode binaire existante !
            $newName = $this->saveImageFromBinary($imageContent, $filename, $subFolder);
                
            $cardId = uniqid(); 
            $extractedImages[$cardId] = [
                    'id' => $cardId,
                    'image' => $newName,
                    'name' => $filename,
                    "type" => "custom",
                    quantity"=> 1,
                    "order "=> $count,
                ];
            $count++;
        }
    }

    $zip->close();

    return $extractedImages;
}

    public function getAssetsCards(array $cardsAssets): array{
    
        $cards = $cardsAssets ?? [];
    
        foreach ($cards as $id => $card) {
            if (isset($card['image']) && $card['image']) {
                $cards[$id]['url'] = $this->getImageUrl(
                    $card['image'], 
                    "cards"
                );
            } else {
                $cards[$id]['image'] = null;
            }
        }
        return $cards;
    } 
}

 