<?php

/**
 * Copyright 2018-2020 DragoVN(hachkingtohach1)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace hachkingtohach1\Shop;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\item\{Item,ItemFactory};
use muqsit\invmenu\{InvMenuHandler,InvMenu};
use pocketmine\command\{Command,CommandSender};
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\{Enchantment,EnchantmentInstance};
use pocketmine\network\mcpe\protocol\{LevelSoundEventPacket, LevelEventPacket};
use DaPigGuy\PiggyCustomEnchants\{CustomEnchantManager,PiggyCustomEnchants,utils\Utils};

class Main extends PluginBase implements Listener {
	
	public $id = [];
	
	public function onEnable() : void
	{
		$this->saveDefaultConfig();	
		$this->getAPI();
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function getAPI() 
	{
		$econapi = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
		$ce = $this->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants');
        if($econapi == null or $ce == null) {
            $this->getLogger()->warning(
			    'You need install plugin EconomyAPI and PiggyCustomEnchants to use this plugin!'
			);			
            $this->getServer()->shutDown();				
		}		
	}
	
	public function getEnchantment(
	    int $id
	) 
	{	
		$enchantment = Enchantment::getEnchantment($id);	
		return $enchantment;
	}
	
	public function getCEnchantment(
	    string $name,
		$item, int $level
	) 
	{
		$enchant = CustomEnchantManager::getEnchantmentByName($name);
        if ($enchant === null) {
			$this->getLogger()->warning(
			    '[Shop] CE is '.$name.' with level '.$level.' name is null!'
			);
			return;
		}
		if ($level > $enchant->getMaxLevel()) {
			$this->getLogger()->warning(
			    '[Shop] CE is '.$name.' with level'.$level.' max level is '.$enchant->getMaxLevel()
			);
			return;
		}
		if(!Utils::checkEnchantIncompatibilities($item, $enchant)) {
			$this->getLogger()->warning(
			    '[Shop] CE is '.$name.' with level '.$level.'This enchant is not compatible with another enchant.'
			);
            return;
        }
		return $enchant;		
	}
	
	public function onCommand(
	    CommandSender $player,
		Command $cmd, String $label,
		array $args
	) :bool 
	{
		switch($cmd->getName()) {
            case "shop":			
		        if(!$player instanceof Player) return true;
				$count = count($this->getConfig()->get("items"));
				$coun_shop = $count - 1;
                if(count($args) < 1) {
				    $player->sendMessage(
					    "Usage: /shop 0 -> ".$coun_shop
					);
				    return true;
				}		
                if($args[0] > $count) {
				    $player->sendMessage(
					    "Usage: /shop 0 -> ".$coun_shop
					);
				    return true;
				}				
			    $this->sendShop($player, $args[0]);
				$this->id[$player->getName()] = $args[0];
			break;
		}
		return false;
	}
	
	public function sendShop(Player $player, $type)
	{
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		foreach($this->getConfig()->get("items")[$type]["items"] as $items) {
            $menu->setName($this->getConfig()->get("items")[$type]["name"]);
		    $menu->readonly();			
		    $item = Item::get($items[0], $items[1], $items[6]);
		    $item->setCustomName($items[2]);
		    $item->setLore(str_replace("%money", $items[5], $items[3]));
		    $inv = $menu->getInventory();			
			if($items[7] != "false"){
				foreach($items[7] as $i) {
				    if($i[2] == "CE") {
					    if(is_string($i[0])) {
				            $item->addEnchantment(
							    new EnchantmentInstance($this->getCEnchantment($i[0], $item, $i[1])),
								$i[1]
							);
					    } elseif(!is_string($i[0])) {
						    $player->sendMessage(
							    "Some error with item this, need support admin!"
							);
					    }		    
				    } elseif($i[2] == "EC") {
					    if(is_numeric($item[0])) {
				            $item->addEnchantment(
							    new EnchantmentInstance($this->getEnchantment($i[0])), $i[1]
							);
					    } elseif(!is_numeric($i[0])) {
						    $player->sendMessage(
							    "Some error with item this, need support admin!"
							);
					    }						
				    }
				}
			}			
            $inv->setItem($items[4], $item);
		}
		foreach($this->getConfig()->get("sell_item") as $items) {			
		    $item = Item::get($items[0], $items[1], $items[4]);
		    $item->setCustomName($items[2]);
		    $item->setLore($items[3]);
		    $inv = $menu->getInventory();
            $inv->setItem($items[5], $item);
		}
		for($a = 45; $a <= 48; $a++) {
			for($b = 50; $b <= 53; $b++) {
				$item = Item::get(160, 7, 1);
		        $item->setCustomName("");
		        $item->setLore(array(""));
		        $inv = $menu->getInventory();
                $inv->setItem($a, $item);
				$inv->setItem($b, $item);
			}
		}	    
		$menu->setListener([$this, "sendShopEvent"]);
		$menu->send($player);
	}
	
	public function sellAll($player, $inventoryAction) 
	{
		$items = $player->getInventory()->getContents();		
		foreach($items as $item){
			$id = $item->getId();
            $name = $item->getName();			
			foreach($this->getConfig()->get("items_sell") as $sell) {	
			    if($item->getId() == $sell[0] && $item->getId() > 0) {
				    $price = $sell[1] * $item->getCount();
				    $this->economyapi->addMoney($player, $price);
				    $player->getInventory()->remove($item);
					$array_1 = ["%name", "%money", "%tmoney"];
					$array_2 = ["$name", "$sell[1]", "$price"];
					$player->sendMessage(
					    str_replace($array_1, $array_2,
						$this->getConfig()->get("have_sell"))
					);
				    $player->getLevel()->broadcastLevelSoundEvent(
					    $player->asVector3(), LevelSoundEventPacket::SOUND_LEVELUP,
						(int)mt_rand()
					);			
				    $player->removeWindow($inventoryAction->getInventory());				
				}
			}					
		}
	}
	
	public function paymentGateways(
	    $player, $values,
		$item, $meta,
		$count, $inventoryAction,
		$enchant, $lore, $nametag
	) 
	{
		$config = $this->getConfig();
		$item = Item::get($item, $meta, $count);
		$money = $this->economyapi->myMoney($player->getName());
        $nameplayer = $player->getName();
	    if ($money >= $values) {
			// Reduce Money 
		    $this->economyapi->reduceMoney($player, $values);
			
            if($enchant != "false"){
				foreach($enchant as $i) {
				    if($i[2] == "CE") {
					    if(is_string($i[0])) {
				            $item->addEnchantment(
							    new EnchantmentInstance($this->getCEnchantment($i[0], $item, $i[1])),
								$i[1]
							);
					    } elseif(!is_string($i[0])) {
						    $player->sendMessage(
							    "Some error with item this, need support admin!"
							);
					    }		    
				    } elseif($i[2] == "EC") {
					    if(is_numeric($i[0])) {
				            $item->addEnchantment(
							    new EnchantmentInstance($this->getEnchantment($i[0])), $i[1]
							);
					    } elseif(!is_numeric($i[0])) {
						    $player->sendMessage(
							    "Some error with item this, need support admin!"
							);
					    }						
				    }
				}
			}	
			
            $item->setCustomName($nametag);
		    $item->setLore($lore);
		    $player->getInventory()->addItem($item);	
			
            $player->sendMessage($config->get("buy_done"));	
            $player->getLevel()->broadcastLevelSoundEvent(
			    $player->asVector3(), LevelSoundEventPacket::SOUND_LEVELUP,
				(int)mt_rand()
			);			
		    $player->removeWindow($inventoryAction->getInventory());
		} else {
		    $player->sendMessage($config->get("do_not_enought_money"));
			$player->getLevel()->broadcastLevelSoundEvent(
			    $player->asVector3(), LevelSoundEventPacket::SOUND_TELEPORT,
				(int)mt_rand()
			);
		    $player->removeWindow($inventoryAction->getInventory());
	    }	
	}
	
	public function sendShopEvent(
	    Player $player, Item $a,
		Item $b, SlotChangeAction $inventoryAction) : bool
	{
		$config = $this->getConfig()->get("items");
		foreach($config[$this->id[$player->getName()]]["items"] as $items) {
            if($a->getCustomName() == $items[2]){
			    $this->paymentGateways(
				    $player, $items[5],
					$items[0], $items[1],
					$items[6], $inventoryAction,
					$items[7], $items[8], $items[2]
				);
			}			
		}
		foreach($this->getConfig()->get("sell_item") as $sell) {
			if($a->getCustomName() == $sell[2]){
                $this->sellAll($player, $inventoryAction);
			}
		}
		return true;
	}	
}
