<?php

namespace Terpz710\XPEconomyPE\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

use Terpz710\XPEconomyPE\Experience;

class XPCommand extends Command {

    /** @var Experience */
    private $plugin;

    public function __construct(Experience $plugin) {
        parent::__construct("exp", "XP Economy command", "/exp [see|pay|myxp|topxp|removexp|setxp|addxp]", ["xp"]);

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        if (count($args) === 0) {
            $sender->sendMessage("Usage: /exp [see|pay|myxp|topxp|removexp|setxp|addxp]");
            return false;
        }

        $subCommand = strtolower(array_shift($args));

        switch ($subCommand) {
            case "see":
                $this->seeExp($sender, $args);
                break;

            case "pay":
                $this->payExp($sender, $args);
                break;

            case "myxp":
                $this->plugin->checkExp($sender);
                break;

            case "topxp":
                $this->topExp($sender);
                break;

            case "removexp":
                if (!$sender->hasPermission("xpeconomype.cmd.removeexp")) {
                        return true;
                    }
                $this->removeExp($sender, $args);
                break;

            case "setxp":
                if (!$sender->hasPermission("xpeconomype.cmd.setexp")) {
                        return true;
                    }
                $this->setExp($sender, $args);
                break;

            case "addxp":
                if (!$sender->hasPermission("xpeconomype.cmd.addexp")) {
                        return true;
                    }
                $this->addExp($sender, $args);
                break;

            default:
                $sender->sendMessage("Usage: /exp [see|pay|myxp|topxp|removexp|setxp|addxp]");
                break;
        }

        return false;
    }

    private function seeExp(Player $sender, array $args): void {
        if (count($args) === 0) {
            $sender->sendMessage("Usage: /exp see <player>");
            return;
        }

        $targetPlayerName = array_shift($args);
        $exp = $this->plugin->getPlayerExp($targetPlayerName);
        
        if ($exp === null) {
            $sender->sendMessage("Player not found or has no EXP.");
        } else {
            $sender->sendMessage("{$targetPlayerName}'s EXP balance: " . $exp);
        }
    }

    private function payExp(Player $sender, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /exp pay <player> <amount>");
            return;
        }

        $targetPlayerName = array_shift($args);
        $amount = intval(array_shift($args));

        if ($amount <= 0) {
            $sender->sendMessage("Amount must be a positive integer.");
            return;
        }

        $success = $this->plugin->payExp($sender, $targetPlayerName, $amount);

        if ($success) {
            $sender->sendMessage("Paid {$amount} EXP to {$targetPlayerName}.");
        } else {
            $sender->sendMessage("Failed to pay EXP. Check if the target player exists and has enough EXP.");
        }
    }

    private function topExp(Player $sender): void {
        $topPlayers = $this->plugin->getTopPlayers();

        if (empty($topPlayers)) {
            $sender->sendMessage("No players with EXP found.");
            return;
        }

        $sender->sendMessage("Top players by EXP:");

        foreach ($topPlayers as $position => $playerData) {
            $sender->sendMessage("{$position}. {$playerData['name']} - {$playerData['exp']} EXP");
        }
    }

    private function removeExp(Player $sender, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /exp removexp <player> <amount>");
            return;
        }

        $targetPlayerName = array_shift($args);
        $amount = intval(array_shift($args));

        if ($amount <= 0) {
            $sender->sendMessage("Amount must be a positive integer.");
            return;
        }

        $success = $this->plugin->removeExp($targetPlayerName, $amount);

        if ($success) {
            $sender->sendMessage("Removed {$amount} EXP from {$targetPlayerName}.");
        } else {
            $sender->sendMessage("Failed to remove EXP. Check if the target player exists and has enough EXP.");
        }
    }

    private function setExp(Player $sender, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /exp setxp <player> <level>");
            return;
        }

        $targetPlayerName = array_shift($args);
        $level = intval(array_shift($args));

        $success = $this->plugin->setPlayerExp($targetPlayerName, $level);

        if ($success) {
            $sender->sendMessage("Set {$targetPlayerName}'s experience level to {$level}.");
        } else {
            $sender->sendMessage("Failed to set experience level. Check if the target player exists.");
        }
    }

    private function addExp(Player $sender, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /exp addxp <player> <amount>");
            return;
        }

        $targetPlayerName = array_shift($args);
        $amount = intval(array_shift($args));

        if ($amount <= 0) {
            $sender->sendMessage("Amount must be a positive integer.");
            return;
        }

        $success = $this->plugin->addExp($targetPlayerName, $amount);

        if ($success) {
            $sender->sendMessage("Added {$amount} EXP to {$targetPlayerName}.");
        } else {
            $sender->sendMessage("Failed to add EXP. Check if the target player exists.");
        }
    }
}
