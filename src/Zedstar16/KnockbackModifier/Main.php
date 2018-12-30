<?php

declare(strict_types=1);

namespace Zedstar16\KnockbackModifier;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\level\Level;
class Main extends PluginBase implements Listener{

	public $cfg;
	public $worlds = array();

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->cfg = $this->getConfig();
		$this->saveResource("config.yml");

	}

	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event){
	   $ent = $event->getEntity();
	   $world = $ent->getLevel()->getFolderName();
	   $worldkb = $this->cfg->get($world);
	    if($worldkb !== null){
	        $kb = $event->getKnockBack();
	        $event->setKnockBack($kb*floatval($worldkb));
        }
    }

    public function getWorlds(array $worlds){
        $dir = new \DirectoryIterator("worlds");
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                array_push($worlds, $fileinfo->getFilename());
            }
        }
        return $worlds;
    }


	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "kbset":
			    if(isset($args[0])){
			        if(is_numeric($args[0])){
			            if(isset($args[1]) ){
			                if(in_array($args[1], $this->getWorlds($this->worlds))){
			                   $sender->sendMessage("The knockback level for ".C::GREEN.$args[1].C::WHITE." has been set to ".C::AQUA.$args[0].C::WHITE." from ".C::GOLD.$this->cfg->get($args[1]));
			                   $this->cfg->set($args[1], $args[0]);
			                   $this->cfg->save();
                            }elseif(strtolower($args[1]) === "global"){
			                    $sender->sendMessage("Knockback level for ".implode(", ", $this->getWorlds($this->worlds)). " has been set to ".C::AQUA.$args[0]);
                                foreach ($this->getWorlds($this->worlds) as $key) {
                                    $this->cfg->set($key, $args[0]);
                                    $this->cfg->save();
                                }
                            }else $sender->sendMessage(C::RED."Unable to find world, either set it as global or if the world name has spaces, enclose it in \"\"");
                        }else{
			                 if($sender instanceof Player) {
                                 $level = $sender->getServer()->getPlayer($sender->getName())->getLevel()->getFolderName();
                                 $sender->sendMessage("The knockback level for your current has been set to " . C::AQUA . $args[0] . " from " . C::GOLD . $this->cfg->get($level));
                                 $this->cfg->set($level, $args[0]);
                                 $this->cfg->save();
                             }else return false;
                        }
                    }else $sender->sendMessage(C::RED."The knockback value must be a ".C::GRAY."number");
                }else return false;
            }
        return true;
    }


}
