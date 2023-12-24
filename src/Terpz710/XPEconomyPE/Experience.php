<?php

namespace Terpz710\XPEconomyPE;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\ExperienceManager;

use Terpz710\XPEconomyPE\Command\XPCommand;

class Experience extends PluginBase implements Listener {

    /** @var Config */
    private $playerData;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->playerData = new Config($this->getDataFolder() . "playerData.json", Config::JSON);
        
        $this->getServer()->getCommandMap()->register("exp", new XPCommand($this));
    }

    public function onDisable(): void {
        $this->playerData->save();
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $playerName = $player->getName();
        if ($this->existPlayer($playerName)) {
            $exp = $this->getPlayerExp($playerName);
            $this->setPlayerExpLevel($player, $exp);
            $this->getLogger()->info($playerName . " has " . $exp . " EXP!"); // Manual security check!
        } else {
            $this->createPlayer($playerName);
            $this->getLogger()->info("Created a new profile for " . $playerName . "!"); // Very usless but cool.
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $playerName = $player->getName();
        $exp = $this->getPlayerExpLevel($player);
        $this->setPlayerExp($playerName, $exp);
    }

    public function getPlayerName(Player $player): string {
        return $player->getName();
    }

    public function existPlayer(string $playerName): bool {
        return $this->playerData->exists($playerName);
    }

    public function createPlayer(string $playerName): void {
        $this->playerData->set($playerName, ["exp" => 0]);
        $this->playerData->save();
    }

    public function addExp(Player $player, int $amount): bool {
        $playerName = $this->getPlayerName($player);
        $currentExp = $this->getPlayerExp($playerName);
        $newExp = $currentExp + $amount;
        $this->setExp($playerName, $newExp);
        $this->setPlayerExpLevel($player, $newExp);
        return true;
    }

    public function removeExp(string $playerName, int $amount): bool {
        $currentExp = $this->getPlayerExp($playerName);
        $newExp = max(0, $currentExp - $amount);
        $this->setExp($playerName, $newExp);
        return true;
    }

    public function setExp(string $playerName, int $amount): void {
        $this->playerData->setNested($playerName . ".exp", $amount);
    }

    public function getPlayerExp(string $playerName): int {
        return $this->playerData->getNested($playerName . ".exp", 0);
    }

    public function setPlayerExpLevel(Player $player, int $level): void {
        $player->getXpManager()->setXpAndProgress($this->calculateExpFromLevel($level));
    }

    public function getPlayerExpLevel(Player $player): int {
        return $this->calculateLevelFromExp($player->getXpManager()->getCurrentTotalXp());
    }

    private function calculateExpFromLevel(int $level): float {
        return (2 * $level ** 3) + (5 * $level ** 2) + (100 * $level);
    }

    private function calculateLevelFromExp(float $exp): int {
        $level = 0;
        $requiredExp = 0;

        while ($exp >= $requiredExp) {
            $level++;
            $requiredExp += $this->calculateExpFromLevel($level);
        }

        return max(0, $level - 1);
    }

    public function getTopPlayers(int $limit = 5): array {
    $players = [];

    foreach ($this->playerData->getAll() as $playerName => $data) {
        $players[$playerName] = $data['exp'];
    }

    arsort($players);

    $topPlayers = [];
    $position = 1;

    foreach ($players as $playerName => $exp) {
        $topPlayers[$position] = [
            'name' => $playerName,
            'exp' => $exp,
        ];

        $position++;

        if ($position > $limit) {
            break;
            }
        }

    return $topPlayers;
    }
}
