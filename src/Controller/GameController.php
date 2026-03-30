<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GameRepository;
use App\Service\ImageService;
use App\Service\TypeService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface; 
 
final class GameController extends AbstractController
{
   private ImageService $imageService;
  public  function __construct(ImageService $imageService){
        $this->imageService = $imageService;
    }

#[Route('/api/test-token', name: 'app_test_token')]
public function testToken(Request $request): Response
{
    // Récupère le header brut
    $authHeader = $request->headers->get('Authorization');
    dd(
        $request->headers->get('Authorization'),
        $this->container->get('security.token_storage')->getToken()
    );
}

     #[Route('/games', name: 'get_games')]
    public function getGames( GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findBy(["isPublic" => true]);
        $gamesToSend = [];
        foreach($games as $game){ 
            $gameToSend = $this->getGameObject($game);
            unset($gameToSend["events"]);
            unset($gameToSend["assets"]);
            unset($gameToSend["playerGlobalValue"]);
            unset($gameToSend["globalValue"]);
            unset($gameToSend["editionHistory"]);  
            $gamesToSend[] = $gameToSend;
        }

       return $this->json(  $gamesToSend, 200);
    }
    #[Route('/api/my/games', name: 'get_games_of_user')]
    public function getMyGames( GameRepository $gameRepository,  EntityManagerInterface $manager): Response
    { 
        if(count($this->getUser()->getGames()) === 0){
            $game = new Game();
            $game->setName("Default Poker");
            $game->setDescription("Le poker est un jeu de cartes mêlant stratégie et psychologie. L'objectif est de remporter le pot, soit avec la main la plus forte (de la paire à la quinte flush), soit par le bluff en poussant les adversaires à abandonner avant l'abattage final.");
            $game->setPlayerCount(0);
            $game->setGameCount(0);
            $game->setIsPublic(true);
            $game->setCreator($this->getUser());
            $game->setTypes(implode(",",["strategy", "luck", "smart"]));
            $game->setEditionHistory([
                [
                    "id"=> "1",
                    "evenement"=> "Dragon de feu",
                    "action"=> "Carte créée",
                    "date_relative"=> "2025-10-09T20:10",
                ],
                [
                "id"=> 2,
                "evenement"=> "Deck Arcane",
                "action"=> "Jeu modifié",
                "date_relative"=> "2025-10-10T10:15",
                  ]
            ]);
            $game->setglobalValue(  [
                "smallBlind" => ["type" => "number", "value" => 1,"id"=>1],
               "currentBet" => ["type" => "number", "value" => 0,"id"=>3]
              
            ] );
            
            $game->setPlayerGlobalValue([
                "currentBet" => [
                    "type" => "number",
                    "value" => 0,
                    "id" => 1,
                    "display" => true,
                ],
                "attachedEventForTour" => [
                    "type" => "array",
                    "value" => [],
                    "id" => 2,
                    "display" => false,
                ]
                
            ]);
            $game->setglobalValueStatic([]);
            $game->setParams([
                'globalGame' => [
                    'jeuSolo' => false,
                    'playersCanJoin' => false,
                    'minPlayer' => 2,
                    'maxPlayer' => 5,
                ],
                'rendering' => [
                    'menu' => [
                        'template' => 1,
                        'backgroundImage' => null,
                    ],
                    'game' => [
                        'template' => 1,
                        'backgroundImage' => null,
                        'displayHandDeck'=>true,
                        'displayCountAdversaryHandDeck'=>true,
                        'displayStatistics'=>true,
                        'displayHistory'=>true,
                        'displayTimer'=>false,
                        'displayChat'=>true,
                    ],
                ],
                'tours' => [
                    'activation' => true,
                    'sens' => "incrementation", // or decrementation
                    'startNumber' => 0,
                    'timerActivation'=>false,
                    'duration'=>0,
                    'maxTour' => 3,
                    'actionOnlyAtPlayerTour' => true,
                    'endOfTour' => ["allPlayersHasPlayed/endOfTour"],
                    'actions' => [
                        [
                            'id'=>1,
                            'name' => "Se coucher",
                            'condition' => null,
                            
                            'appearAtPlayerTurn' => true,
                            'withValue' => [
                                ['id' => 7,  'player' => "{currentPlayer}"],
                                ['id' => 1, 'inputBool' => true, 'player' => "{currentPlayer}"],
                            ],

                        ],
                        [
                            'id'=>2,
                            'name' => "miser",
                            
                            'type' => "askPlayer",
                            'appearAtPlayerTurn' => true,
                            'condition' => "comp({currentPlayer#gain#1};isSuperiorNumber;{currentBet})",
                            'withValue' => [
                                ['id' => 11, 'player' => "{currentPlayer}"],
                            ],
                        ],
                        [
                                'id'=>3,
                            'name' => "suivre",
                            'appearAtPlayerTurn' => true,
                            'condition' => "exp(comp({currentPlayer#currentBet};isNotEqualNumber;{currentBet})&&comp({currentPlayer#gain#1};isSuperiorNumber;calc({currentBet}-{currentPlayer#currentBet})))",
                            'return' => "{currentPlayer}",
                            'withValue' => [
                                [
                                    'id' => 14,
                                    'player' => "{currentPlayer}",
                                    'inputNumber' => "calc({currentBet}-{currentPlayer#currentBet})",
                                ],
                                ['id' => 1, 'inputBool' => true, 'player' => "{currentPlayer}"],
                            ],
                        ],
                        [
                            'id'=>4,
                            'name' => "Check",
                            'appearAtPlayerTurn' => true,
                            'condition' => "comp({currentPlayer#currentBet};isEqualNumber;{currentBet})",
                            
                            'withValue' => [
                                ['id' => 1, 'inputBool' => true, 'player' => "{currentPlayer}"],
                            ],
                        ],
                        [
                            'id'=>5,
                            'name' => "Tapis",
                            'appearAtPlayerTurn' => true,
                            'condition' => "exp(comp({currentPlayer#currentBet};isInferiorOrEqual;{currentBet})&&comp({currentPlayer#gain#1};isSuperiorNumber;0))",
                            
                            'withValue' => [
                                [
                                    'id' => 14,
                                    'type' => "withValueEvent",
                                    'player' => "{currentPlayer}",
                                    'inputNumber' => "{currentPlayer#gain#1}",
                                ],
                                ['id' => 3, 'inputNumber' => "{currentPlayer#currentBet}"],
                                ['id' => 1, 'inputBool' => true, 'player' => "{currentPlayer}"],
                                ['id' => 8, 'inputBool' => true],
                            ],
                        ],
                    ]
                ],
                'manches' => [ 
                    'max' => null,
                    'sens'=> "incrementation", // or decrementation
                    'startNumber'=>0
                ],
                'cards' => [
                    'activeHandDeck' => true,
                    'activPersonalHandDeck' => true,
                    'activPersonalHandDiscard' => true,
                    'activeDiscardDeck' => false,
                    'discard' => [
                        'quantity' => ['min' => null, 'max' => null],
                    ],
                    'pickOnDeck' => [
                        'quantity' => ['min' => null, 'max' => null],
                    ],
                    'activeCardAsGain' => true,
                    'handDeck' => [
                        'activation' => true,
                        'visibility' => "nobody",
                    ],
                    'cardBoard' => [],
                ],
                'gain' => [
                    'activation' => true,
                    'groupPot' => true,
                ],
                'roles'=>[
                    "activation" => true,
                ]
            ]);
            $game->setEventWin([]);
            $game->setEventLoose([]);
            $game->setEventDemons(
                [ 
                // La partie se lance après que tous les démons se soient activés si etat != start
                [

                    // Pas besoin d'une liste de conditions, on met une comp "or" si plusieurs conditions d'exec
                    'id' => 1,
                    'name'=>"Quand on arrive au tour 4 et que tous les joueurs ont joué",
                    'condition' => "exp(comp({tour};isEqualNumber;4)&&allPlayersHasPlayed/endOfTour)",
                    'events' => [13, 15, 16, 302],
                    // 13 récupération des mises
                    // 15 lancer la verification des cartes
                    // 16 reset global bet
                    // 302 change manche
                ],

                [
                    'id' => 2,
                    'name'=>"Quand on arrive au tour 5",
                    'condition' => "exp(comp({tour};isEqualNumber;5)&&onChangeTour)",
                    'events' => [],
                    // Lancer la verification des cartes
                ],

                [
                    'id' => 3,
                    'name'=>"Quand un tour change",
                    'condition' => "onChangeTour",
                    'events' => [13, 18],
                    // Récupérer les mises centrales
                ],

                [
                    'id' => 4,
                    'name'=>"Au début de la partie",
                    'condition' => "startOfGame",
                    'events' => [8, 4],
                    'removeAfterUse' => true,
                    // Melanger les cartes
                    // Distribution des gains
                    // La distribution des cartes se fait au début de la manche
                ],

                [
                    'id' => 5,
                    "name" => "Début de manche",
                    'condition' => "eachStartOfManche",
                    'events' => [3, 5, 6, 7, 9, 8, 10, 14],
                    // 3 reset events 'coucher'
                    // 5 changer le joueur de depart
                    // 6 pose de la petite blinde
                    // 7 pose de la grosse blinde
                    // 9 rassembler les paquets
                    // 8 melanger la pioche
                    // 10 distribuer les cartes
                    // 14 mettre le status de tous les joueurs en "non joués"
                ],

                [
                    'id' => 6,
                    'name' => "Quand tous les joueurs ont passé leur tour",
                    'boucle' => "{allPlayersInGame}",
                    'condition' => "exp(comp({playerBoucle};samePlayer;{currentPlayer})||comp({playerBoucle#attachedEventForTour};contain;<<skipPlayerTour>>))",
                    'events' => [13, 17, 18, 302],
                    // Relance eachStartOfManche
                    // Récupérer les mises centrales
                    // 18 remettre current bet à 0
                ],

                // Créé automatiquement
                [
                    'id' => 7,
                    'name'=>"Changement de tour",
                    'condition' => "allPlayersHasPlayed/endOfTour", // ALSO END OF TOUR
                    'events' => [14, 301],
                    // 14 mettre le status de tous les joueurs en "non joués"
                    // 301 changement de tour
                ],
            ] 
            );
            $game->setEventEvents([
                // default events
                [
                    'id' => 300,
                    'name' => "win",
                    'condition' => null,
                    'event' => [
                        'for' => "{currentPlayer}",
                        'give' => null,
                        'attachedEventForTour' => null,
                        'action' => "win",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 301,
                    'name' => "Change tour",
                    'loadMessage' => "Changement de tour...",
                    'condition' => null,
                    'event' => [
                        'for' => null,
                        'give' => null,
                        'attachedEventForTour' => null,
                        'action' => "endOfTour",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 302,
                    'name' => "Change manche",
                    'loadMessage' => "Changement de manche...",
                    'condition' => null,
                    'event' => [
                        'for' => null,
                        'give' => null,
                        'attachedEventForTour' => null,
                        'action' => "changeManche",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 3,
                    'name' => "Faire revenir tous les joueurs dans la partie",
                    'condition' => null,
                    'loadMessage' => "Réintégration des joueurs...",
                    'boucle' => "{allPlayersInGame}",
                    'event' => [
                        'for' => "{playerBoucle}",
                        'give' => null,
                        'action' => "removeAllAtachedEventsForTour",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 4,
                    'name' => "Distribute all gains",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'loadMessage' => "Distribution des gains...",
                    'event' => [
                        'for' => "{playerBoucle}",
                        'give' => [
                            "{gain#1}" => 6250,
                        ],
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 5,
                    'name' => "Changer le joueur qui commence",
                    'loadMessage' => "Changement du joueur de départ...",
                    'event' => [
                        'for' => null,
                        'give' => null,
                        'action' => "changeStartingPlayer",
                        'value' => "next",
                    ],
                ],
                [
                    'id' => 6,
                    'name' => "Pose des petites blind ",
                    'loadMessage' => "Pose de la petite blinde...",
                    'condition' => null,
                    'event' => [
                        'from' => "getPlayer(calc({startPlayer#position}+1))",
                        'for' => "{getPlayer(calc({startPlayer#position}+1))#currentBet}",
                        'give' => [
                            "{gain#1}" => "{smallBlind}",
                        ],
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 7,
                    'name' => "Pose de la grosse blind ",
                    'loadMessage' => "Pose de la grosse blinde...",
                    'condition' => null,
                    'event' => [
                        'from' => "getPlayer(calc({startPlayer#position}+2))",
                        'for' => "{getPlayer(calc({startPlayer#position}+2))#currentBet}",
                        'give' => [
                            "{gain#1}" => "calc(2*{smallBlind})",
                        ],
                        'action' => null,
                        'value' => null,
                        'params' => [
                            'ifFromStackDoesNotHaveRessource' => [
                                'giveAllRessourcePossible' => false,
                                'doEvents' => [],
                            ],
                        ],
                        'withValue' => [
                            [
                                'id' => 3,
                                'inputNumber' => "{getPlayer(calc({startPlayer#position}+2))#currentBet}",
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 8,
                    'name' => "Melanger le jeu",
                    'loadMessage' => "Mélange du jeu...",
                    'condition' => null,
                    'event' => [
                        'from' => null,
                        'for' => "{deck}",
                        'give' => null,
                        'action' => "shuffle",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 9,
                    'name' => "Rassembler les jeux",
                    'loadMessage' => null,
                    'condition' => null,
                    'event' => [
                        'from' => "{discardDeck}",
                        'for' => "{deck}",
                        'give' => [
                            "{cards}" => "*",
                        ],
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 10,
                    'name' => "Distribuer",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'loadMessage' => "Distribution des cartes aux joueurs...",
                    'event' => [
                        'for' => "{playerBoucle#handDeck}",
                        'from' => "{deck}",
                        'give' => [
                            "{cards}" => 2,
                        ],
                        'attachedEventForTour' => null,
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 13,
                    'name' => "Recuperer les mises",
                    'loadMessage' => "Récupération de la mise...",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'event' => [
                        'from' => "{playerBoucle#currentBet}",
                        'for' => "{groupPot}",
                        'give' => [
                            "{gain#1}" => "{playerBoucle#currentBet}",
                        ],
                        'attachedEventForTour' => null,
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 14,
                    'name' => "change play status to all player at start of game",
                    'loadMessage' => "Réintégration des joueurs...",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'event' => [
                        'for' => "{playerBoucle#hasPlayed}",
                        'action' => "updateGlobalValue",
                        'value' => "false",
                    ],
                ],
                [
                    'id' => 15,
                    'name' => "Verification des combinaisons",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'loadMessage' => "Analyse des cartes...",
                    'event' => [
                        'for' => "{currentPlayer#handœCardDeck#type=french_standard}",
                        'action' => "verificationCards",
                        'return' => "{winnersPlayers}",
                        'withValue' => [
                            ['id' => 6, 'inputPlayers' => "{winnersPlayers}"],
                        ],
                    ],
                ],
                [
                    'id' => 16,
                    'name' => "Reset global bet",
                    'loadMessage' => "Réinitialisation de la mise globale...",
                    'condition' => null,
                    'event' => [
                        'for' => "{groupPot#gain#1}",
                        'action' => "updateGlobalValue",
                        'value' => 0,
                    ],
                ],
                [
                    'id' => 17,
                    'name' => "Donner les mises à un joueur",
                    'loadMessage' => "Réinitialisation de la mise globale...",
                    'condition' => null,
                    'event' => [
                        'from' => "{groupPot}",
                        'for' => "{currentPlayer}",
                        'give' => [
                            "{gain#1}" => "*",
                        ],
                    ],
                ],
                [
                    'id' => 18,
                    'name' => "Reset current bet",
                    'condition' => null,
                    'event' => [
                        'for' => "{currentBet}",
                        'action' => "updateGlobalValue",
                        'value' => 0,
                    ],
                ],
            ]);
           
            $game->setEventWithValueEvents([
                // Événements suite à une action qui concerne un joueur spécifique ou des variables

                // Combinaisons poker (Vérifications)
                [
                    'id' => 300,
                    'name' => "Verifier une suite",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-straight",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 301,
                    'name' => "Verifier une suite royale",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-royal-straight",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 302,
                    'name' => "Verifier quinte flush",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-straight-flush",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 303,
                    'name' => "Verifier carre",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-four-of-a-kind",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 304,
                    'name' => "Verifier full",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-full-house",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 305,
                    'name' => "Verifier couleur",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-flush",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 306,
                    'name' => "Verifier brelan",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-three-of-a-kind",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 307,
                    'name' => "Verifier deux paires",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-two-pair",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 308,
                    'name' => "Verifier une paire",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-one-pair",
                        'value' => true,
                    ],
                ],
                [
                    'id' => 309,
                    'name' => "Verifier carte haute",
                    'condition' => null,
                    'boucle' => "{inputCardList#type=french_standard}",
                    'event' => [
                        'condition' => null,
                        'for' => ["card"],
                        'give' => null,
                        'action' => "french-card-verify-high-card",
                        'value' => true,
                    ],
                ],

                // Gestion du statut et des mises
                [
                    'id' => 1,
                    'name' => "change status of 'hasPlayed' for one player",
                    'condition' => null,
                    'boucle' => null,
                    'event' => [
                        'for' => "{player#hasPlayed}",
                        'give' => null,
                        'action' => "updateGlobalValue",
                        'value' => "{inputBool}",
                    ],
                ],
                [
                    'id' => 2,
                    'name' => "when player bet or follow bet ",
                    'condition' => null,
                    'boucle' => null,
                    'event' => [
                        'from' => "{player}",
                        'for' => "{player#currentBet}",
                        'give' => [
                            "{gain#1}" => "{inputNumber}",
                        ],
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 3,
                    'name' => "updateGlobalBet",
                    'condition' => null,
                    'boucle' => null,
                    'event' => [
                        'for' => "{currentBet}",
                        'action' => "updateGlobalValue",
                        'value' => "{inputNumber}",
                    ],
                ],
                [
                    'id' => 4,
                    'name' => "change play status to all player when player bet",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'event' => [
                        'condition' => "exp(comp({playerBoucle#attachedEventForTour};notContain;<<skipPlayerTour>>)&&comp({playerBoucle};differentPlayer;{currentPlayer}))",
                        'for' => "{playerBoucle#hasPlayed}",
                        'action' => "updateGlobalValue",
                        'value' => "false",
                    ],
                ],
                [
                    'id' => 14,
                    'name' => "suivre une mise",
                    'condition' => null,
                    'boucle' => null,
                    'event' => [
                        'from' => "{player}",
                        'for' => "{player#currentBet}",
                        'give' => [
                            "{gain#1}" => "{inputNumber}",
                        ],
                    ],
                ],
                [
                    'id' => 11,
                    'name' => "Miser",
                    'condition' => null,
                    'event' => [
                        'for' => "{currentPlayer}",
                        'action' => "askPlayer",
                        'requiresInput' => [
                            'type' => "number",
                            'label' => "Choisissez le montant à miser",
                            'min' => 1,
                            'max' => "playerMaxGain",
                            'unit' => "gain#1",
                            'return' => ["{currentPlayer}", "{insertedValue}"],
                        ],
                        'withValue' => [
                            ['id' => 2, 'inputNumber' => "{insertedValue}"],
                            ['id' => 3, 'inputNumber' => "{currentPlayer#currentBet}"],
                            ['id' => 1, 'inputBool' => true, 'player' => "{currentPlayer}"],
                            ['id' => 4, 'inputBool' => true],
                        ],
                        'attachedEventForTour' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 5,
                    'name' => "distribution de carte dans le pot",
                    'condition' => null,
                    'boucle' => null,
                    'event' => [
                        'from' => "{deck}",
                        'for' => "{boardDeck}",
                        'give' => [
                            "card#comp({currentCard#type};isEqualString;<<french_standard>>)" => "exp(comp({tour};isEqualNumber;1;return;1)||comp({tour};isEqualNumber;2;return;1)||comp({tour};isEqualNumber;3;return;3))",
                        ],
                        'action' => null,
                        'value' => "true",
                    ],
                ],
                [
                    'id' => 6,
                    'name' => "distribution des gains  ",
                    'condition' => null,
                    'boucle' => "{inputPlayers}",
                    'event' => [
                        'from' => "{groupPot}",
                        'for' => "{bouclePlayer#gain#1}",
                        'give' => [
                            "{gain#1}" => "%",
                        ],
                        'action' => null,
                        'value' => null,
                    ],
                ],
                [
                    'id' => 7,
                    'name' => "Se coucher",
                    'condition' => null,
                    'event' => [
                        'for' => "{currentPlayer}",
                        'give' => null,
                        'action' => "skipPlayerTour",
                        'value' => null,
                    ],
                ],
                [
                    'id' => 8,
                    'name' => "change play status to all player when player bet",
                    'condition' => null,
                    'boucle' => "{allPlayersInGame}",
                    'event' => [
                        'condition' => "exp(exp(comp({playerBoucle#attachedEventForTour};notContain;<<skipPlayerTour>>)&&comp({playerBoucle};differentPlayer;{currentPlayer}))&&comp({playerBoucle#currentBet};isInferiorNumber;{currentBet}))",
                        'for' => "{playerBoucle#hasPlayed}",
                        'action' => "updateGlobalValue",
                        'value' => "false",
                    ],
                ]
            ]);
            $colors = [
                'pique'   => range(1, 13),
                'treffle' => range(14, 26),
                'coeur'   => range(27, 39),
                'carreau' => range(40, 52),
            ];

            $cardsConfig = [];

            foreach ($colors as $colorName => $range) {
                foreach ($range as $index => $id) {
                    // La valeur de la carte va de 1 à 13 pour chaque couleur
                    $value = $index + 1; 

                    $cardsConfig[$id] = [
                        'id' => $id,
                        'value' => $value,
                        'type' => "french_standard",
                        'addedAttributs' => [
                            'couleur' => $colorName
                        ]
                    ];
                }
            }

            // Pour l'insertion dans ton entité Symfony
            $game->setAssetsCard($cardsConfig);
            $game->setAssetsGain([
                    [
                        'id' => 1,
                        'nom' => "jetons",
                        'value' => null,
                        'value_numérique' => 1,
                        'quantite' => null, // infini
                    ],
                ]);
            $game->setRoles([
                [
                    'nom' => "dealer",
                    'attribution' => "{startPlayer}",
                ],
            ]); 
            $manager->persist($game)  ;
            $manager->flush(); 
        };
        $games = $gameRepository->findBy(["creator"=>$this->getUser()]);
            $gamesToSend = [];
      
        foreach($games as $game){ 
            $gameToSend = $this->getGameObject($game);
            unset($gameToSend["events"]);
            unset($gameToSend["assets"]);
            unset($gameToSend["playerGlobalValue"]);
            unset($gameToSend["globalValue"]);
            unset($gameToSend["editionHistory"]);  
            $gamesToSend[] = $gameToSend;
        }
        return $this->json(  $gamesToSend ,200, [],['groups'=>"games"] );
    }
     
    #[Route('/api/new/game', name: 'new_game')]
    public function newGame( GameRepository $gameRepository,  EntityManagerInterface $manager): Response
    { 
    
            $game = new Game();
            $game->setName("Default name");
            $game->setDescription("");
            $game->setPlayerCount(0);
            $game->setGameCount(0);
            $game->setIsPublic(true);
            
            $game->setCreator($this->getUser());
            $game->setTypes("");
            $game->setEditionHistory([ ]);
            $game->setglobalValue(  [ ] );
            
            $game->setglobalValueStatic([]);
            $game->setPlayerGlobalValue([  ]);
            $game->setParams([
                'globalGame' => [
                    'jeuSolo' => false,
                    'playersCanJoin' => false,
                    'minPlayer' => 2,
                    'maxPlayer' => 5,
                ],
                'rendering' => [
                    'menu' => [
                        'template' => 1,
                        'backgroundImage' => null,
                    ],
                     'game' => [
                        'template' => 1,
                        'backgroundImage' => null,
                        'displayHandDeck'=>true,
                        'displayCountAdversaryHandDeck'=>true,
                        'displayStatistics'=>true,
                        'displayHistory'=>true,
                        'displayTimer'=>false,
                        'displayChat'=>true,
                    ],
                ],
                'tours' => [
                    'activation' => true,
                    'sens' => "incrementation", // or decrementation
                    'startNumber' => 0,
                    'firstPlayer'=>'randomPlayer',
                    'firstPlayerValue'=>null,
                    'maxTour' => 3,
                    'actionOnlyAtPlayerTour' => true,
                    'endOfTour' => [],
                    'actions' => [  ],
                    'actionsAtEnd' => 0,
                ],
                 'manches' => [ 
                    'max' => null,
                    'sens'=> "incrementation", // or decrementation
                    'startNumber'=>0
                ],
                'cards' => [
                    'activeHandDeck' => true,
                    'activPersonalHandDeck' => true,
                    'activPersonalHandDiscard' => true,
                    'activeDiscardDeck' => false,
                    'discard' => [
                        'quantity' => ['min' => null, 'max' => null],
                    ],
                    'pickOnDeck' => [
                        'quantity' => ['min' => null, 'max' => null],
                    ],
                    'activeCardAsGain' => true,
                    'handDeck' => [
                        'activation' => true,
                        'visibility' => "nobody",
                    ],
                    'cardBoard' => [],
                ],
                'roles'=>[
                    "activation" => true,
                ],
                'gain' => [
                    'activation' => true,
                    'groupPot' => true,
                ]
            ]);

            $game->setEventDemons([]);
            $game->setEventEvents([

            ]);
            $game->setEventWin([
                "applyOnAllPlayers" => true,
                "allElementOfBoucleMustSatisyCondition"=>false,
                "manyPlayersCanBeWinner"=>true,
                "displayPoints"=>[
                    "activation"=>false

                ]

            ]);
            $game->setEventLoose([]);
           
            $game->setEventWithValueEvents([ ]); 
              $cardsConfig = [];
  $colors = [
                'pique'   => range(1, 13),
                'treffle' => range(14, 26),
                'coeur'   => range(27, 39),
                'carreau' => range(40, 52),
            ];
            foreach ($colors as $colorName => $range) {
                foreach ($range as $index => $id) {
                    // La valeur de la carte va de 1 à 13 pour chaque couleur
                    $value = $index + 1; 

                    $cardsConfig[$id] = [
                        'id' => $id,
                        'value' => $value,
                        'type' => "french_standard",
                        'addedAttributs' => [
                            'couleur' => $colorName
                        ]
                    ];
                }
            }

            $game->setAssetsCard($cardsConfig);
            $game->setAssetsGain([]);
            $game->setRoles([]);
            $manager->persist($game)  ;
            $manager->flush(); 
      
        return $this->json( ["id"=>$game->getId()] ,200 );
    }

    #[Route('api/game/{id}', name: 'get_game')]
    public function getGame(Game $game): Response
    {    
        if ($game->getCreator() == $this->getUser()){     
             return $this->json( $this->getGameObject($game) ,200, [],['groups'=>"game"] );
        }
        return $this->json(  null ,200, [],['groups'=>"games"] );
    }   

    #[Route('api/game/remove/{id}', name: 'remove_game', methods: ['DELETE'])]
    public function removeGame(Game $game, EntityManagerInterface $manager): Response
    {    
        if (!$game) {
            return $this->json(["message" => "Game not found."], 404);
        }
        if ($game->getCreator() == $this->getUser()){     
             $manager->remove($game);
             $manager->flush();
             return $this->json( ["message"=>"ok"] ,200 );
        }
        return $this->json(  null ,200, [],['groups'=>"games"] );
    }   
    

    #[Route('game/{id}', name: 'get_game_public')]
    public function getGameWithId(Game $game): Response
    {    
           return $this->json( $this->getGameObject($game) ,200, [],['groups'=>"game"] );
    }
 

    private function getGameObject($game){
        $averageNotes = 0; 
        $notes = [];
        foreach ($game->getNotes() as $note){
            $averageNotes += $note->getRate();
         $notes[] = [
            "id"=>$note->getId(),
            "rate"=>$note->getRate(),
            "comment"=>$note->getDescription(),
            "date"=>$note->getDate()->format("Y-m-d"),
         ] ;
        }
        if ($averageNotes > 0 ){
                $averageNotes = round($averageNotes/count($game->getNotes()) , 1) ;
        }
            
        return   [
        "id"=>$game->getId(), 
        "name"=>$game->getName(),
        "image"=>$game->getImage()? $this->imageService->getImageUrl($game->getImage(),    "game" ,'game_image') : null,
        "isPublic"=>$game->isPublic(),
        "description"=>$game->getDescription(),
        "averageNotes"=>$averageNotes, 
        "playerCount"=>$game->getPlayerCount(),
        "notes"=>$notes,
        "gameCount"=>$game->getGameCount(),
        "types"=>$game->getTypes(), 
        "editionHistory"=>$game->getEditionHistory(),
        "globalValue"=>$game->getglobalValue(),
        "globalValueStatic"=>$game->getglobalValueStatic() ?? [],
        "playerGlobalValue"=>$game->getPlayerGlobalValue(),
        "params"=>$game->getParams(),
        "events"=>[
            "demons"=>$game->getEventDemons(),
            "events"=>$game->getEventEvents(),
            "win"=>$game->getEventWin(),
            "loose"=>$game->getEventLoose(),
            "withValueEvent"=>$game->getEventWithValueEvents()
        ],
        "assets"=>[
            "cards"=>$game->getAssetsCard(),
            "gains"=>$game->getAssetsGain(),
            "roles"=>$game->getRoles(),
        ]
        ] ;

    }
     #[Route('api/game/edit/{id}', name: 'edit_game')]
    public function editGame(Game $game ,  SerializerInterface $serializer, EntityManagerInterface $manager, Request $request , TypeService $typeService): Response
    {    
     
        $gameEdited = $serializer->deserialize($request->getContent(), Game::class, 'json');
        $data = json_decode($request->getContent(), true);

        if (!$typeService->verify($gameEdited->getName(), "string")) {
            return $this->json(["message" => "Invalid name. (field : name, accepted : string)"], 406);
        };

        if (!$typeService->verify($gameEdited->getDescription(), "string")) {
            return $this->json(["message" => "Invalid description. (field : description, accepted : string)"], 406);
        };

        if (!$typeService->verify($gameEdited->getTypes(), "string")) {
            return $this->json(["message" => "Invalid types. (field : types, accepted : string)"], 406);
        };

        if (!$typeService->verify($gameEdited->isPublic(), "bool")) {
            return $this->json(["message" => "Visibility must be defined. (field : isPublic, value : true,false)"], 406);
        };
         
        if($gameEdited->getName()){
        $game->setName($gameEdited->getName());
        } 
        if ($gameEdited->getDescription()){
        $game->setDescription($gameEdited->getDescription());
        };
        if ($gameEdited->getTypes()){
        $game->setTypes($gameEdited->getTypes());
        };
        if ($gameEdited->isPublic() !== null){
        $game->setIsPublic($gameEdited->isPublic());
        }
        if( $gameEdited->getPlayerGlobalValue()){
        $game->setPlayerGlobalValue($gameEdited->getPlayerGlobalValue());
        };
        if( $gameEdited->getglobalValueStatic()){
        $game->setglobalValueStatic($gameEdited->getglobalValueStatic());
        };
        if( $gameEdited->getEditionHistory()){
        $game->setEditionHistory($gameEdited->getEditionHistory());
        };
        if ( $gameEdited->getglobalValue()){
        $game->setglobalValue($gameEdited->getglobalValue());
        };
        if ( $gameEdited->getParams()){
        $game->setParams($gameEdited->getParams());
        };
       
        if( $gameEdited->getEventEvents()){
        $game->setEventEvents($data["EventEvents"]);
        };
        if ( $gameEdited->getEventDemons()){
        $game->setEventDemons($data["EventDemons"]);
        };
        if ( $gameEdited->getEventWin()){
        $game->setEventWin($data["EventWin"]);
        };
        if ( $gameEdited->getEventLoose()){
        $game->setEventLoose($data["EventLoose"]);
        };
        if( $gameEdited->getEventWithValueEvents()){
        $game->setEventWithValueEvents($data["EventWithValueEvents"]);
        };
        if( $gameEdited->getAssetsCard()){
        $game->setAssetsCard($data["assetsCard"]);
        };
        if( $gameEdited->getAssetsGain()){
        $game->setAssetsGain($data["assetsGain"]);
        };
        if( $gameEdited->getRoles()){
        $game->setRoles($data["roles"]);
        };
        $manager->persist($game);
        $manager->flush();
        return $this->json( $this->getGameObject($game) ,200, [],['groups'=>"games"] );
        
    }

     #[Route('/game/{id}/one/more/player', name: 'add_player_count_in_game')]
    public function addPlayerCountInGame(Game $game ,  EntityManagerInterface $manager ): Response
    {    
     
        $game->setPlayerCount($game->getPlayerCount() + 1);
        $manager->persist($game);
        $manager->flush();
        return $this->json(["message"=> "ok"],200, []);
    }
    
     #[Route('/api/game/upload-image/{id}', name: 'app_image',methods: ['POST'])]
    public function index(Game $game, Request $request, EntityManagerInterface $em, Filesystem $filesystem): Response
    {
 

        $folder = $this->getParameter('images_directory') . '/'."game";    

        $oldImage = $game->getImage();
        
        if ($oldImage) {
            $oldPath = $folder.'/'.$oldImage; 
            if ($filesystem->exists($oldPath)) {
                $filesystem->remove($oldPath);
            }  
        } 

        $file = $request->files->get('file');

        $newFilename = uniqid().'.'.$file->guessExtension();
 
        
            $file->move($folder, $newFilename);
       
        $game->setImage($newFilename); 
 
        $em->persist($game);
        $em->flush();   
        return $this->json([
            'message' => 'Image ajoutée avec succès',
            'filename' => $newFilename
        ]);
    }
}
