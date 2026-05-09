<?php

namespace App\Service; 

class ImageService
{ 

    private string $imageDomain;

    public function __construct(string $imageDomain)
    {
        $this->imageDomain = rtrim($imageDomain, '/');
    }
    public function getImageUrl(string $imageName, string $folder, string $filter): string
    {
        $imageName = trim($imageName, '/');
        return $this->imageDomain . '/images/' . $folder . '/' . $imageName;
    }

}


//bon je sais pas trop comment gerer, j'ai mon app qui configure le jeu, les sauvegarde ne sont pas faites automatiquement, ellles se font en local puis l'utilisateur choisis de les save, mais quand j'ajoute une image par exemple pour le jeu, je dois utpload l'image sur le server parceque je peux pas stocker l'image en local storage il me semble.  donc à ce moment la j'upload direct, donc la sauvegarde de l'image se fait en parallele de la sauvegarde en local. Quadn tu changes tu ne peux pas revenir en arriere, quand je change l'image d'une carte au final c'est pareil. Mais la je travail sur l'importation de plusieurs cartes.  Je supp celles qui sont déjà là doànc elle seront sup en local. J4'importe monf ichier zip qui va recupere toutes les images et creer des cartes de jeu avec. Mais je 