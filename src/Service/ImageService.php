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
     
    $path = 'images/'.$folder.'/'.$imageName; 

    try {
        return $this->cacheManager->getBrowserPath($path, $filter);
    } catch (\Exception $e) { 
        return '/images/'.$folder.'/'.$imageName; 
    }
    }

}
