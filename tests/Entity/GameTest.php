// tests/Entity/GameTest.php
namespace App\Tests\Entity;

use App\Entity\Deck;
use App\Entity\Game;
use App\Entity\Note;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        $this->game = new Game();
    }

    public function testGameBasicProperties(): void
    {
        $this->game->setName('Poker Texas Holdem');
        $this->game->setDescription('Configuration classique du poker');
        $this->game->setPlayerCount(8);
        $this->game->setGameCount(0);
        $this->game->setIsPublic(true);
        $this->game->setTypes('cartes');

        $this->assertEquals('Poker Texas Holdem', $this->game->getName());
        $this->assertEquals('Configuration classique du poker', $this->game->getDescription());
        $this->assertEquals(8, $this->game->getPlayerCount());
        $this->assertEquals(0, $this->game->getGameCount());
        $this->assertTrue($this->game->isPublic());
        $this->assertEquals('cartes', $this->game->getTypes());
    }

    public function testGameJsonConfigurations(): void
    {
        $params = ['globalGame' => ['maxPlayer' => 8, 'allowSpectator' => true]];
        $triggers = [['id' => 1, 'name' => 'startTurn', 'condition' => 'true']];

        $this->game->setParams($params);
        $this->game->setEventTriggers($triggers);

        $this->assertSame($params, $this->game->getParams());
        $this->assertSame($triggers, $this->game->getEventTriggers());
    }

    public function testGameRelations(): void
    {
        $user = new User();
        $deck = new Deck();
        $note = new Note();

        $this->game->setCreator($user);
        $this->game->setDeckUsed($deck);
        $this->game->addNote($note);

        $this->assertSame($user, $this->game->getCreator());
        $this->assertSame($deck, $this->game->getDeckUsed());
        $this->assertTrue($this->game->getNotes()->contains($note));

        $this->game->removeNote($note);
        $this->assertFalse($this->game->getNotes()->contains($note));
    }
}