<?php

namespace App\Service;
 
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TypeService
{
    public function __construct(private UploaderHelper $helper, private CacheManager $cacheManager)
    {

    }

    public function verify($value,$type)
    {
        if ($type === "string") {
            if (!is_string($value)) {
                return false;
            }
        } elseif ($type === "number") {
            if (!is_numeric($value)) {
                return false;
            }
        } elseif ($type === "bool" || $type === "boolean") {
            if (!is_bool($value)) {
                return false;
            }
        } else {
            return false; 
        }
       return true; 
    }
}
