<?php
    
namespace roi611\plugin;
    
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\server\CommandEvent;

use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;

use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\item\Totem;

use pocketmine\scheduler\Task;

use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\PlayerInventory;
    
class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onReSpawn(PlayerRespawnEvent $event){

        $player = $event->getPlayer();
        $this->getScheduler()->scheduleDelayedTask(new Run($player,false),1);

    }

    public function Damage(EntityDamageByEntityEvent $event){

        $player = $event->getEntity();

        if($player instanceof Player){

            $inventory = $player->getInventory();
            $item = Item::get(450,0);
            $inventory->remove($item);
            $inventory->sendContents($player);
            $this->getScheduler()->scheduleDelayedTask(new Run($player,true), 10 * 20);

        }

    }

    public function onSlotChange(InventoryTransactionEvent $event){
        
        $tr = $event->getTransaction();
        $actions = $tr->getActions();
        foreach($actions as $action){

            if($action instanceof SlotChangeAction){

                if($action->getSlot() === 8){

                    $inventory = $action->getInventory();
                    if($inventory instanceof PlayerInventory){

                        $item = $inventory->getHotBarSlotItem(8);
                        if($item instanceof Totem){
                            $event->setCancelled();
                        }
    
                    }

                }

            }

        }

    }

    public function Command(CommandEvent $event){

        $sender = $event->getSender();
        if($sender instanceof Player){

            $command = $event->getCommand();
            if($command === 'main' or $command === "/main"){

                $event->setCancelled();
                $inventory = $sender->getInventory();
                
                $inventory->remove(Item::get(450,0));

                if($inventory->isSlotEmpty(8)){

		    $player->sendMessage(§aメニューを取得しました！);
                    $item = Item::get(450,0,1);
                    $item->setCustomName('§aメニュー');
                    $inventory->setItem(8,$item);
                    $inventory->sendContents($sender);

                } else {

                    $new = Item::get(450,0,1);
                    $new->setCustomName('§aメニュー');
                    if($inventory->canAddItem($new)){

			$player->sendMessage(§aメニューを取得しました！);
                        $old = $inventory->getHotBarSlotItem(8);
                        $inventory->setItem(8,$new);
                        $inventory->addItem($old);
                        $inventory->sendContents($sender);

                    }

                }

            }

        }

    }

    public function onJoin(PlayerJoinEvent $event){

        $player = $event->getPlayer();
        $inventory = $player->getInventory();

        $inventory->remove(Item::get(450,0));

        if($inventory->isSlotEmpty(8)){

            $item = Item::get(450,0,1);
            $item->setCustomName('§aメニュー');
            $inventory->setItem(8,$item);
            $inventory->sendContents($player);

        } else {

            $new = Item::get(450,0,1);
            $new->setCustomName('§aメニュー');
            if($inventory->canAddItem($new)){

                $old = $inventory->getHotBarSlotItem(8);
                $inventory->setItem(8,$new);
                $inventory->addItem($old);
                $inventory->sendContents($player);

            }

        }

    }

    public function onDrop(PlayerDropItemEvent $event){

        $item = $event->getItem();
        if($item instanceof Totem){
            $event->setCancelled();
        }

    }
        
}



class Run extends Task{
    
    function __construct(Player $player,bool $bool){
        $this->player = $player;
        $this->bool = $bool;
	}

    function onRun(int $currentTick){

        $player = $this->player;

        if($this->bool){

            $inventory = $player->getInventory();
            $item = Item::get(450,0);
            $item->setCustomName('§aメニュー');
            $item->setCount(1);
            $inventory->setItem(8,$item);
            $inventory->sendContents($player);

        } else {

            $effect = new EffectInstance(Effect::getEffect(18),10 * 20,255,false);;
            $player->addEffect($effect);
            $effect = new EffectInstance(Effect::getEffect(11),10 * 20,255,false);
            $player->addEffect($effect);

        }

    }

}
