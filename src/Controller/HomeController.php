<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
 

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig' );
    } 
      #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentions_legales.html.twig' );
    }  
    #[Route('/documentation', name: 'app_documentation')]
   public function documentation(Request $request): Response
{



// Documentation des routes de l'API sous forme de tableau associatif
$routes = [
    'User' => [
        [
            'name' => 'Get current user',
            'route' => '/api/me',
            'description' => 'Récupère les informations du profil utilisateur connecté.',
            'methode' => 'GET',
            'body' => null,
            'sendBack' => [
                'id' => 'number',
                'username' => 'string',
                'displayErrors' => 'bool',
                'lang' => 'string'
            ],
            'token' => true
        ],
        [
            'name' => 'Edit profile',
            'route' => '/api/edit/me',
            'description' => 'Modifie la langue et la visibilité des erreurs du profil utilisateur.',
            'methode' => 'PUT',
            'body' => [
                'lang' => 'string (NOT NULL)',
                'displayErrors' => 'bool (NOT NULL)'
            ],
            'sendBack' => ['message' => 'ok'],
            'token' => true
        ]
    ],
    'Registration' => [
        [
            'name' => 'Sign up',
            'route' => '/register',
            'methode' => 'POST',
            'description' => 'Permet la création d’un compte utilisateur.',
            'body' => [ 
                'username' => 'string (NOT NULL)',
                'password' => 'string (NOT NULL)'
            ],
            'sendBack' => ['user' => 'object'],
            'token' => false
        ]
    ],
    'Game' => [
        [
            'name' => 'Get all public games',
            'route' => '/games',
            'methode' => 'GET',
            'description' => 'Liste tous les jeux publics.',
            'body' => null,
            'sendBack' => [
                'id' => 'number',
                'name' => 'string',
                'image' => 'null',
                'isPublic' => 'bool',
                'description' => 'string',
                'averageNotes' => 'number',
                'notes' => 'array',
                'playerCount' => 'number',
                'gameCount' => 'number',
                'types' => 'string',
                'globalValueStatic' => 'array',
                'params' => [
                    'globalGame' => [
                        'jeuSolo' => 'bool',
                        'playersCanJoin' => 'bool',
                        'minPlayer' => 'number',
                        'maxPlayer' => 'number'
                    ],
                    'rendering' => [
                        'menu' => [
                            'template' => 'number',
                            'backgroundImage' => 'null'
                        ],
                        'game' => [
                            'template' => 'number',
                            'backgroundImage' => 'null',
                            'displayHandDeck' => 'bool',
                            'displayCountAdversaryHandDeck' => 'bool',
                            'displayStatistics' => 'bool',
                            'displayHistory' => 'bool',
                            'displayTimer' => 'bool',
                            'displayChat' => 'bool'
                        ]
                    ],
                    'tours' => [
                        'activation' => 'bool',
                        'sens' => 'string',
                        'startNumber' => 'number',
                        'timerActivation' => 'bool',
                        'duration' => 'number',
                        'maxTour' => 'number',
                        'actionOnlyAtPlayerTour' => 'bool',
                        'endOfTour' => 'array',
                        'actions' => [
                            [
                                'id' => 'number',
                                'name' => 'string',
                                'type' => 'string',
                                'condition' => 'string',
                                'appearAtPlayerTurn' => 'bool',
                                'withValue' => 'array',
                                'return' => 'string'
                            ]
                        ]
                    ],
                    'manches' => [
                        'max' => 'null',
                        'sens' => 'string',
                        'startNumber' => 'number'
                    ],
                    'cards' => [
                        'activeHandDeck' => 'bool',
                        'activPersonalHandDeck' => 'bool',
                        'activPersonalHandDiscard' => 'bool',
                        'activeDiscardDeck' => 'bool',
                        'discard' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'pickOnDeck' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'activeCardAsGain' => 'bool',
                        'handDeck' => [
                            'activation' => 'bool',
                            'visibility' => 'string'
                        ],
                        'cardBoard' => 'array'
                    ],
                    'gain' => [
                        'activation' => 'bool',
                        'groupPot' => 'bool'
                    ],
                    'roles' => [
                        'activation' => 'bool'
                    ]
                ]
            ],
            'token' => false
        ],
        [
            'name' => 'Get my games',
            'route' => '/api/my/games',
            'methode' => 'GET',
            'description' => 'Liste les jeux de l’utilisateur connecté.',
            'body' => null,
           'sendBack' => [
                'id' => 'number',
                'name' => 'string',
                'image' => 'null',
                'isPublic' => 'bool',
                'description' => 'string',
                'averageNotes' => 'number',
                'notes' => 'array',
                'playerCount' => 'number',
                'gameCount' => 'number',
                'types' => 'string',
                'globalValueStatic' => 'array',
                'params' => [
                    'globalGame' => [
                        'jeuSolo' => 'bool',
                        'playersCanJoin' => 'bool',
                        'minPlayer' => 'number',
                        'maxPlayer' => 'number'
                    ],
                    'rendering' => [
                        'menu' => [
                            'template' => 'number',
                            'backgroundImage' => 'null'
                        ],
                        'game' => [
                            'template' => 'number',
                            'backgroundImage' => 'null',
                            'displayHandDeck' => 'bool',
                            'displayCountAdversaryHandDeck' => 'bool',
                            'displayStatistics' => 'bool',
                            'displayHistory' => 'bool',
                            'displayTimer' => 'bool',
                            'displayChat' => 'bool'
                        ]
                    ],
                    'tours' => [
                        'activation' => 'bool',
                        'sens' => 'string',
                        'startNumber' => 'number',
                        'timerActivation' => 'bool',
                        'duration' => 'number',
                        'maxTour' => 'number',
                        'actionOnlyAtPlayerTour' => 'bool',
                        'endOfTour' => 'array',
                        'actions' => [
                            [
                                'id' => 'number',
                                'name' => 'string',
                                'type' => 'string',
                                'condition' => 'string',
                                'appearAtPlayerTurn' => 'bool',
                                'withValue' => 'array',
                                'return' => 'string'
                            ]
                        ]
                    ],
                    'manches' => [
                        'max' => 'null',
                        'sens' => 'string',
                        'startNumber' => 'number'
                    ],
                    'cards' => [
                        'activeHandDeck' => 'bool',
                        'activPersonalHandDeck' => 'bool',
                        'activPersonalHandDiscard' => 'bool',
                        'activeDiscardDeck' => 'bool',
                        'discard' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'pickOnDeck' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'activeCardAsGain' => 'bool',
                        'handDeck' => [
                            'activation' => 'bool',
                            'visibility' => 'string'
                        ],
                        'cardBoard' => 'array'
                    ],
                    'gain' => [
                        'activation' => 'bool',
                        'groupPot' => 'bool'
                    ],
                    'roles' => [
                        'activation' => 'bool'
                    ]
                ]
            ],
            'token' => true
        ],
        [
            'name' => 'Create new game',
            'route' => '/api/new/game',
            'methode' => 'POST',
            'description' => 'Crée un nouveau jeu.',
            'body' => null,
            'sendBack' => ['game' => 'object'],
            'token' => true
        ],
        [
            'name' => 'Get game by id',
            'route' => '/api/game/{id}',
            'methode' => 'GET',
            'description' => "Les joueurs récupèrent le jeu par son identifiant afin de le transmettre au serveuer.",
            'body' => null,
            'sendBack' => [
                'id' => 'number',
                'name' => 'string',
                'image' => 'null',
                'isPublic' => 'bool',
                'description' => 'string',
                'averageNotes' => 'number',
                'notes' => 'array',
                'playerCount' => 'number',
                'gameCount' => 'number',
                'types' => 'string',
                'editionHistory' => 'array',
                'globalValue' => 'array',
                'globalValueStatic' => [
                    'Joueurs' => [
                        'type' => 'string',
                        'defaultValue' => 'number',
                        'value' => 'string',
                        'display' => 'bool'
                    ]
                ],
                'playerGlobalValue' => 'array',
                'params' => [
                    'globalGame' => [
                        'jeuSolo' => 'bool',
                        'playersCanJoin' => 'bool',
                        'minPlayer' => 'number',
                        'maxPlayer' => 'number',
                        'autoriseSpectator' => 'bool'
                    ],
                    'rendering' => [
                        'menu' => [
                            'template' => 'number',
                            'backgroundImage' => 'null'
                        ],
                        'game' => [
                            'template' => 'number',
                            'backgroundImage' => 'null',
                            'displayCountAdversaryHandDeck' => 'bool',
                            'displayHandDeck' => 'bool',
                            'displayStatistics' => 'bool',
                            'displayChat' => 'bool'
                        ]
                    ],
                    'tours' => [
                        'activation' => 'bool',
                        'sens' => 'string',
                        'startNumber' => 'number',
                        'firstPlayer' => 'string',
                        'firstPlayerValue' => 'null',
                        'maxTour' => 'number',
                        'actionOnlyAtPlayerTour' => 'bool',
                        'endOfTour' => 'array',
                        'actions' => [
                            [
                                'name' => 'string',
                                'id' => 'number',
                                'condition' => 'string',
                                'appearAtPlayerTurn' => 'bool',
                                'withValue' => 'array',
                                'actionOnDeck' => 'bool',
                                'actionOnDiscardDeck' => 'bool'
                            ]
                        ],
                        'actionsAtEnd' => 'number'
                    ],
                    'manches' => [
                        'max' => 'null',
                        'sens' => 'string',
                        'startNumber' => 'number'
                    ],
                    'cards' => [
                        'activeHandDeck' => 'bool',
                        'activPersonalHandDeck' => 'bool',
                        'activPersonalHandDiscard' => 'bool',
                        'activeDiscardDeck' => 'bool',
                        'discard' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ],
                            'activation' => 'bool',
                            'renderTheLastDiscardedCard' => 'bool'
                        ],
                        'pickOnDeck' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'activeCardAsGain' => 'bool',
                        'handDeck' => [
                            'activation' => 'bool',
                            'visibility' => 'string'
                        ],
                        'cardBoard' => 'array',
                        'deck' => [
                            'activation' => 'bool',
                            'renderTheNextDeckCard' => 'bool'
                        ]
                    ],
                    'roles' => [
                        'activation' => 'bool'
                    ],
                    'gain' => [
                        'activation' => 'bool',
                        'groupPot' => 'bool'
                    ]
                ],
                'events' => [
                    'demons' => [
                        [
                            'id' => 'number',
                            'condition' => 'string',
                            'name' => 'string',
                            'events' => 'array',
                            'boucle' => 'null'
                        ]
                    ],
                    'events' => [
                        [
                            'id' => 'number',
                            'name' => 'string',
                            'condition' => 'string',
                            'event' => 'object',
                            'boucle' => 'string'
                        ]
                    ],
                    'win' => [
                        'boucle' => 'string',
                        'condition' => 'string',
                        'applyOnAllPlayers' => 'bool'
                    ],
                    'loose' => 'array',
                    'withValueEvent' => 'array'
                ],
                'assets' => [
                    'cards' => [
                        '{{card_id}}' => [
                            'id' => 'number',
                            'value' => 'number',
                            'type' => 'string',
                            'addedAttributs' => [
                                'couleur' => 'string'
                            ]
                        ]
                    ],
                    'gains' => 'array',
                    'roles' => 'array'
                ]
            ],
            'token' => true
        ],  [
            'name' => "Get creator's game by id",
            'route' => '/api/game/{id}',
            'methode' => 'GET',
            'description' => "L'utilisateur récupère un jeu qu'il a créé, par son identifiant.",
            'body' => null,
            'sendBack' => [
                'id' => 'number',
                'name' => 'string',
                'image' => 'null',
                'isPublic' => 'bool',
                'description' => 'string',
                'averageNotes' => 'number',
                'notes' => 'array',
                'playerCount' => 'number',
                'gameCount' => 'number',
                'types' => 'string',
                'editionHistory' => 'array',
                'globalValue' => 'array',
                'globalValueStatic' => [
                    'Joueurs' => [
                        'type' => 'string',
                        'defaultValue' => 'number',
                        'value' => 'string',
                        'display' => 'bool'
                    ]
                ],
                'playerGlobalValue' => 'array',
                'params' => [
                    'globalGame' => [
                        'jeuSolo' => 'bool',
                        'playersCanJoin' => 'bool',
                        'minPlayer' => 'number',
                        'maxPlayer' => 'number',
                        'autoriseSpectator' => 'bool'
                    ],
                    'rendering' => [
                        'menu' => [
                            'template' => 'number',
                            'backgroundImage' => 'null'
                        ],
                        'game' => [
                            'template' => 'number',
                            'backgroundImage' => 'null',
                            'displayCountAdversaryHandDeck' => 'bool',
                            'displayHandDeck' => 'bool',
                            'displayStatistics' => 'bool',
                            'displayChat' => 'bool'
                        ]
                    ],
                    'tours' => [
                        'activation' => 'bool',
                        'sens' => 'string',
                        'startNumber' => 'number',
                        'firstPlayer' => 'string',
                        'firstPlayerValue' => 'null',
                        'maxTour' => 'number',
                        'actionOnlyAtPlayerTour' => 'bool',
                        'endOfTour' => 'array',
                        'actions' => [
                            [
                                'name' => 'string',
                                'id' => 'number',
                                'condition' => 'string',
                                'appearAtPlayerTurn' => 'bool',
                                'withValue' => 'array',
                                'actionOnDeck' => 'bool',
                                'actionOnDiscardDeck' => 'bool'
                            ]
                        ],
                        'actionsAtEnd' => 'number'
                    ],
                    'manches' => [
                        'max' => 'null',
                        'sens' => 'string',
                        'startNumber' => 'number'
                    ],
                    'cards' => [
                        'activeHandDeck' => 'bool',
                        'activPersonalHandDeck' => 'bool',
                        'activPersonalHandDiscard' => 'bool',
                        'activeDiscardDeck' => 'bool',
                        'discard' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ],
                            'activation' => 'bool',
                            'renderTheLastDiscardedCard' => 'bool'
                        ],
                        'pickOnDeck' => [
                            'quantity' => [
                                'min' => 'null',
                                'max' => 'null'
                            ]
                        ],
                        'activeCardAsGain' => 'bool',
                        'handDeck' => [
                            'activation' => 'bool',
                            'visibility' => 'string'
                        ],
                        'cardBoard' => 'array',
                        'deck' => [
                            'activation' => 'bool',
                            'renderTheNextDeckCard' => 'bool'
                        ]
                    ],
                    'roles' => [
                        'activation' => 'bool'
                    ],
                    'gain' => [
                        'activation' => 'bool',
                        'groupPot' => 'bool'
                    ]
                ],
                'events' => [
                    'demons' => [
                        [
                            'id' => 'number',
                            'condition' => 'string',
                            'name' => 'string',
                            'events' => 'array',
                            'boucle' => 'null'
                        ]
                    ],
                    'events' => [
                        [
                            'id' => 'number',
                            'name' => 'string',
                            'condition' => 'string',
                            'event' => 'object',
                            'boucle' => 'string'
                        ]
                    ],
                    'win' => [
                        'boucle' => 'string',
                        'condition' => 'string',
                        'applyOnAllPlayers' => 'bool'
                    ],
                    'loose' => 'array',
                    'withValueEvent' => 'array'
                ],
                'assets' => [
                    'cards' => [
                        '{{card_id}}' => [
                            'id' => 'number',
                            'value' => 'number',
                            'type' => 'string',
                            'addedAttributs' => [
                                'couleur' => 'string'
                            ]
                        ]
                    ],
                    'gains' => 'array',
                    'roles' => 'array'
                ]
            ],
            'token' => true
        ],
        [
            'name' => 'Remove game',
            'route' => '/api/game/remove/{id}',
            'methode' => 'DELETE',
            'description' => 'Supprime un jeu.',
            'body' => null,
            'sendBack' => ['message' => 'ok'],
            'token' => true
        ],
        [
            'name' => 'Edit game',
            'route' => '/api/game/edit/{id}',
            'methode' => 'PUT',
            'description' => 'Modifie un jeu existant. Les champs modifiables sont : name, description, types, isPublic, playerGlobalValue, globalValueStatic, editionHistory, globalValue, params, EventEvents, EventDemons, EventWin, EventLoose, EventWithValueEvents, assetsCard, assetsGain, roles.',
            'body' => [
                'name' => 'string (NOT NULL)',
                'description' => 'string (NOT NULL)',
                'types' => 'string (NOT NULL)',
                'isPublic' => 'bool (NOT NULL)',
                'playerGlobalValue' => 'array (optionnel)',
                'globalValueStatic' => 'array (optionnel)',
                'editionHistory' => 'array (optionnel)',
                'globalValue' => 'array (optionnel)',
                'params' => 'array (optionnel)',
                'EventEvents' => 'array (optionnel)',
                'EventDemons' => 'array (optionnel)',
                'EventWin' => 'array (optionnel)',
                'EventLoose' => 'array (optionnel)',
                'EventWithValueEvents' => 'array (optionnel)',
                'assetsCard' => 'array (optionnel)',
                'assetsGain' => 'array (optionnel)',
                'roles' => 'array (optionnel)'
            ],
            'sendBack' => ['game' => 'object'],
            'token' => true
        ],
        [
            'name' => 'Upload game image',
            'route' => '/api/game/upload-image/{id}',
            'methode' => 'POST',
            'description' => 'Ajoute ou remplace l’image d’un jeu.',
            'body' => ['file' => 'image (NOT NULL)'],
            'sendBack' => ['filename' => 'string'],
            'token' => true
        ]
    ],
    'Note' => [
        [
            'name' => 'Note page',
            'route' => '/note',
            'methode' => 'GET',
            'description' => 'Affiche la page de notes.',
            'body' => null,
            'sendBack' => null,
            'token' => false
        ]
    ],
    'Home' => [
        [
            'name' => 'Home page',
            'route' => '/',
            'methode' => 'GET',
            'description' => 'Page d’accueil.',
            'body' => null,
            'sendBack' => null,
            'token' => false
        ],
        [
            'name' => 'Mentions légales',
            'route' => '/mentions-legales',
            'methode' => 'GET',
            'description' => 'Page des mentions légales.',
            'body' => null,
            'sendBack' => null,
            'token' => false
        ]
    ],
    'Security' => [
        [
            'name' => 'Login',
            'route' => '/login',
            'methode' => 'POST',
            'description' => 'Permet de se connecter. Nécessite email et mot de passe.',
            'body' => [
                'email' => 'string (NOT NULL)',
                'password' => 'string (NOT NULL)'
            ],
            'sendBack' => [
                'token' => 'string'
            ],
            'token' => false
        ],
        [
            'name' => 'Logout',
            'route' => '/logout',
            'methode' => 'GET',
            'description' => 'Déconnecte l’utilisateur.',
            'body' => null,
            'sendBack' => null,
            'token' => false
        ]
    ]
];



      $currentRoutePath = $request->query->get('route');

   
    
    $currentRoute = null;
     foreach ($routes as $categoryName => $endpoints) {
        foreach ($endpoints as $endpoint) {
            if ($endpoint['route'] === $currentRoutePath) {
                $currentRoute = $endpoint;
                break 2;
            }
        }
    }

    return $this->render('home/documentation.html.twig', [
        
            "routes" => $routes,
      
        "currentRoute" => $currentRoute
    ]);
}


}
