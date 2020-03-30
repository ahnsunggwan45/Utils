<?php

/**
 * @name c
 * @author ojy
 * @version B1
 * @api 4.0.0
 * @main o\c\c
 */

namespace o\c;

use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;

class c extends PluginBase
{

    public static function command(string $name, string $description, string $usage, array $aliases, callable $f, bool $op = false)
    {
        Server::getInstance()->getCommandMap()->register($name, new class($name, $description, $usage, $aliases, $f, $op) extends Command
        {

            protected $f;

            /** @var bool */
            private $op = false;

            public function __construct($n, $d, $u, $aliases, $f, $op)
            {
                parent::__construct($n, $d, $u, $aliases);
                $this->f = $f;
                $this->op = $op;
                if ($op)
                    $this->setPermission(Permission::DEFAULT_OP);
            }

            public function execute(CommandSender $sender, string $commandLabel, array $args)
            {
                if ($this->op) {
                    if ($sender->hasPermission($this->getPermission())) {
                        $f = $this->f;
                        $f($sender, $commandLabel, $args);
                    }
                } else {
                    $f = $this->f;
                    $f($sender, $commandLabel, $args);
                }
            }
        });
    }
}