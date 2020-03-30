<?php
namespace invutil;

use pocketmine\tile\Chest;
use pocketmine\Player;

class InvChest extends Chest
{

    public $user = null;

    public $users = [];

    public $custom_id = 0;

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