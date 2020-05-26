<?php

namespace Unickorn\DeviceControl;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

    private $deviceOS;

    private $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "NX"];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $e){
        $packet = $e->getPacket();
        if($packet instanceof LoginPacket){
        	$this->deviceOS[$packet->username] = $packet->clientData["DeviceOS"];
        }
    }

    public function onLevelChange(EntityLevelChangeEvent $e){
        $p = $e->getEntity();
        if(!$p instanceof Player) return;
        if($p->hasPermission("devicecontrol.bypass")) return;
        if(in_array($e->getTarget()->getFolderName(), $this->getConfig()->get($os = $this->getOS($p->getName())))){
            $p->sendMessage(TextFormat::RED . $os . TextFormat::WHITE . " users are not allowed to go to that world.");
            $e->setCancelled();
        }
    }

    /**
     *  @param string $player
     *  @return string
     */
    public function getOS(string $player) : string{
        if(!array_key_exists($player, $this->deviceOS)) throw new \InvalidKeyException("Player $player either is not online or has not been given a device OS.");
        return $this->os[$this->deviceOS[$player]];
    }
}