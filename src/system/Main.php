<?php

namespace system;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;

use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\utils\Random;
use pocketmine\item\Item;
use pocketmine\utils\Config;


use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\entity\Effect;

use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\PlayerActionPacket;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\utils\TextFormat;
use pocketmine\command\{Command, CommandSender};

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\FloatingTextParticle;

use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\AnvilBreakSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\EndermanTeleportSound;
use system\Run;

class Main extends PluginBase implements Listener {
    
    public $alert = "§8(§f§4 K§ci§4l§cl§4A§cl§4e§cr§4t§8)§r ";
    public $gamemode = "§8(§f §cG§6a§em§ae§bm§do§cd§6e§8)§r ";
    public $staron = "§f⨿§6ด§eา§6ว§eพ§6รี§eเ§6มี§eย§6ม§f⨿";
    public $staroff = "§cไม่มี";
    public $rank1 = "§4ส§cว§4ะ§cก§4ร§cะ§4จ§cอ§4ก";
    public $rank2 = "§3ไ§bก่§3ก§bร§3ะ§bจ§3อ§bก";
    public $rank3 = "§2ก§aร§2ะ§aจ§2อ§aก";
    public $rank4 = "§5เ§dริ่§5ม§dเ§5ล่§dน§5เ§dป็§5น";
    public $rank5 = "§6กุ§eเ§6ล่§eน§6เ§eป็§6น§eล§6ะ";
    public $rank6 = "§3พ§bว§3ก§bโ§3ง่";
    public $rank7 = "§cพ§6ว§eก§aก§bร§dะ§cจ§6อ§eก";
    public $rank8 = "§2กุ§aเ§2ก่§aง§2ค§aรั§2บ";
    public $rank9 = "§5พ§dว§5ก§dไ§5ก่§dหุ§5บ§dป§5า§dก";
    public $rank10 = "§4ม§cห§6า§2เ§aท§3พ§bย§1า§9อ่§5อ§dน";
    public $tag = "§8(§f§c แ§6ค§eล§aน§8)§r ";
    public $tag2 = "§8(§f⩐ §6ธ§eน§6า§eค§6า§eร§8)§r ";
    public $tag3 = "§8(§f§6 ด§eา§6ว§eพ§6รี§eเ§6มี§eย§6ม§8)§r ";
    public $color = "§8(§f§c C§6o§el§ao§br§8)§r ";
    public $lvlsys = "§8(§f§2 เ§aล§2เ§aว§2ล§8)§r ";
    public $free = "§8(§f §2F§ar§2e§ae§8)§r ";
    public $vanish = "§8(§f §3ซ่§bอ§3น§bตั§3ว§8)§r ";
    
	function onEnable ()
	{
		@mkdir($this->getDataFolder());
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Run($this), 20 * 1);
        $this->money = new Config($this->getDataFolder()."money.yml", Config::YAML);
        $this->rank = new Config($this->getDataFolder()."rank.yml", Config::YAML);
        $this->star = new Config($this->getDataFolder()."star.yml", Config::YAML);
        $this->lvl = new Config($this->getDataFolder()."levelexp.yml", Config::YAML);
        $this->expn = new Config($this->getDataFolder()."exppoint.yml", Config::YAML);
        $this->expc = new Config($this->getDataFolder()."exppointneed.yml", Config::YAML);
        $this->kill = new Config($this->getDataFolder()."kill.yml", Config::YAML);
        $this->death = new Config($this->getDataFolder()."death.yml", Config::YAML);
		$this->rgb = new Config($this->getDataFolder()."rgb.yml", Config::YAML);
		$this->clan = $this->getServer()->getPluginManager()->getPlugin("Clan");
		$this->saveDefaultConfig();
		$this->freeitem = new Config($this->getDataFolder(). "freeitemcooldown.yml", Config::YAML);
		if(!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());

		Server::getInstance()->getPluginManager()->registerEvents($this,$this);

		$this->bullet = [271 => 15, 258 => 15, 257 => 15, 292 => 15, 291 => 15, 278 => 15, 260 => 1];
		$this->reload = [271 => 1.5, 258 => 1.5, 257 => 1.5, 292 => 1.5, 291 => 1.5, 278 => 1.5, 260 => 1.5];
		$this->vec = [];
		$this->crirate = [271 => 30, 258 => 30, 257 => 30, 292 => 30, 291 => 30, 278 => 30];

