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
use pocketmine\math\Vector3;


class Main extends PluginBase  implements Listener {
	
	
	public function onEnable() {
		@mkdir($this->getDataFolder());
                @mkdir($this->getDataFolder() . "Players/");
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info( TextFormat::GREEN . "BlockLogger - Enabled!" );
                $this->db = new \SQLite3($this->getDataFolder() . "Log.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS logBreak (player TEXT, block TEXT, data TEXT);");
 		$this->db->exec("CREATE TABLE IF NOT EXISTS logPlace (player TEXT, block TEXT, data TEXT);");
        }
	
	
	public function onBreak(BlockBreakEvent $ev) {
            $player = $ev->getPlayer();
            $name = $player->getName();
            $auth = $this->getConfig()->get($name);
            $block = $ev->getBlock();
            $date = date("[m/d]");
            $pos = new Vector3($block->getX(),$block->getY(),$block->getZ());
            $format = $pos->getX() . ", " . $pos->getY() . ", " . $pos->getZ() . ", Day- " . $date;
            if($auth) {
		if($this->getConfig()->get("Enabled")) {
                    $sql = $this->db->prepare("INSERT OR REPLACE INTO logBreak (player, block, data) VALUES (:player, :block, :data);");
                    $sql->bindValue(":player", $name);
                    $sql->bindValue(":block", $block->getName());
                    $sql->bindValue(":data", $format);
                    $result = $sql->execute();
                    return true;
                }
            }
        }
        public function onPlace(BlockPlaceEvent $ev) {
            $player = $ev->getPlayer();
            $name = $player->getName();
            $auth = $this->getConfig()->get($name);
            $block = $ev->getBlock();
            $date = date("[m/d]");
            $pos = new Vector3($block->getX(),$block->getY(),$block->getZ());
            $format = $pos->getX() . ", " . $pos->getY() . ", " . $pos->getZ() . ", Day- " . $date;
            if($auth) {
		if($this->getConfig()->get("Enabled")) {
                    $sql = $this->db->prepare("INSERT OR REPLACE INTO logPlace (player, block, data) VALUES (:player, :block, :data);");
                    $sql->bindValue(":player", $name);
                    $sql->bindValue(":block", $block->getName());
                    $sql->bindValue(":data", $format);
                    $result = $sql->execute();
                    return true;
                }
            }
        }
}