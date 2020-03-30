<?php
namespace invutil\event;

use pocketmine\event\Event;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\event\Cancellable;

class InvChestSlotChangeEvent extends Event implements Cancellable
{

    public static $handlerList = null;

    protected $player;

    protected $sourceItem;

    protected $targetItem;

    protected $slot;

    protected $inventory;

    protected $custom_id;

    public function __construct(Player $player, Item $sourceItem, int $slot, Inventory $inventory, int $custom_id, Item $targetItem)
    {
        $this->player = $player;
        $this->sourceItem = $sourceItem;
        $this->targetItem = $targetItem;
        $this->slot = $slot;
        $this->inventory = $inventory;
        $this->custom_id = $custom_id;
    }

    public function getCustomId(): int
    {
        return $this->custom_id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getItem(): Item
    {
        return $this->sourceItem;
    }

    public function getTargetItem(): Item
    {
        return $this->targetItem;
    }

    public function getSlot(): int
    {
        return $this->slot;
    }
}