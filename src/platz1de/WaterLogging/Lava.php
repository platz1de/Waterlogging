<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Lava as PMLava;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;

class Lava extends PMLava
{
	protected function checkForHarden(): bool
	{
		if ($this->falling) {
			return false;
		}
		$colliding = null;
		foreach (Facing::ALL as $side) {
			if ($side === Facing::DOWN) {
				continue;
			}
			$blockSide = $this->getSide($side);
			//Even blocked water logged faces can harden lava
			if ($blockSide instanceof Water || WaterLogging::isWaterLogged($blockSide)) {
				$colliding = $blockSide;
				break;
			}
		}

		if ($colliding !== null) {
			if ($this->decay === 0) {
				$this->liquidCollide($colliding, VanillaBlocks::OBSIDIAN());
				return true;
			}

			if ($this->decay <= 4) {
				$this->liquidCollide($colliding, VanillaBlocks::COBBLESTONE());
				return true;
			}
		}

		return false;
	}
}