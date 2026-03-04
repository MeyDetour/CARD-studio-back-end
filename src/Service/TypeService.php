<?php

namespace App\Service;

use App\Entity\Image; 
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TypeService
{
    public function __construct(private UploaderHelper $helper, private CacheManager $cacheManager)
    {

    }

    public function verify($value,$type)
    {

       return true; 
    }
}
