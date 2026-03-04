<?php

namespace App\Service;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageService
{
    public function __construct(private UploaderHelper $helper, private CacheManager $cacheManager, private ImageRepository $imageRepository)
    {

    }
    public function getImageUrl(string $imageName, string $folder, string $filter): string
    {
        $path = '/images/'.$folder.'/'.$imageName;

        return $this->cacheManager->generateUrl($path, $filter);
    }

}
