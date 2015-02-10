<?php


namespace BlockLogger;


use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\Player;
use pocketmine\IPlayer;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


class Main extends PluginBase  implements Listener {
	
	
	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info( TextFormat::GREEN . "BlockLogger - Enabled!" );
	}
	
	
	public function onBreak(BlockBreakEvent $ev) {
            $provider = $this->getConfig()->get("Provider");
            $player = $ev->getPlayer();
            $name = $player->getName();
            $auth = $this->getConfig()->get($name);
            $block = $ev->getBlock();
            $pos = new Vector3($block->getX(),$block->getY(),$block->getZ());
            if($auth == true && $this->getConfig()->get("Enabled") && $this->getConfig()->get("Provider") !== null) {
		if($provider == "CONFIG" && !file_exists($this->getDataFolder() . "Players/" . $name . ".yml")) {
                    $this->conf = new Config($this->getDataFolder() . "Players/" . $name . ".yml", CONFIG::YAML);
                    echo $pos;
                    echo $block;
                    return true;
                }
            }
        }
	public function onQuit(PlayerQuitEvent $ev) {
		if(isset($this->sessions[$ev->getPlayer()->getName()])) {
			unset($this->sessions[$ev->getPlayer()->getName()]);
			return true;
		}
	}
	
	public function hideUser($user) {
		foreach($user->getLevel()->getPlayers() as $p) {
			$p->hidePlayer($user);
			return true;
		}
	}
	
	public function showUser($user) {
		foreach($user->getLevel()->getPlayers() as $p) {
			$p->showPlayer($user);
			$user->sendMessage("Curse has worn off!");
			return true;
		}
	}
}

