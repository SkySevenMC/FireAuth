<?php
namespace fireauth;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
	public $Main;
	public $auth = [];
	
	public function onEnable(){
	$this->saveDefaultConfig();
	@mkdir($this->getDataFolder());
	$this->getResource("config.yml");
	@mkdir($this->getDataFolder()."Players");

	$this->getLogger()->info(TF::GREEN."FireAuth loaded by SkySeven!");
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
	
    }
	
	public function onJoin(PlayerJoinEvent $event){
		
		$player = $event->getPlayer();
		$name = $player->getName();
		
		$this->auth[$name] = false;
		$player->sendMessage($this->getConfig()->get("Join.message"));
	
	 	if($this->isRegistered($name)){
			$playerfile = new Config($this->getDataFolder()."Players/".$name.".yml", Config::YAML);
			if($playerfile->get("Ip") == $player->getAddress()){
				$this->auth[$name] = true;
				$player->sendMessage($this->getConfig()->get("Ip.login.message"));
			}
	 	}
		if($this->auth[$name] == false){
			if($this->isRegistered($name)){
				$player->sendMessage($this->getConfig()->get("Login.message"));
			}else{
			$player->sendMessage($this->getConfig()->get("Register.message"));
			}
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
	
		$player = $sender->getPlayer();
		$name = $player->getName();

		if($cmd->getName() == "register"){
			if(!empty($args[0])){
				if(!$this->isRegistered($name)){
					$playerfile = new Config($this->getDataFolder()."Players/".$name.".yml", Config::YAML);
					$playerfile->set("Ip", $sender->getAddress());
					$playerfile->set("Password", md5($args[0]));
					$playerfile->save();
					
					$this->auth[$name] = true;
					
					$sender->sendMessage($this->getConfig()->get("Register.success.message"));
				}else{
					$sender->sendMessage($this->getConfig()->get("Already.registred.message"));
				}
			}else{
				$sender->sendMessage($this->getConfig()->get("Register.command.message"));
			}
		}
		if($cmd->getName() == "login"){
			if(!empty($args[0])){
				if($this->isRegistered($name)){
					$playerfile = new Config($this->getDataFolder()."Players/".$name.".yml", Config::YAML);
					if($this->auth[$name] == false){
					$pw = $playerfile->get("Password");
						if($pw == md5($args[0])){
							$sender->sendMessage($this->getConfig()->get("Login.success.message"));
							$playerfile->set("Ip", $sender->getAddress());
							$playerfile->save();
							$this->auth[$name] = true;
						}else{
							$sender->sendMessage($this->getConfig()->get("Incorrect.password.message"));
						}
					}else{
						$sender->sendMessage($this->getConfig()->get("Already.logged-in.message"));
					}
				}else{
					$sender->sendMessage($this->getConfig()->get("User.not.registered.message"));
				}
			}else{
				$sender->sendMessage($this->getConfig()->get("Login.command.message"));
			}
		}
	}
	
	public function onMove(PlayerMoveEvent $event){
	
	$player = $event->getPlayer();
	$name = $player->getName();
		
		if($this->auth[$name] == false){
			$event->setCancelled(TRUE);
		}
	}
	
	public function onChat(PlayerChatEvent $event){
	
	$player = $event->getPlayer();
	$name = $player->getName();
		
		if($this->auth[$name] == false){
			$event->setCancelled(TRUE);
		}
	}
	
	public function onCmdProcess(PlayerCommandPreprocessEvent $event){
	
	$player = $event->getPlayer();
	$name = $player->getName();
	$msg = strtolower($event->getMessage());
	$args = explode(" ", $msg);
	$cmd = array_shift($args);
	
	
		if($this->auth[$name] == false){
			$perm = FALSE;
			if($cmd == "/register"){
				$perm = TRUE;
			}
			if($cmd == "/login"){
				$perm = TRUE;	
			}	
			if($perm == FALSE){
				$event->setCancelled(TRUE);
			}
		}
	}
	
	public function onBlockBreak(BlockBreakEvent $event){
	
	$player = $event->getPlayer();
	$name = $player->getName();
	
		if($this->auth[$name] == false){
			$event->setCancelled(TRUE);
		}
	}
	
	public function onUse(PlayerInteractEvent $event){
	
	$player = $event->getPlayer();
	$name = $player->getName();
	
		if($this->auth[$name] == false){
			$event->setCancelled(TRUE);
		}
	}
	
	public function isRegistered($name){
		if(file_exists($this->getDataFolder()."Players/".$name.".yml")){
			return true;
		}
		return false;
	}
}
