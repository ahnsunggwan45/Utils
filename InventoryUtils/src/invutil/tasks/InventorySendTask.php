<?php
namespace invutil\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\inventory\Inventory;

class InventorySendTask extends Task
{

    private $player, $inventory;

    public function __construct(Player $player, Inventory $inventory)
    {
        $this->player = $player;
        $this->inventory = $inventory;
    }

    public function onRun(int $currentTick)
    {
        $this->player->addWindow($this->inventory);
    }
}