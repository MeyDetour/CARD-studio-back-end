<?php

namespace App\Service;
 
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageService
{
    public function __construct(private UploaderHelper $helper, private CacheManager $cacheManager)
    {

    }
    public function getImageUrl(string $imageName, string $folder, string $filter): string
    {
        $imageName = trim($imageName, '/');
        // Retourne toujours l'URL brute, sans passer par LiipImagine
        return '/images/' . $folder . '/' . $imageName;
    }

}
