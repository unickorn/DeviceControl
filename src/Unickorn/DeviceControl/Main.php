<?php

declare(strict_types=1);

namespace Unickorn\DeviceControl;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use function array_key_exists;
use function in_array;

class Main extends PluginBase implements Listener{

	/** @var array $deviceOS */
	private $deviceOS = [];
	/** @var string[] $os */
	private $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "NX"];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if($packet instanceof LoginPacket){
			$this->deviceOS[$packet->username] = $packet->clientData["DeviceOS"];
		}
	}

	/**
	 * @param EntityLevelChangeEvent $event
	 * @throws OSNotFoundException
	 */
	public function onLevelChange(EntityLevelChangeEvent $event) : void{
		$player = $event->getEntity();
		if(!$player instanceof Player) return;
		if($player->hasPermission("devicecontrol.bypass")) return;
		if(in_array($event->getTarget()->getFolderName(), $this->getConfig()->get($os = $this->getOS($player->getName())))){
			$player->sendMessage(TextFormat::RED . $os . TextFormat::WHITE . " users are not allowed to go to that world.");
			$event->setCancelled();
		}
	}

	/**
	 * @param string $player
	 * @return string
	 * @throws OSNotFoundException
	 */
	public function getOS(string $player) : string{
		if(!array_key_exists($player, $this->deviceOS)){
			throw new OSNotFoundException("Player $player either is not online or has not been given a device OS.");
		}
		return $this->os[$this->deviceOS[$player]];
	}
}