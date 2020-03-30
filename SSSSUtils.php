<?php
/**
 * @name SSSSUtils
 * @main ssss\utils\SSSSUtils
 * @author ssss
 * @api x
 * @version x
 */

namespace ssss\utils;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\network\mcpe\protocol\OnScreenTextureAnimationPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\item\Item;

class SSSSUtils extends PluginBase
{

    public function onEnable()
    {
        \o\c\c::command("야간투시", "야간투시를 얻습니다.", "/야간투시", [], function (CommandSender $sender, string $commandLabel, array $args) {
            if ($sender instanceof Player) {
                $sender->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 20 * 60 * 60, 1, false));
                SSSSUtils::osta($sender, 16);
            }
        });
    }

    public static function itemName(Item $item): string
    {
        return $item->getCustomName() !== '' ? $item->getCustomName() : $item->getName();
    }

    public static function osta(Player $player, int $code)
    {
        $pk = new OnScreenTextureAnimationPacket();

        $pk->effectId = (int)$code;
        $player->sendDataPacket($pk);
    }

    public static function message(CommandSender $sender, string $message)
    {
        $sender->sendMessage('§l§b[알림] §r§7' . $message);
    }

    public static function info(CommandSender $sender, string $message)
    {
        $sender->sendMessage("§l§6[알림] §r§7{$message}");
    }

    public static function prevent(CommandSender $sender, string $message)
    {
        $sender->sendMessage("§l§c[알림] §r§7{$message}");
    }

    public static function setNamedTagEntry(Item &$item, NamedTag $new)
    {
        $tag = $item->getNamedTag();
        $tag->setTag($new);
        $item->setNamedTag($tag);
    }

    public static function getNamedTagEntry(Item $item, string $name): ?NamedTag
    {
        return $item->getNamedTag()->getTag($name);
    }

    public static function posToString(Position $pos): string
    {
        return implode(':', [
            $pos->x,
            $pos->y,
            $pos->z,
            $pos->level->getFolderName()
        ]);
    }

    public static function strToPosition(string $pos): ?Position
    {
        $p = explode(':', $pos);
        $load = Server::getInstance()->loadLevel($p[3]);
        if ($load)
            return new Position((int)$p[0], (int)$p[1], (int)$p[2], Server::getInstance()->getLevelByName($p[3]));
        return null;
    }
}