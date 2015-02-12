<?php



namespace BlockLogger;


use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\Player;


class AutoTask extends PluginTask{
//HI	
	
	
	
    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }
	
    public function onRun($currentTick){
    	$tables = $this->plugin->getAll();
        if($this->plugin->dataExists($tables)) {
            $this->plugin->killAll($tables);
        }
    }
}
