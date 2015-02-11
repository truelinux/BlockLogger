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
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($sender instanceof Player) {
                    $player = $sender->getPlayer()->getName();
                    $auth = $this->getConfig()->get($player);
                    if(strtolower($command->getName('bl'))) {
                        if(empty($args)) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]\n/bl reset <player>");
                            return true;
                        }
                        if($args[0] == "reset") {
                            if(empty($args[1])) {
                                $sender->sendMessage("> Usage:\n/bl reset <player>");
                                return true;
                            }
                            $auth = $this->getConfig()->get($args[1]);
                            if(!$auth) {
                                $sender->sendMessage("> Player not being logged, case sensitive.");
                                return true;
                            }
                            $this->db->query("DELETE FROM logBreak WHERE player='$args[1]';");
                            $this->db->query("DELETE FROM logPlace WHERE player='$args[1]';");
                            return true;
                        }
                        if(empty($args[0])) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]");
                            return true;
                        }
                        if(empty($args[1])) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]");
                            return true;
                        }
                        $auth = $this->getConfig()->get($args[0]);
                        if(!$auth) {
                            $sender->sendMessage("> Player not being logged, case sensitive.");
                            return true;
                        }
                        if($args[1] == "break") {
                            $name = $args[0];
                            $block = $this->getBreakBlock($name);
                            $data = $this->getBreakData($name);
                            $msg = "$name -> $block , $data";
                            foreach($msg as $send) {
                                $sender->sendMessage($send);
                            }
                            return true;
                        }
                        if($args[1] == "place") {
                            $name = $args[0];
                            $block = $this->getPlaceBlock($name);
                            $data = $this->getPlaceData($name);
                            $msg = "$name -> $block , $data";
                            foreach($msg as $send) {
                                $sender->sendMessage($send);
                            }
                            return true;
                        }
                    }
                }else{
                    if(strtolower($command->getName('bl'))) {
                        if(empty($args)) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]\n/bl reset <player>");
                            return true;
                        }
                        if($args[0] == "reset") {
                            if(empty($args[1])) {
                                $sender->sendMessage("> Usage:\n/bl reset <player>");
                                return true;
                            }
                            $auth = $this->getConfig()->get($args[1]);
                            if(!$auth) {
                                $sender->sendMessage("> Player not being logged, case sensitive.");
                                return true;
                            }
                            $this->db->query("DELETE FROM logBreak WHERE player='$args[1]';");
                            $this->db->query("DELETE FROM logPlace WHERE player='$args[1]';");
                            return true;
                        }
                        if(empty($args[0])) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]");
                            return true;
                        }
                        if(empty($args[1])) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]");
                            return true;
                        }
                        $auth = $this->getConfig()->get($args[0]);
                        if(!$auth) {
                            $sender->sendMessage("> Player not being logged, case sensitive.");
                            return true;
                        }
                        if($args[1] == "break") {
                            $name = $args[0];
                            $block = $this->getBreakBlock($name);
                            $data = $this->getBreakData($name);
                            $msg = "$name -> $block , $data";
                            foreach($msg as $send) {
                                $sender->sendMessage($send);
                            }
                            return true;
                        }
                        if($args[1] == "place") {
                            $name = $args[0];
                            $block = $this->getPlaceBlock($name);
                            $data = $this->getPlaceData($name);
                            $msg = "$name -> $block , $data";
                            foreach($msg as $send) {
                                $sender->sendMessage($send);
                            }
                            return true;
                        }
                    }
                }
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
        public function getBreakBlock($player) {
            $break = $this->db->query("SELECT * FROM logBreak WHERE player='$player';");
            $breakArray = $break->fetchArray(SQLITE3_ASSOC);
            return $breakArray["block"];
	}
        public function getBreakData($player) {
            $break = $this->db->query("SELECT * FROM logBreak WHERE player='$player';");
            $breakArray = $break->fetchArray(SQLITE3_ASSOC);
            return $breakArray["data"];
	}
        public function getPlaceBlock($player) {
            $place = $this->db->query("SELECT * FROM logPlace WHERE player='$player';");
            $placeArray = $place->fetchArray(SQLITE3_ASSOC);
            return $placeArray["block"];
	}
        public function getPlaceData($player) {
            $place = $this->db->query("SELECT * FROM logPlace WHERE player='$player';");
            $placeArray = $place->fetchArray(SQLITE3_ASSOC);
            return $placeArray["data"];
	}
}