		$this->weapon = [271 => "§r§f §2V§ai§2r§au§2s §7GUN§f §r", 258 => "§r§f §6C§ey§6b§ee§6r§7 GUN§f §r", 257 => "§r§f§c V§6e§en§ao§bm§7 GUN§f §r", 292 => "§r§f§c R§6a§ez§ae§br§7 GUN§f §r", 291 => "§r§f §cS§6i§et§aa§bm§da§7 GUN §f§r", 278 => "§r§f §5U§dl§5t§di§5m§da§5t§de§f §r"];
		$this->heal = [260 => "§r§f⩝ §4B§ca§4n§cd§4a§cg§4e§7 DX§f ⩝§r"];
		$this->face = [271 => 35, 258 => 45, 257 => 60, 292 => 40, 291 => 55, 278 => 65, 260 => 80];
		$this->body = [298 => 89, 299 => 90, 306 => 91, 307 => 92, 301 => 92, 309 => 96];
		$this->armor = [298 => "§r§f §2S§ac§2o§ar§2p§ai§2o§an §7Head§f §r", 299 => "§r§f §2S§ac§2o§ar§2p§ai§2o§an §7Body§f §r", 306 => "§r§f §6C§ey§6b§er§6o§eg§7 Head§f §r", 307 => "§r§f §6C§ey§6b§er§6o§eg§7 Body§f §r", 301 => "§r§f⩨§3 แ§bห§3ว§bน§7แ§fห่§7ง§9ส§1า§9ย§1ล§9ม§f ⩨§r", 309 => "§r§f §cแ§6ห§eว§aน§7แ§fห่§7ง§bโ§dล§cหิ§6ต§f6ต§f §r"];
		$this->ammo = [262 => "§r§f §6A§em§6m§eo§f §r", 367 => "§r§f§c R§6a§en§ad§bo§dm§f ", 264 => "§r§f§6 T§ei§6c§ek§6e§et§f ", 353 => "§r§f§3 T§er§3a§bs§3h§f ", 388 => "§r§c§e SR§7 Stone§c §r", 265 => "§r§5§d SSR§7 Stone§5 §r", 263 => "§r§d§b SSSR§7 Stone§b §r", 266 => "§r§f§6 Coin§ebox§f §r"];
	}

	function onJoin (PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setJoinMessage("§r§8(§f⩋§8)§r ".$name." §aเข้าร่วมเซิฟเวอร์");
		$this->tap[$name] = [271 => 0, 258 => 0, 257 => 0, 292 => 0, 291 => 0, 278 => 0];
		$this->tick[$name] = [271 => 0, 258 => 0, 257 => 0, 292 => 0, 291 => 0, 278 => 0];
		$this->launch[$name] = [];
		if(!$this->money->get($player->getName())){
            $this->money->set($player->getName(), 500);
            $this->money->save();
        }
        if(!$this->rank->get($player->getName())){
            $this->rank->set($player->getName(), $this->rank1);
            $this->rank->save();
        }
        if(!$this->star->get($player->getName())){
            $this->star->set($player->getName(), $this->staroff);
            $this->star->save();
        }
        if(!$this->expc->get($player->getName())){
            $this->expc->set($player->getName(), 20);
            $this->expc->save();
        }
        if(!$this->rgb->get($player->getName())){
            $this->rgb->set($player->getName(), "off");
            $this->rgb->save();
        }
        if(!$this->expn->get($player->getName())){
            $this->expn->set($player->getName(), 0);
            $this->expn->save();
        }
        if(!$this->lvl->get($player->getName())){
            $this->lvl->set($player->getName(), 0);
            $this->lvl->save();
        }
        if(!$this->kill->get($player->getName())){
            $this->kill->set($player->getName(), 0);
            $this->kill->save();
        }
        if(!$this->death->get($player->getName())){
            $this->death->set($player->getName(), 0);
            $this->death->save();
        }
	}
	
	function onQuit (PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setQuitMessage("§r§8(§f⩕§8)§r ".$name." §cออกจากเซิฟเวอร์");
		$this->freeitem->save();
	}
	
	public function onWalk(PlayerMoveEvent $ev){
	    $p = $ev->getPlayer();
	    if($this->myStar($p) == $this->staron){
			$effect = Effect::getEffect(1);
			$effect->setVisible(false);
			$effect->setAmplifier(0.3);
			$effect->setDuration(99999999);
			$p->addEffect($effect);
		} else{
		    $p->removeEffect(1);
		} 
		return true;
	}
	
	public function onName(){
	    foreach($this->getServer()->getOnlinePlayers() as $p){
	        $group = $this->myRank($p);
	        $money = $this->myMoney($p);
	        $star = $this->myStar($p);
	        $levelexp = $this->myLevel($p);
	        $expn = $this->myExp($p);
	        $expc = $this->myNeed($p);
	        $t = str_repeat(" ", 85);
		    $n = str_repeat("\n", 20);
		    $online = $online = count(Server::getInstance()->getOnlinePlayers());
		    $monline = $this->getServer()->getMaxPlayers();
		    $id = $p->getInventory()->getItemInHand()->getId();
		    $ids = $p->getInventory()->getItemInHand()->getDamage();
		    $cn = $this->clan->getPlayerClan($p->getName());
		    if($this->clan){
                if(($cn = $this->clan->getPlayerClan($p->getName())) == null){
                    $clan = "ไม่มีแคลน";
                }else{
                    $clan = $cn;
                }
            }else{
                $clan = "ไม่มีระบบ";
            }
		    $p->sendTip($t."§r§f §l§cＮ§6ｉ§eｖ§aｅ§bａ§dＺ§r §f\n"
		    .$t."§r§f⩇ ชื่อ:§a ".$p->getName()."\n"
		    .$t."§r§f⩐ เงิน: §a".$money."\n"
		    .$t."§r§f⨹ แรงค์: §a".$group."\n"
		    .$t."§r§f เลเวล: §a".$levelexp."\n"
		    .$t."§r§f เอ็กพี: §a".$expn."/".$expc."\n"
		    .$t."§r§f แคลน: §a".$clan."\n"
		    .$t."§r§f สิทธิ์: §a".$star."\n"
		    .$t."§r§f ไอดี: §a".$id.":".$ids."\n"
		    .$t."§r§f⨔ ออนไลน์: §a".$online."/".$monline."\n"
		    .$t."§r§f⨫ Watchara Sangkakalo".$n);
	        if($this->myLevel($p) >= 0 && $this->myLevel($p) <= 4){
	            $p->setMaxHealth(100);
	        }
        if($this->myLevel($p) >= 5 && $this->myLevel($p) <= 9){
	            $p->setMaxHealth(102);
	        }
	        if($this->myLevel($p) >= 10 && $this->myLevel($p) <= 14){
	            $p->setMaxHealth(104);
	        }
	        if($this->myLevel($p) >= 15 && $this->myLevel($p) <= 24){
	           $p->setMaxHealth(108);
	        }
	        if($this->myLevel($p) >= 25 && $this->myLevel($p) <= 39){
	            $p->setMaxHealth(110);
	        }
	        if($this->myLevel($p) >= 40 && $this->myLevel($p) <= 54){
	            $p->setMaxHealth(112);
	        }
	        if($this->myLevel($p) >= 55 && $this->myLevel($p) <= 69){
	            $p->setMaxHealth(114);
	        }
	        if($this->myLevel($p) >= 70 && $this->myLevel($p) <= 84){
	            $p->setMaxHealth(116);
	        }
	        if($this->myLevel($p) >= 85 && $this->myLevel($p) <= 99){
	            $p->setMaxHealth(118);
	        }
	        if($this->myLevel($p) >= 100 && $this->myLevel($p) <= 999999){
	            $p->setMaxHealth(120);
	        }
	        if($this->myRgb($p) == "on"){
	            $rgb = mt_rand(0, 11);
	            switch($rgb){
	                case 0:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §4".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 1:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §c".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 2:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §6".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 3:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §e".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 4:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §2".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 5:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §a".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 6:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §3".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 7:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §b".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 8:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §5".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 9:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §d".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 10:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §1".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	                case 11:
	                    $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §9".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	                break;
	            }
	        } else{
	            $p->setNameTag("§8(§f§eเวล.§6".$levelexp."§8) §r".$p->getName()."§f⨍\n§f⩈ เลือด.".$p->getHealth()."/".$p->getMaxHealth()."\n§f สิทธิ์.§f".$star);
	        }
	        if($this->myLevel($p) == 5){
	            $this->setRank($p, $this->rank2);
	        }
	        if($this->myLevel($p) == 10){
	            $this->setRank($p, $this->rank3);
	        }
	        if($this->myLevel($p) == 15){
	            $this->setRank($p, $this->rank4);
	        }
	        if($this->myLevel($p) == 25){
	            $this->setRank($p, $this->rank5);
	        }
	        if($this->myLevel($p) == 40){
	            $this->setRank($p, $this->rank6);
	        }
	        if($this->myLevel($p) == 55){
	            $this->setRank($p, $this->rank7);
	        }
	        if($this->myLevel($p) == 70){
	            $this->setRank($p, $this->rank8);
	        }
	        if($this->myLevel($p) == 85){
	            $this->setRank($p, $this->rank9);
	        }
	        if($this->myLevel($p) == 100){
	            $this->setRank($p, $this->rank10);
	        }
	     }
	}
	public function onChat(PlayerChatEvent $ev){
	    $p = $ev->getPlayer();
	    $group = $this->myRank($p);
	    $money = $this->myMoney($p);
	    $star = $this->myStar($p);
	    $cn = $this->clan->getPlayerClan($p->getName());
		    if($this->clan){
                if(($cn = $this->clan->getPlayerClan($p->getName())) == null){
                    $clan = "ไม่มีแคลน";
                }else{
                    $clan = $cn;
                }
            }else{
                $clan = "ไม่มีระบบ";
            }
	    $levelexp = $this->myLevel($p);
	    $name = $p->getName();
	    $msg = $ev->getMessage();
	    $m1 = "§8[§f§6เ§eล§6เ§eว§6ล§f.".$levelexp."§8]§8[§f⨿§bยศ§f.".$group."§8]§8[§f§dแคลน§f.".$clan."§8]§8[§f§cสิทธิ์§f.".$this->myStar($p)."§8] §r".$name." §f ".$msg;
	    $ev->setFormat($m1);
	}
	public function newfreeCooldown($player){
		$this->freeitem->set(strtolower($player->getName()), 300);
		$this->a[strtolower($player->getName())] = strtolower($player->getName());
		$this->freeitem->save();
	}

	public function freetimer(){
		foreach($this->freeitem->getAll() as $player => $time){
			$time--;
			$this->freeitem->set($player, $time);
			$this->freeitem->save();
			if($time == 0){
				$this->freeitem->remove($player);
			unset($this->a[$player]);
				$this->freeitem->save();
			}
		}
	}
	
	public function myLevel($p){
        return $this->lvl->get($p->getName());
    }
    
	public function myStar($p){
        return $this->star->get($p->getName());
    }
    
    public function setStar($p, $count){
        $this->star->set($p->getName(), $count);
        $this->star->save();
    }
    
	public function myExp($p){
        return $this->expn->get($p->getName());
    }
    
    public function setLevel($p, $count){
        $this->lvl->set($p->getName(), $count);
        $this->lvl->save();
    }
    
    public function setExp($p, $count){
        $this->expn->set($p->getName(), $count);
        $this->expn->save();
    }
    
    public function reduceExp($p, $count){
        $this->expn->set($p->getName(), $this->expn->get($p->getName()) - $count);
        $this->expn->save();
    }
    
    public function myNeed($p){
        return $this->expc->get($p->getName());
    }
    
    public function myRgb($p){
        return $this->rgb->get($p->getName());
    }
    
    public function setRgb($p, $count){
        $this->rgb->set($p->getName(), $count);
        $this->rgb->save();
    }
    
    public function startLevel($p){
        $this->lvl->set($p->getName(), $this->lvl->get($p->getName()) + 1);
        $this->lvl->save();
        $this->getServer()->broadcastMessage($this->lvlsys."ยินดีด้วยคุณ §b".$p->getName()."§r อัพเลเวลเป็นเลเวล §e".$this->myLevel($p)."§f!!");
    }
    
    public function addExp($p, $count){
        $this->expn->set($p->getName(), $this->expn->get($p->getName()) + $count);
        $this->expn->save();
        if($p instanceof Player){
            $expn = $this->myExp($p);
            $expc = $this->myNeed($p);
            if($expn >= $expc){
                $this->startLevel($p);
                $this->reduceExp($p, $expc);
                $this->addExpCount($p, $expn);
            }
            if($expn <= 0){
                $this->setExp($p, 0);
            }
        }
    }
    
    public function addExpCount($p, $count){
        $this->expc->set($p->getName(), $this->expc->get($p->getName()) + $count);
        $this->expc->save();
    }
	public function myRank($p){
        return $this->rank->get($p->getName());
    }
    
    public function setRank($p, $count){
        $this->rank->set($p->getName(), $count);
        $this->rank->save();
    }
	
	public function myMoney($p){
        return $this->money->get($p->getName());
	}
    public function setMoney($p, $count){
        $this->money->set($p->getName(), $count);
        $this->money->save();
    }
    public function addMoney($p, $count){
        if($p instanceof Player){
            $this->money->set($p->getName(), $this->myMoney($p) + $count);
            $this->money->save();
        }
    }
    public function reduceMoney($p, $count){
        if($p instanceof Player){
            $this->money->set($p->getName(), $this->myMoney($p) - $count);
            $this->money->save();
        }
    }
	
	public function onDeath(PlayerDeathEvent $ev){
		$player = $ev->getEntity();
		$p = $ev->getPlayer();
		$cause = $player->getLastDamageCause();
		$ev->setDeathMessage(null);
		$this->death->set($p, $this->death->get($p) + 1);
		$this->death->save();
		$this->addExp($p, -5);
		if($p->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			if($p->getLastDamageCause()->getDamager() instanceof Player){
				$killer = $p->getLastDamageCause()->getDamager();
				$this->kill->set($killer, $this->kill->get($killer) + 1);
		        $this->kill->save();
		        $this->addExp($killer, 20);
			}
		}
		if($player instanceof Player){
		switch($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()){
				case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						$item = $e->getItemInHand();
						$itemname = $item->getName();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eถูกฆาตกรรมโดย§b ".$e->getName()." §eโดยใช้ §a[§6 $itemname §a]");
							break;
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eถูกฆาตกรรมโดย§b ".$e->getName()."");
							break;
						}else{
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยไม่มีสาเหตุ§c?");
						}
					}
					break;
				case EntityDamageEvent::CAUSE_PROJECTILE:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eถูกยิงโดย§b ".$e->getName()." §eโดยการใข้ธนู§c!");
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยลูกธนูปริศนา§c?");
							break;
						}else{
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยลูกธนูปริศนา§c?");
						}
					}
					break;
				case EntityDamageEvent::CAUSE_SUICIDE:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตาย§c!");
					break;
				case EntityDamageEvent::CAUSE_VOID:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตกออกนอกอวกาศตาย§c!");
					break;
				case EntityDamageEvent::CAUSE_FALL:
					if($cause instanceof EntityDamageEvent){
						if($cause->getFinalDamage() > 2){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตกจากที่สูงกระดูกหักตาย§c!");
							break;
						}
					}
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยไม่มีสาเหตุ§c?");
					break;

				case EntityDamageEvent::CAUSE_SUFFOCATION:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eติดบล๊อคตาย§c!");
					break;

				case EntityDamageEvent::CAUSE_LAVA:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eพยายามที่จะว่ายลาวาแทนน้ำ§c!");
					break;

				case EntityDamageEvent::CAUSE_FIRE:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eไฟไหม้ตาย§c!");
					break;

				case EntityDamageEvent::CAUSE_FIRE_TICK:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eไฟไหม้ตัวตาย§c!");
					break;

				case EntityDamageEvent::CAUSE_DROWNING:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eจมน้ำขึ้นอืดตาย§c!");
					break;

				case EntityDamageEvent::CAUSE_CONTACT:
					if($cause instanceof EntityDamageByBlockEvent){
						if($cause->getDamager()->getId() === Block::CACTUS){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยกระบองเพรช§c โง่ชิปหาย!");
						}
					}
					break;

				case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
				case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eถูกยิงโดย§b ".$e->getName()." §eโดยการใช้ธนู§c!");
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยระเบิด§c!");
							break;
						}
					}else{
						$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยไม่มีสาเหตุ§c?");
					}
					break;

				case EntityDamageEvent::CAUSE_MAGIC:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยไม่มีสาเหตุ§c?");
					break;

				case EntityDamageEvent::CAUSE_CUSTOM:
					$this->getServer()->broadcastMessage($this->alert.$player->getName()." §eตายโดยไม่มีสาเหตุ§c?");
					break;
		}
	}
 }
 
    function onCommand(CommandSender $p, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case "farm":
                switch($args[0]){
                    case "1":
                        if($p->getInventory()->contains(Item::get(353,0,20))){
                            $p->getInventory()->removeItem(Item::get(353,0,20));
                            $item = Item::get(388, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "2":
                        if($p->getInventory()->contains(Item::get(388,0,10))){
                            $p->getInventory()->removeItem(Item::get(388,0,10));
                            $item = Item::get(265, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "3":
                        if($p->getInventory()->contains(Item::get(265,0,5))){
                            $p->getInventory()->removeItem(Item::get(265,0,5));
                            $item = Item::get(263, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "4":
                        if($p->getInventory()->contains(Item::get(263,0,10))){
                            $p->getInventory()->removeItem(Item::get(263,0,10));
                            $item = Item::get(306, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "5":
                        if($p->getInventory()->contains(Item::get(263,0,10))){
                            $p->getInventory()->removeItem(Item::get(263,0,10));
                            $item = Item::get(307, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "6":
                        if($p->getInventory()->contains(Item::get(263,0,15))){
                            $p->getInventory()->removeItem(Item::get(263,0,15));
                            $item = Item::get(258, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f§2 แ§aล§2ก§aข§2อ§aง§2ฟ§aา§aร์§2ม§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                }
            break;
            case "event":
                switch($args[0]){
                    case "1":
                        if($p->getInventory()->contains(Item::get(388,0,32))){
                            $p->getInventory()->removeItem(Item::get(388,0,32));
                            $item = Item::get(292, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "2":
                        if($p->getInventory()->contains(Item::get(265,0,32))){
                            $p->getInventory()->removeItem(Item::get(265,0,32));
                            $item = Item::get(291, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "3":
                        if($p->getInventory()->contains(Item::get(263,0,32))){
                            $p->getInventory()->removeItem(Item::get(263,0,32));
                            $item = Item::get(278, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r แลกของสำเร็จ!");
                        } else{
                            $p->sendMessage("§8(§f §5อี§dเ§5ว้§dน§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                }
            break;
            case "token":
                switch($args[0]){
                    case "1":
                        if($p->getInventory()->contains(Item::get(264,0,32))){
                            $p->getInventory()->removeItem(Item::get(264,0,32));
                            $item = Item::get(301, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f §6แ§eห§6ว§eน§6เ§eท§6พ§8)§r §fแลกของสำเร็จ");
                        } else{
                            $p->sendMessage("§8(§f §6แ§eห§6ว§eน§6เ§eท§6พ§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                    case "2":
                        if($p->getInventory()->contains(Item::get(264,0,64))){
                            $p->getInventory()->removeItem(Item::get(264,0,64));
                            $item = Item::get(309, 0, 1);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f §6แ§eห§6ว§eน§6เ§eท§6พ§8)§r §fแลกของสำเร็จ");
                        } else{
                            $p->sendMessage("§8(§f §6แ§eห§6ว§eน§6เ§eท§6พ§8)§r §fไม่มีของในการแลก");
                        }
                    break;
                }
            break;
            case "shop":
                switch($args[0]){
                    case "1":
                        if($this->myMoney($p) >= 32){
                            $this->reduceMoney($p, 32);
                            $item = Item::get(260, 0, 64);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fซื้อของสำเร็จ");
                        } else{
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fเงินของคุณไม่พอ");
                        }
                    break;
                    case "2":
                        if($this->myMoney($p) >= 32){
                            $this->reduceMoney($p, 32);
                            $item = Item::get(262, 0, 64);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fซื้อของสำเร็จ");
                        } else{
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fเงินของคุณไม่พอ");
                        }
                    break;
                    case "3":
                        if($this->myMoney($p) >= 50){
                            $this->reduceMoney($p, 50);
                            $item = Item::get(272, 0, 64);
                            $p->getInventory()->addItem($item);
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fซื้อของสำเร็จ");
                        } else{
                            $p->sendMessage("§8(§f⩝ §eร้านค้า§8)§r §fเงินของคุณไม่พอ");
                        }
                    break;
                }
            break;
            case "free":
                   	if(isset($this->a[strtolower($p->getName())])){
                   	    $p->sendMessage($this->free."กรุณารอ§e ". $this->freeitem->get(strtolower($p->getName())). " §fวินาที");
		            }else{
		                $i = Item::get(302, 0, 1);
		                $p->getInventory()->addItem($i);
		                $i = Item::get(303, 0, 1);
		                $p->getInventory()->addItem($i);
		                $i = Item::get(269, 0, 1);
		                $p->getInventory()->addItem($i);
		                $i = Item::get(260, 0, 64);
		                $p->getInventory()->addItem($i);
		                $i = Item::get(262, 0, 64);
		                $p->getInventory()->addItem($i);
		                $p->sendMessage($this->free."รับของฟรีเรียบร้อย!");
                        $this->newfreeCooldown($p);
		            }
            break;
            case "heal":
                if(!$p->hasPermission("heal.cmd")){
                    $p->sendMessage($this->heal."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    $p->setHealth($p->getMaxHealth());
                    $p->sendMessage($this->heal.$p->getName()." §fฮีลเลือด");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->heal."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        $player->setHealth($player->getMaxHealth());
                        $player->sendMessage($this->heal.$player->getName()." §bฮีลเลือด");
				    }
                }
                }
            break;
            case "v":
                if(!$p->hasPermission("vanish.cmd")){
                    $p->sendMessage($this->vanish."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    foreach($this->getServer()->getOnlinePlayers() as $online){ 
                        $online->hidePlayer($p);
                    }
                    $p->sendMessage($this->vanish.$p->getName()." §aซ่อนตัวจากคนอื่น");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->vanish."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        foreach($this->getServer()->getOnlinePlayers() as $online){ 
                        $online->hidePlayer($player);
                        }
                        $player->sendMessage($this->vanish.$player->getName()." §bซ่อนตัวจากคนอื่น");
				    }
                }
                }
            break;
            case "unv":
                if(!$p->hasPermission("vanish.cmd")){
                    $p->sendMessage($this->vanish."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    foreach($this->getServer()->getOnlinePlayers() as $online){ 
                        $online->showPlayer($p);
                    }
                    $p->sendMessage($this->vanish.$p->getName()." §aปิดการซ่อนตัวจากคนอื่น");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->vanish."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        foreach($this->getServer()->getOnlinePlayers() as $online){ 
                        $online->showPlayer($player);
                        }
                        $player->sendMessage($this->vanish.$player->getName()." §bปิดการซ่อนตัวจากคนอื่น");
				    }
                }
                }
            break;
            case "gms":
                if(!$p->hasPermission("gms.cmd")){
                    $p->sendMessage($this->gamemode."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    $p->setGamemode(0);
                    $p->sendMessage($this->gamemode.$p->getName()." §aโหมดเอาชีวิตรอด");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->gamemode."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        $player->setGamemode(0);
                        $player->sendMessage($this->gamemode.$player->getName()." §bโหมดเอาชีวิตรอด");
				    }
                }
                }
            break;
            case "gmc":
                if(!$p->hasPermission("gmc.cmd")){
                    $p->sendMessage($this->gamemode."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    $p->setGamemode(1);
                    $p->sendMessage($this->gamemode.$p->getName()." §bโหมดสร้างสรรค์");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->gamemode."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        $player->setGamemode(1);
                        $player->sendMessage($this->gamemode.$player->getName()." §bโหมดสร้างสรรค์");
				    }
                }
                }
            break;
            case "gma":
                if(!$p->hasPermission("gma.cmd")){
                    $p->sendMessage($this->gamemode."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    $p->setGamemode(2);
                    $p->sendMessage($this->gamemode.$p->getName()." §dโหมดผจญภัย");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->gamemode."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        $player->setGamemode(2);
                        $player->sendMessage($this->gamemode.$player->getName()." §dโหมดผจญภัย");
				    }
                }
                }
            break;
            case "gmspec":
                if(!$p->hasPermission("gmspec.cmd")){
                    $p->sendMessage($this->gamemode."§cคุณไม่มีสิทธิ์ในการใช้คำสั่งนี้");
                } else{
                if(!isset($args[0])){
                    $p->setGamemode(3);
                    $p->sendMessage($this->gamemode.$p->getName()." §eโหมดผีล่องหน");
                } else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(($player) == null){
                        $p->sendMessage($this->gamemode."§cผู้เล่นคนนี้ไม่ออนไลน์");
				    } else{
				        $player->setGamemode(3);
                        $player->sendMessage($this->gamemode.$player->getName()." §eโหมดผีล่องหน");
				    }
                }
                }
            break;
            case "mymoney":
                $p->sendMessage($this->tag2."คุณมีเงิน §f".number_format($this->money->get($p->getName()))." §fบาท");
			    return true;
			break;
		    case "pay":
		        if(!isset($args[0])){
		            $p->sendMessage($this->tag2."§f/pay <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
			    if(isset($args[1])){
			        if(!is_numeric($args[1])){
			            $p->sendMessage($this->tag2."§fจำนวนเงินเขียนเป็นตัวเลขเท่านั้น");
				        return true;
				    }
				}
				$player = $this->getServer()->getPlayer($args[0]);
				if(!$player){
				    if($this->money->get($args[0])){
				        if($this->money->get($p->getName()) >= $args[1]){
				            $this->money->set($p->getName(), $this->money->get($p->getName()) - $args[1]);
						    $this->money->set($args[0], $this->money->get($args[0]) + $args[1]);
						    $this->money->save();
						    $p->sendMessage($this->tag2."คุณได้โอนเงินให้ §f".$args[0]." §fจำนวน §f".number_format($args[1])." §fบาท");
				        }else{
				            $p->sendMessage($this->tag2."ขออภัย เงินของคุณไม่พอ!!");
						    return true;
						}
				    }else{
				        $p->sendMessage($this->tag2."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
				    if($this->money->get($p->getName()) >= $args[1]){
				        $this->money->set($p->getName(), $this->money->get($p->getName()) - $args[1]);
				        $this->money->set($player->getName(), $this->money->get($player->getName()) + $args[1]);
				        $this->money->save();
				        $p->sendMessage($this->tag2."คุณได้โอนเงินให้ §f".$player->getName()." §fจำนวน §f".number_format($args[1])." §fบาท");
				        $player->sendMessage($this->tag2."ผู้เล่น §f".$p->getName()." §fโอนเงินให้คุณจำนวน §f".number_format($args[1])." §fบาท");
					}else{
					    $p->sendMessage($this->tag2."ขออภัย เงินของคุณไม่พอ!!");
					    return true;
					}
				}
			break;
			case "paystar":
		        if(!isset($args[0])){
		            $p->sendMessage($this->tag4."§f/paystar <ชื่อผู้เล่น>");
				    return true;
				}
				$player = $this->getServer()->getPlayer($args[0]);
				if(!$player){
				    if($this->star->get($args[0])){
				        if($this->star->get($p->getName()) == $this->staron){
				            $this->star->set($p->getName(), $this->staroff);
						    $this->star->set($player->getName(), $this->staron);
						    $this->star->save();
						    $p->sendMessage($this->tag3."คุณได้โอนดาวให้ §f".$args[0]);
				        }else{
				            $p->sendMessage($this->tag3."ขออภัย เงินของคุณไม่พอ!!");
						    return true;
						}
				    }else{
				        $p->sendMessage($this->tag3."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
				    if($this->money->get($p->getName()) >= $args[1]){
				        $this->star->set($p->getName(), $this->staroff);
						    $this->star->set($player->getName(), $this->staron);
						    $this->star->save();
				        $p->sendMessage($this->tag3."คุณได้โอนดาวให้ §f".$player->getName());
				        $player->sendMessage($this->tag3."ผู้เล่น §f".$p->getName()." §fโอนเงินดาวให้คุณ");
					}else{
					    $p->sendMessage($this->tag3."ขออภัย เงินของคุณไม่พอ!!");
					    return true;
					}
				}
			break;
		    case "setmoney":
		        if(!$p->hasPermission("setmoney.cmd")){
		            $p->sendMessage($this->tag2."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->tag2."/setmoney <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
				if(isset($args[1])){
				    if(!is_numeric($args[1])){
				        $p->sendMessage($this->tag2."§fจำนวนเงินเขียนเป็นตัวเลขเท่านั้น");
					    return true;
					}
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->money->get($args[0])){
			            $this->money->set($args[0], $args[1]);
					    $this->money->save();
					    $p->sendMessage($this->tag2."คุณได้เซ็ตเงินผู้เล่น §f".$args[0]." §fเป็น §f".number_format($args[1])." §fบาท");
					}else{
					    $p->sendMessage($this->tag2."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->tag2."คุณได้เซ็ตเงินผู้เล่น §f".$player->getName()." §fเป็น §f".number_format($args[1])." §fบาท");
					$player->sendMessage($this->tag2."แอดมินได้เซ็ตเงินของคุณเป็น §f".number_format($args[1])." §fบาท");
					$this->money->set($player->getName(), $args[1]);
					$this->money->save();
					return true;
				}
			break;
			case "setlvl":
		        if(!$p->hasPermission("setlvl.cmd")){
		            $p->sendMessage($this->lvlsys."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->lvlsys."/setlvl <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
				if(isset($args[1])){
				    if(!is_numeric($args[1])){
				        $p->sendMessage($this->lvlsys."§fจำนวนเงินเขียนเป็นตัวเลขเท่านั้น");
					    return true;
					}
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->lvl->get($args[0])){
			            $this->lvl->set($args[0], $args[1]);
					    $this->lvl->save();
					    $p->sendMessage($this->lvlsys."คุณได้เซ็ตเลเวลผู้เล่น §f".$args[0]." §fเป็น §f".number_format($args[1])." §fเลเวล");
					}else{
					    $p->sendMessage($this->lvlsys."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->lvlsys."คุณได้เซ็ตเลเวลผู้เล่น §f".$player->getName()." §fเป็น §f".number_format($args[1])." §fเลเวล");
					$player->sendMessage($this->lvlsys."แอดมินได้เซ็ตเลเวลของคุณเป็น §f".number_format($args[1])." §fเลเวล");
					$this->lvl->set($player->getName(), $args[1]);
					$this->lvl->save();
					return true;
				}
			break;
			case "setstar":
			    switch($args[1]){
			        case "on":
			            if(!$p->hasPermission("setstar.cmd")){
		            $p->sendMessage($this->tag3."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->tag3."/setstar <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->star->get($args[0])){
			            $this->star->set($args[0], $this->staron);
					    $this->star->save();
					    $p->sendMessage($this->tag3."คุณได้เซ็ตดาว §f".$args[0]." ได้สิทธิ์ดาว");
					}else{
					    $p->sendMessage($this->tag3."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->tag3."คุณได้เซ็ตดาว §f".$player->getName()." §fได้สิทธิ์ดาว");
					$player->sendMessage($this->tag3."แอดมินได้เซ็ตดาวของคุณเป็นดาว");
					$this->star->set($player->getName(), $this->staron);
					$this->star->save();
					return true;
				}
			        break;
			        case "off":
			            if(!$p->hasPermission("setstar.cmd")){
		            $p->sendMessage($this->tag3."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->tag3."/setstar <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->star->get($args[0])){
			            $this->star->set($args[0], $this->staroff);
					    $this->star->save();
					    $player->removeEffect(1);
					    $player->removeEffect(11);
					    $p->sendMessage($this->tag3."คุณได้ปลดดาว §f".$args[0]." ถอนสิทธิ์ดาว");
					}else{
					    $p->sendMessage($this->tag3."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->tag3."คุณได้ปลดดาว §f".$player->getName()." §fถอนสิทธิ์ดาว");
					$player->sendMessage($this->tag3."แอดมินได้ปลดดาวของคุณ");
					$this->star->set($player->getName(), $this->staroff);
					$this->star->save();
					$player->removeEffect(1);
					$player->removeEffect(11);
					return true;
			    }
			        break;
        }
			break;
			case "setrgb":
			    switch($args[1]){
			        case "on":
			            if(!$p->hasPermission("setrgb.cmd")){
		            $p->sendMessage($this->color."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->color."/setrgb <ชื่อผู้เล่น> <on/off>");
				    return true;
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->rgb->get($args[0])){
			            $this->setRgb($args[0], "on");
					    $p->sendMessage($this->color."คุณได้เซ็ตชื่อสีให้ §f".$args[0]);
					}else{
					    $p->sendMessage($this->color."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->color."คุณได้เซ็ตชื่อสีให้ §f".$player->getName());
					$player->sendMessage($this->color."แอดมินได้เซ็ตชื่อสีให้คุณ!");
					$this->setRgb($player, "on");
					return true;
				}
			        break;
			        case "off":
			            if(!$p->hasPermission("setrgb.cmd")){
		            $p->sendMessage($this->color."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
				if(!isset($args[0])){
				    $p->sendMessage($this->color."/setrgb <ชื่อผู้เล่น> <on/off>");
				    return true;
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->rgb->get($args[0])){
			            $this->setRgb($args[0], "off");
					    $p->sendMessage($this->color."คุณได้ปิดชื่อสีของ §f".$args[0]);
					}else{
					    $p->sendMessage($this->color."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->color."คุณได้ปิดชื่อสีของ §f".$player->getName());
					$player->sendMessage($this->color."แอดมินได้ปิดชื่อสีของคุณ!");
					$this->setRgb($player, "off");
					return true;
				}
			        break;
        }
			break;
	        case "givemoney":
	            if(!$p->hasPermission("givemoney.cmd")){
	                $p->sendMessage($this->tag2."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
			    if(!isset($args[0])){
				    $p->sendMessage($this->tag2."/givemoney <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
			    if(isset($args[1])){
				    if(!is_numeric($args[1])){
				        $p->sendMessage($this->tag2."§fจำนวนเงินเขียนเป็นตัวเลขเท่านั้น");
					    return true;
					}
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->money->get($args[0])){
			            $this->money->set($args[0], $this->money->get($args[0]) + $args[1]);
			            $this->money->save();
					    $p->sendMessage($this->tag2."คุณได้เพิ่มเงินผู้เล่น §f".$args[0]." §fจำนวน §f".number_format($args[1])." §fบาท");
					}else{
					    $p->sendMessage($this->tag2."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->tag2."คุณได้เพิ่มเงินผู้เล่น §f".$player->getName()." §fเป็น §f".number_format($args[1])." §fบาท");
					$player->sendMessage($this->tag2."แอดมินได้เพิ่มเงินของคุณ §f".number_format($args[1])." §fบาท");
					$this->money->set($player->getName(), $this->money->get($player->getName()) + $args[1]);
					$this->money->save();
					return true;
				}
			break;
			case "givelvl":
	            if(!$p->hasPermission("givelvl.cmd")){
	                $p->sendMessage($this->lvlsys."ไม่สามารถใช้คำสั่งนี้ได้");
				    return true;
				}
			    if(!isset($args[0])){
				    $p->sendMessage($this->lvlsys."/givelvl <ชื่อผู้เล่น> <จำนวนเงิน>");
				    return true;
				}
			    if(isset($args[1])){
				    if(!is_numeric($args[1])){
				        $p->sendMessage($this->lvlsys."§fจำนวนเงินเขียนเป็นตัวเลขเท่านั้น");
					    return true;
					}
				}
			    $player = $this->getServer()->getPlayer($args[0]);
			    if(!$player){
			        if($this->lvl->get($args[0])){
			            $this->lvl->set($args[0], $this->lvl->get($args[0]) + $args[1]);
			            $this->lvl->save();
					    $p->sendMessage($this->lvlsys."คุณได้เพิ่มเลเวลผู้เล่น §f".$args[0]." §fจำนวน §f".number_format($args[1])." §fเลเวล");
					}else{
					    $p->sendMessage($this->lvlsys."ขออภัย ไม่พบผู้เล่น!!");
					    return true;
					}
				}else{
					$p->sendMessage($this->lvlsys."คุณได้เพิ่มเลเวลผู้เล่น §f".$player->getName()." §fเป็น §f".number_format($args[1])." §fเลเวล");
					$player->sendMessage($this->lvlsys."แอดมินได้เพิ่มเลเวลของคุณ §f".number_format($args[1])." §fเลเวล");
					$this->lvl->set($player->getName(), $this->lvl->get($player->getName()) + $args[1]);
					$this->lvl->save();
					return true;
				}
			break;
        }
    }

	function onReceive (DataPacketReceiveEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$pk = $event->getPacket();
		if ($pk::NETWORK_ID == Info::PLAYER_ACTION_PACKET) {
			if ($pk->action == PlayerActionPacket::ACTION_ABORT_BREAK) {
				$id = $player->getInventory()->getItemInHand()->getId();
				switch ($id) {
					case 271:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
					case 291:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
					case 258:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
					case 292:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
					case 278:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
					case 257:
					$itemok = Item::get(262, 0, 1);
					if($player->getInventory()->contains($itemok)){
						$ifworld = ('spawn');
						if($ifworld == $player->level->getName()){
							$player->sendPopup("§l§cไม่§6สา§eมา§aรถ§bยิง§cปืน§6ใน§eโลก§f $ifworld §aได้§r");
							} else {
							$this->tick[$name][$id]++;
							if ($this->tick[$name][$id] === 15) $this->tick[$name][$id] = 0;
							else return false;
							$this->tap[$name][$id]++;
							if ($this->tap[$name][$id] <= $this->bullet[$id]) {
								$O = -sin($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$N = -sin($player->pitch/180*M_PI)*3;
								$M = cos($player->yaw/180*M_PI)*cos($player->pitch/180*M_PI)*3;
								$eid = mt_rand(100000, 10000000);
								$this->pos[$eid] = new Vector3($player->x+$O/2, $player->y+$player->getEyeHeight()+$N/2-0.02, $player->z+$M/2);
								$this->motion[$eid] = new Vector3($O,$N,$M);
								$this->Projectile[$eid] = Server::getInstance()->getOnlinePlayers();
								$player->getLevel()->addSound(new ExplodeSound(new Vector3($player->x, $player->y, $player->z)));
								$this->move($player,$eid, $this->face[$id],"k-2");
								Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"close"],[$eid]),50);
								$bullet = $this->bullet[$id] - $this->tap[$name][$id];
								$player->sendPopup("§d• §7".$bullet." §d•");
								if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20, $player->getInventory()->removeItem($itemok));
							}else{
								$player->sendPopup("§eรี§6โห§eลด...");
							}
							}
						}else{
							$player->sendPopup("§cไม่มีกระสุน...");
						}
					break;
                }
			}
		}
	}

	function onTap (PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$item = $player->getInventory()->getItemInHand();
		$id = $item->getId();
		$block = $event->getBlock();
		if ($item->getId() == 260) $event->setCancelled();
		switch ($id) {
			case 260:
				$this->tap[$name][$id]++;
				if ($this->tap[$name][$id] <= $this->bullet[$id]) {
					$player->setHealth($player->getHealth() + $this->face[$id]);
					$player->setFood(20);
					$player->getLevel()->addSound(new AnvilUseSound(new Vector3($player->x,$player->y,$player->z)), [$player]);
					$bullet = $this->bullet[$id] - $this->tap[$name][$id];
					$player->getInventory()->removeItem(Item::get(260, 0, 1));
					$player->sendPopup("".$bullet."");
					if ($bullet <= 0) Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"reload"],[$player,$id]), $this->reload[$id]*20);
				}else{
					$player->sendPopup("§6กำลังเตรียมยา...");
				}
			break;

		}
		if($player->getInventory()->getItemInHand()->getId() == 266){
		    $player->getInventory()->removeItem(Item::get(266,0,1));										
            $prize = rand(1,1);
            switch($prize){
                case 1:
                    $coin = rand(1,100);
                    $this->addMoney($player, $coin);
		            $player->sendMessage("§8(§6Coin§ebox§8)§f คุณเปิดได้เงิน §e".$coin." §fบาท");
                break;
            }
		}
		if($player->getInventory()->getItemInHand()->getId() == 367){
		    $player->getInventory()->removeItem(Item::get(367,0,1));										
            $prize = rand(1,50);
            switch($prize){
                case 1:
                    $item = Item::get(263, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 2:
                    $item = Item::get(264, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 3:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 4:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 5:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 6:
                    $item = Item::get(388, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 7:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 8:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 9:
                    $item = Item::get(264, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 10:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 11:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 12:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 13:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 14:
                    $item = Item::get(388, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 15:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 16:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 17:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 18:
                    $item = Item::get(388, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 19:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 20:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 21:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 22:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 23:
                    $item = Item::get(265, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 24:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 25:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 26:
                    $item = Item::get(265, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 27:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 28:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 29:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 30:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 31:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 32:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 33:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 34:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 35:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 36:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 37:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 38:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 39:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 40:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 41:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 42:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 43:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 44:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 45:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 46:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 47:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 48:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 49:
                    $item = Item::get(266, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                case 50:
                    $item = Item::get(353, 0, 1);
                    $player->getInventory()->addItem($item);
		            $player->sendMessage("§8(§cRand§6omb§eox§8)§f คุณเปิดได้ §e".$item->getName());
                break;
                
            }
        }
	}

	function onItem (EntityInventoryChangeEvent $event)
	{
		if (!$event->getEntity() instanceof Player) return false;
		$item = $event->getNewItem();
		if (isset($this->weapon[$item->getId()])) {
			$tag = "§r".$this->weapon[$item->getId()];
			$cri = $this->face[$item->getId()] + 5;
			$lore= "§r§f ดาเมจ:§f ".$this->face[$item->getId()]."\n§r§f กระสุน: §f".$this->bullet[$item->getId()]." เม็ด\n§r§f รีโหลด:§f ".$this->reload[$item->getId()]." วินาที\n§r§f⩯ ดาเมจคริ: §f".$cri."\n§r§f อัตราคริ:§f ".$this->crirate[$item->getId()]."%";
			$item->setCustomName($tag);
			$item->setLore([$lore]);
			$event->setNewItem($item);
		}elseif (isset($this->heal[$item->getId()])) {
			$tag = "§r".$this->heal[$item->getId()];
			$lore = "§r§f⩈ ฟื้นฟูเลือด:§e ".$this->face[$item->getId()]."\n§r§f รีโหลด: §a".$this->reload[$item->getId()]."§r วินาที";
			$item->setCustomName($tag);
			$item->setLore([$lore]);
			$event->setNewItem($item);
		}elseif (isset($this->armor[$item->getId()])) {
			$tag = "§r".$this->armor[$item->getId()];
			$lore = "§r§f ความถึก:§b ".$this->body[$item->getId()];
			$item->setCustomName($tag);
			$item->setLore([$lore]);
			$event->setNewItem($item);
		}elseif (isset($this->ammo[$item->getId()])) {
			$tag = "§b".$this->ammo[$item->getId()];
			$item->setCustomName($tag);
			$event->setNewItem($item);
		}
	}

	function onDamage (EntityDamageEvent $event)
	{
		if($event instanceof EntityDamageByEntityEvent) {
            $hit = $event->getEntity();
            $damager = $event->getDamager();
            $event->setKnockBack(0);
            $item = $damager->getInventory()->getItemInHand();
            if($event instanceof EntityDamageByEntityEvent){
			if($event->getDamager() instanceof Player && $event->getEntity() instanceof Player){
				if(isset($this->face[$item->getId()])){
						        $persend = $this->crirate[$item->getId()];
						        $random = rand(1, 100);
						        if($random <= $persend){
						            $event->setDamage($event->getDamage() + 5);
						            $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
						            $criticalParticle = new FloatingTextParticle($pos, "", "§6C§er§6i§et§6i§ec§6a§el");
			                        $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $criticalParticle, $event->getEntity()->getLevel(), $event), 1);
			                        $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
			                        $damageParticle = new FloatingTextParticle($pos, "", "§cD§4M§cG §f-".$event->getDamage());
			                        $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $damageParticle, $event->getEntity()->getLevel(), $event), 1);
						        }else{
						            $event->setDamage($event->getDamage());
			                        $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
                                    $damageParticle = new FloatingTextParticle($pos, "", "§cD§4M§cG §f-".$event->getDamage());
                                    $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $damageParticle, $event->getEntity()->getLevel(), $event), 1);
						        }
						    }
			}
		}
            
        }
	}

	function onMove (PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$x = $player->x;
		$y = $player->y - 1;
		$z = $player->z;
		$x = round($x);
		$y = round($y);
		$z = round($z);
		$key = $x.":".$y.":".$z;
		if (array_key_exists($key, $this->vec)) {
			if ($this->vec[$key] != $name) {
				$damager = Server::getInstance()->getPlayer($this->vec[$key]);
				$this->ExplodeDamage(new Vector3($x,$y,$z),$player->getLevel(),$damager,$this->face[$id]);
			}
		}
	}

	function isBreak ($player, $item)
	{
		if ($player->getInventory()->getItemInHand()->getId() === 0) $this->tap[strtolower($player->getName())][$item->getId()] = 0;
	}

	function ExplodeDamage (Vector3 $v3, $level, $damager, $damage, $value = true)
	{//爆破によるダメージ
		$x = $v3->x;
		$y = $v3->y;
		$z = $v3->z;
		$players = Server::getInstance()->getOnlinePlayers();
		$this->explode($v3, $level, 3);
		foreach ($players as $player) {
			$px = $player->x;
			$py = $player->y;
			$pz = $player->z;
			$k = sqrt(pow($x-$px,2)+pow($y-$py,2)+pow($z-$pz,2));
			if ($k < 5) {
				$d = $this->getFinalDamage($player, $damage);
				$ev = ($damager instanceof Player) ? (new EntityDamageByEntityEvent($damager, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $d)) : (new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, $d));
				$player->attack($d, $ev);
				if ($value) $player->setOnFire(5);
			}
		}
		unset($this->vec[$x.":".$y.":".$z]);
	}

	function explode (Vector3 $v3, $level, $size)
	{//爆発
		$pk = new ExplodePacket();
		$pk->x = $v3->x;
		$pk->y = $v3->y;
		$pk->z = $v3->z;
		$pk->radius = $size;
		$pk->records = [];
		$level->addChunkPacket($v3->x >> 4, $v3->z >> 4, $pk);
	}

	function move ($player,$eid,$damage,$type,$value = true)
	{
		//$typeは将来使うかもだから一応keep
		if (!isset($this->pos[$eid])) return false;
		$H = $this->pos[$eid];
		$F = $this->motion[$eid];
		if ($value) $F->y-=0.00;
		$this->pos[$eid] = new Vector3($H->x+$F->x, $H->y+$F->y, $H->z+$F->z);
		$player->level->addParticle(new FlameParticle($H,204,0,0));
		for ($K = 1; $K < 4; $K++) {
			$H = new Vector3($H->x+$F->x/$K, $H->y+$F->y/$K, $H->z+$F->z/$K);
			if ($player->level->getBlock($H)->isSolid()) {
				$this->close($eid);
				break;
				return false;
			}
			foreach (Server::getInstance()->getOnlinePlayers() as $p) {
				$x = $p->x;
				$y = $p->y;
				$z = $p->z;
				$c = new Vector2($x, $z);
				if ((new Vector2($H->x, $H->z))->distance($c) <= 1.2 && $H->y-$p->y <= 2.6 && $H->y-$p->y > 0) {
					if($p->getName() != $player->getName()) {
						$d = $this->getFinalDamage($p, $damage);
						$ev = new EntityDamageByEntityEvent($player, $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $d);
						$p->attack($d, $ev);
					}
					return $this->close($eid);
				}
			}
		}
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new Callback([$this,"move"],[$player,$eid,$damage]),1);
	}

	function close($eid)
	{
		if (!isset($this->Projectile[$eid])) {
			if (isset($this->pos[$eid])) unset($this->pos[$eid]);
			return true;
		}
		unset ($this->Projectile[$eid]);
		if(isset($this->pos[$eid])) unset($this->pos[$eid]);
	}

	function launch ($name,$id)
	{
		if (isset($this->launch[$name][$id])) $this->launch[$name][$id] = false;
	}

	function reload ($player, $id, $value = false)
	{
		$name = $player->getName();
		$this->tap[$name][$id] = 0;
		$item = [];
		$player->sendPopup("โหลดเสร็จเเล้ว");
		if ($value) {
			if ($player instanceof Player) {
				$item = Item::get($id,0,0);
				if ($player->getInventory() != null) {
					if (!$player->getInventory()->contains($item)) $player->getInventory()->addItem($item);
				}
			}
		}
	}

	function getFinalDamage($A,$D)
	{
		$S = [Item::LEATHER_CAP=>1.4,Item::LEATHER_TUNIC=>1.4,Item::LEATHER_PANTS=>0,Item::LEATHER_BOOTS=>1.2,Item::CHAIN_HELMET=>1.4,Item::CHAIN_CHESTPLATE=>1.4,Item::CHAIN_LEGGINGS=>0,Item::CHAIN_BOOTS=>0,Item::GOLD_HELMET=>1.9,Item::GOLD_CHESTPLATE=>1.9,Item::GOLD_LEGGINGS=>0,Item::GOLD_BOOTS=>0,Item::IRON_HELMET=>2.2,Item::IRON_CHESTPLATE=>2.2,Item::IRON_LEGGINGS=>0,Item::IRON_BOOTS=>1.5,Item::DIAMOND_HELMET=>5.8,Item::DIAMOND_CHESTPLATE=>5.8,Item::DIAMOND_LEGGINGS=>0,Item::DIAMOND_BOOTS=>1.5];
		$T = 0;
		foreach($A->getInventory()->getArmorContents() as $g => $K){
			if(isset($S[$K->getId()])){
				$T+=$S[$K->getId()];
			}
		}
		$D+=-floor($D*$T*0.04);
		if ($D<1) $D=1;
		return$D;
	}
	
	public function eventCheck(FloatingTextParticle $particle, Level $level, $event) {
        if ($event instanceof EntityDamageEvent) { 
            if ($event->isCancelled()) { 
                return;
            } 
        }
        $level->addParticle($particle); 
        $this->getServer()->getScheduler()->scheduleDelayedTask(new DeleteParticlesTask($this, $particle, $event->getEntity()->getLevel()), 20);
    }
    
    public function deleteParticles(FloatingTextParticle $particle, Level $level) {
        $particle->setInvisible();
        $level->addParticle($particle);
    }
}

class Callback extends Task {

	function __construct(callable $callable, array $args = [])
    {
        $this->callable = $callable;
        $this->args = $args;
        $this->args[] = $this;
    }

    function getCallable()
    {
        return $this->callable;
    }

    function onRun ($tick)
    {
        call_user_func_array($this->callable, $this->args);
    }
}
