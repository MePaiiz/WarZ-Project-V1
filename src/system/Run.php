<?php

namespace system;

use pocketmine\scheduler\Task;

use system\Main;

class Run extends Task{
	
	public function __construct(Main $main){
		$this->main = $main;
		}
	public function onRun($currentTick){
		$this->main->onName();
		$this->main->freetimer();
		}
	}