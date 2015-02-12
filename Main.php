<?php


namespace BlockLogger;


use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
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
                //Automatically Reset
                if($this->getConfig()->get("AutoReset")) {
                    $time = $this->getConfig()->get("ResetTime") * 1200;
                    $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new AutoTask($this), 120, $time);
                }
        }
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($sender instanceof Player) {
                    $player = $sender->getPlayer()->getName();
                    $auth = $this->getConfig()->get($player);
                    if(strtolower($command->getName('bl'))) {
                        if(empty($args)) {
                            $sender->sendMessage("> Usage:\n//bl reset <player>");
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
                            if(!$this->dataExists($args[1])) {
                                $sender->sendMessage("> Player has not data!");
                                return true;
                            }
                            $this->db->query("DROP TABLE $args[1];");
                            return true;
                        }
                        if($args[1] == "break") {
                            $sender->sendMessage("> Please use command in console.");
                            return true;
                        }
                        if($args[1] == "place") {
                            $sender->sendMessage("> Please use command in console.");
                            return true;
                        }
                    }
                }else{
                    if(strtolower($command->getName('bl'))) {
                        if(empty($args)) {
                            $sender->sendMessage("> Usage:\n/bl <player> [break/place]\n/bl reset <player>");
                            return true;
                        }
                        if(strtolower($args[0]) == "reset") {
                            if(empty($args[1])) {
                                $sender->sendMessage("> Usage:\n/bl reset <player>");
                                return true;
                            }
                            $auth = $this->getConfig()->get($args[1]);
                            if(!$auth) {
                                $sender->sendMessage("> Player not being logged, case sensitive.");
                                return true;
                            }
                            if(!$this->dataExists($args[1])) {
                                $sender->sendMessage("> Player has not data!");
                                return true;
                            }
                            $this->db->query("DROP TABLE $args[1];");
                            $sender->sendMessage("> Player reset!");
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
                        if(!$this->dataExists($args[0])) {
                            $sender->sendMessage("> Player has not data!");
                            return true;
                        }
                        if(strtolower($args[1]) == "break") {
                            $name = $args[0];
                            $this->getBreakFormat($name);
                            return true;
                        }
                        if(strtolower($args[1]) == "place") {
                            $name = $args[0];
                            $this->getPlaceFormat($name);
                            return true;
                        }
                    }
                }
        }//TEST
	public function onBreak(BlockBreakEvent $ev) {
            $player = $ev->getPlayer();
            $name = $player->getName();
            $auth = $this->getConfig()->get($name);
            $block = $ev->getBlock();
            $date = date("[m/d]");
            $pos = new Vector3($block->getX(),$block->getY(),$block->getZ());
            $format = $pos->getX() . ", " . $pos->getY() . ", " . $pos->getZ() . ", Day- " . $date;
            $act = "BREAK";
            if($auth) {
		if($this->getConfig()->get("Enabled")) {
                    $this->db->exec("CREATE TABLE IF NOT EXISTS $name (block TEXT, data TEXT, action TEXT);");
                    $sql = $this->db->prepare("INSERT OR REPLACE INTO $name (block, data, action) VALUES (:block, :data, :action);");
                    $sql->bindValue(":block", $block->getName());
                    $sql->bindValue(":data", $format);
                    $sql->bindValue(":action", $act);
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
            $act = "PLACE";
            if($auth) {
		if($this->getConfig()->get("Enabled")) {
                    $this->db->exec("CREATE TABLE IF NOT EXISTS $name (block TEXT, data TEXT, action TEXT);");
                    $sql = $this->db->prepare("INSERT OR REPLACE INTO $name (block, data, action) VALUES (:block, :data, :action);");
                    $sql->bindValue(":block", $block->getName());
                    $sql->bindValue(":data", $format);
                    $sql->bindValue(":action", $act);
                    $result = $sql->execute();
                    return true;
                }
            }
        }
        public function getAll() {
            $table = $this->db->query("SELECT tbl_name FROM sqlite_master WHERE type = 'table';");
            $tableArray = $table->fetchArray(SQLITE3_ASSOC);
            $tp = $tableArray["tbl_name"];
            return $tp;
	}
        public function killAll($tp) {
            $this->db->query("DROP TABLE $tp;");
	}
        public function getBreakFormat($player) {
            $break = $this->db->query("SELECT * FROM $player WHERE action='BREAK';");
            while($row = $break->fetchArray(SQLITE3_ASSOC)) {
                $rowArray = $row["block"] . ", " . $row["data"] . ", " . $row["action"] . "\n";
                $this->getLogger()->info( TextFormat::RED . "$rowArray" );
            }
	}
        public function getPlaceFormat($player) {
            $place = $this->db->query("SELECT * FROM $player WHERE action='PLACE';");
            while($row = $place->fetchArray(SQLITE3_ASSOC)) {
                $rowArray = $row["block"] . ", " . $row["data"] . ", " . $row["action"] . "\n";
                $this->getLogger()->info( TextFormat::RED . "$rowArray" );
            }
	}
        public function dataExists($player) {
            $check = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$player';");
            if($check->fetchArray(SQLITE3_ASSOC) == 0){
                return false;
            }else{
                return true;
            }
        }
}