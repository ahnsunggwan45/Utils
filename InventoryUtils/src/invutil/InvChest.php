<?php

namespace invutil;

use pocketmine\tile\Chest;
use pocketmine\Player;

class InvChest extends Chest
{

    public $user = null;

    public $users = [];

    public $custom_id = 0;

    /** @var callable[] */
    public $handlers = [];

    public function resetHandler()
    {
        $this->handlers = [];
    }

    public function setHandler(int $slot, callable $f)
    {
        $this->handlers[$slot] = $f;
    }

    public function handle(Player $player, int $slot): bool
    {
        if (isset($this->handlers[$slot])) {
            $f = $this->handlers[$slot];
            $f($player);
            return true;
        }
        return false;
    }

    public function setCustomId(int $id)
    {
        $this->custom_id = $id;
    }

    public function getCustomId(): int
    {
        return $this->custom_id;
    }

    public function setUser(Player $player)
    {
        if ($this->user === null)
            $this->user = $player;
        $this->users[] = $player;
    }

    /**
     *
     * @return array | Player[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function getUser(): ?Player
    {
        return $this->user;
    }
}