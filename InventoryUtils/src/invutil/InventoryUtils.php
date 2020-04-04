<?php

namespace invutil;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\inventory\ChestInventory;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\tile\Tile;
use pocketmine\inventory\Inventory;
use invutil\tasks\InventorySendTask;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use invutil\event\InvChestSlotChangeEvent;
use pocketmine\inventory\DoubleChestInventory;

class InventoryUtils extends PluginBase implements Listener
{

    /** @var self|null */
    private static $instance = null;

    /**
     * @return InventoryUtils
     */
    public static function getInstance(): self
    {
        return static::$instance;
    }

    /**
     * @throws \ReflectionException
     */
    public function onLoad()
    {
        self::$instance = $this;
        Tile::registerTile(InvChest::class, ["invchest"]);
    }

    public function onEnable()
    {
        $this->getServer()
            ->getPluginManager()
            ->registerEvents($this, $this);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            $this->invChestTileCheck();
        }), 100);
    }

    public function invChestTileCheck()
    {
        $r = 0;
        foreach (Server::getInstance()->getLevels() as $level) {
            foreach ($level->getTiles() as $tile) {
                if ($tile instanceof InvChest) {
                    $level->removeTile($tile);
                    ++$r;
                }
            }
        }
        Server::getInstance()->getLogger()->info("{$r} tiles removed.");
    }

    /**
     * @param InventoryTransactionEvent $event
     * @throws \ReflectionException
     */
    public function slotChange(InventoryTransactionEvent $event)
    {
        $tr = $event->getTransaction();
        $actions = $tr->getActions();
        foreach ($actions as $hash => $action) {
            if ($action instanceof SlotChangeAction) {
                $slot = $action->getSlot();
                $inv = $action->getInventory();
                if ($inv instanceof ChestInventory) {
                    $holder = $inv->getHolder();

                    if ($holder instanceof InvChest) {
                        $player = $holder->getUser();
                        if ($holder->handle($player, $slot)) {
                            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player): void {
                                $player->getCursorInventory()->clearAll(true);
                                $player->getCursorInventory()->setItem(0, ItemFactory::get(0));
                            }), 2);
                            $event->setCancelled();

                            /*$e = new InvChestSlotChangeEvent($player, $action->getSourceItem(), $slot, $inv, $id, $action->getTargetItem());
                            $e->call();*/
                        }
                    }
                }
            }
            unset($hash);
        }
    }

    /**
     * @param Player $player
     * @param string $customName
     * @param int $id
     * @return DoubleChestInventory
     */
    public function getDoubleChestInventory(Player $player, string $customName, int $id): DoubleChestInventory
    {
        $pos = $player->add(0, -3, 0)->floor();
        $block = $player->level->getBlock($pos);
        $nbt = InvChest::createNBT($pos->asVector3());
        $nbt->setString("CustomName", "{$customName}");
        $holder = InvChest::createTile("InvChest", $player->level, $nbt);
        if ($holder instanceof InvChest) {
            $holder->setUser($player);
            $holder->setCustomId($id);
        }
        $block = Block::get(Block::CHEST);
        $block->x = (int)$holder->x;
        $block->y = (int)$holder->y;
        $block->z = (int)$holder->z;
        $block->level = $holder->getLevel();
        $block->level->sendBlocks([
            $player
        ], [
            $block
        ]);
        $pos = $player->add(1, -3, 0)->floor();
        $block = $player->level->getBlock($pos);
        $nbt = InvChest::createNBT($pos->asVector3());
        $nbt->setString("CustomName", "{$customName}");
        $holder2 = InvChest::createTile("InvChest", $player->level, $nbt);
        if ($holder2 instanceof InvChest) {
            $holder2->setUser($player);
            $holder2->setCustomId($id);
        }
        $block = Block::get(Block::CHEST);
        $block->x = (int)$holder2->x;
        $block->y = (int)$holder2->y;
        $block->z = (int)$holder2->z;
        $block->level = $holder2->getLevel();
        $block->level->sendBlocks([
            $player
        ], [
            $block
        ]);
        $holder->pairWith($holder2);
        return new DoubleChestInventory($holder, $holder2);
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $block = $event->getBlock();
        $tile = $block->level->getTile($block);
        if ($tile instanceof InvChest) {
            $event->getPlayer()->sendMessage("DONT BREAK THAT BLOCK, Block ID: {$block->getId()}");
            $event->setCancelled();
        }
    }

    /**
     * @param Player $player
     * @param string $customName
     * @param int $id
     * @return ChestInventory
     */
    public function getChestInventory(Player $player, string $customName, int $id): ChestInventory
    {
        $pos = $player->add(0, -3, 0)->floor();
        $block = $player->level->getBlock($pos);
        $nbt = InvChest::createNBT($pos->asVector3());
        $nbt->setString("CustomName", "{$customName}");
        $holder = InvChest::createTile("InvChest", $player->level, $nbt);
        if ($holder instanceof InvChest) {
            $holder->setUser($player);
            $holder->setCustomId($id);
        }
        $block = Block::get(Block::CHEST);
        $block->x = (int)$holder->x;
        $block->y = (int)$holder->y;
        $block->z = (int)$holder->z;
        $block->level = $holder->getLevel();
        $block->level->sendBlocks([
            $player
        ], [
            $block
        ]);
        return new ChestInventory($holder);
    }

    /**
     * @param Player $player
     * @param Inventory $inventory
     */
    public function sendInventory(Player $player, Inventory $inventory)
    {
        $this->getScheduler()->scheduleDelayedTask(new InventorySendTask($player, $inventory), 10);

        /*if ($inventory instanceof ChestInventory) {
            $holder = $inventory->getHolder();
            if ($holder instanceof InvChest) {
                $player->level->removeTile($holder);
                if($inventory instanceof DoubleChestInventory)
                    $player->level->removeTile($holder->getPair());
            }
        }*/
    }
}