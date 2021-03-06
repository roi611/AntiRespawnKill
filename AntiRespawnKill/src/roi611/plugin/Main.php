<?php
    
namespace roi611\plugin;
    
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
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

use pocketmine\math\Vector3;
    
class Main extends PluginBase implements Listener{

    public $time;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onReSpawn(PlayerRespawnEvent $event){

        $player = $event->getPlayer();
        $this->getScheduler()->scheduleDelayedTask(new Run($player,false,$this),1);

    }

    public function Damage(EntityDamageByEntityEvent $event){

        $player = $event->getEntity();

        if($player instanceof Player){

            $inventory = $player->getInventory();
            $item = Item::get(450,0);
            $inventory->remove($item);
            $inventory->sendContents($player);
            if(!is_array($this->time)) $this->time = [];

            if(!isset($this->time[$player->getName()])){

                $this->time[$player->getName()] = strtotime("+10 Second");
                $this->getScheduler()->scheduleDelayedTask(new Run($player,true,$this), 10 * 20);

            }

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

                    $item = Item::get(450,0,1);
                    $item->setCustomName('??a????????????');
                    $inventory->setItem(8,$item);
                    $inventory->sendContents($sender);

                } else {

                    $new = Item::get(450,0,1);
                    $new->setCustomName('??a????????????');
                    if($inventory->canAddItem($new)){

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
            $item->setCustomName('??a????????????');
            $inventory->setItem(8,$item);
            $inventory->sendContents($player);

        } else {

            $new = Item::get(450,0,1);
            $new->setCustomName('??a????????????');
            if($inventory->canAddItem($new)){

                $old = $inventory->getHotBarSlotItem(8);
                $inventory->setItem(8,$new);
                $inventory->addItem($old);
                $inventory->sendContents($player);

            } else {

                $old = $inventory->getHotBarSlotItem(8);
                $inventory->setItem(8,$new);
                $inventory->sendContents($player);
                $player->getLevel()->DropItem(new Vector3($player->x,$player->y,$player->z),$old);

            }

        }

    }

    public function onMove(PlayerMoveEvent $event){

        $player = $event->getPlayer();
        if($player->getLevel()->getName() === 'lobby1'){

            $player->removeEffect(6);
            $effect = new EffectInstance(Effect::getEffect(6),1 * 20,5,false);;
            $player->addEffect($effect);

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
    
    function __construct(Player $player,bool $bool,Main $main){
        $this->player = $player;
        $this->bool = $bool;
        $this->main = $main;
	}

    function onRun(int $currentTick){

        $player = $this->player;

        if($this->bool){

            $inventory = $player->getInventory();

            if($inventory !== null){

                $new = Item::get(450,0,1);
                $new->setCustomName('??a????????????');
                if($inventory->canAddItem($new)){

                    $old = $inventory->getHotBarSlotItem(8);
                    $inventory->setItem(8,$new);
                    $inventory->addItem($old);
                    $inventory->sendContents($player);

                } else {

                    $old = $inventory->getHotBarSlotItem(8);
                    $inventory->setItem(8,$new);
                    $inventory->sendContents($player);
                    $player->getLevel()->dropItem(new Vector3($player->x,$player->y,$player->z),$old);

                }

            } else {
                $time = date('H:i:s');
                $this->main->getLogger()->info("??4 Error: AntiRespawnKill ".'$inventory was null'." Line: 207");
            }

            unset($this->main->time[$player->getName()]);

        } else {

            $effect = new EffectInstance(Effect::getEffect(18),10 * 20,255,false);
            $player->addEffect($effect);
            $effect = new EffectInstance(Effect::getEffect(11),10 * 20,255,false);
            $player->addEffect($effect);

        }

    }

}