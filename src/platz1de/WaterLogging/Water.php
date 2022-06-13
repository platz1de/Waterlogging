<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\Water as PMWater;
use pocketmine\math\Vector3;

class Water extends PMWater
{
	/**
	 * This is a really dumb hack to avoid having to rewrite all the liquid spreading logic
	 * Needed as getSmallestFlowDecay() is private...
	 */
	private bool $sourceHack = false;

	public function onScheduledUpdate(): void
	{
		if ($this->decay > 0) {
			$adjacent = WaterLogging::isWaterLoggedAt($this->position->getWorld(), $this->position->add(0, 0, -1)) +
				WaterLogging::isWaterLoggedAt($this->position->getWorld(), $this->position->add(0, 0, 1)) +
				WaterLogging::isWaterLoggedAt($this->position->getWorld(), $this->position->add(-1, 0, 0)) +
				WaterLogging::isWaterLoggedAt($this->position->getWorld(), $this->position->add(1, 0, 0));
			if ($adjacent > 0) {
				$decay = 1;
				$falling = false;
				if (WaterLogging::isWaterLoggedAt($this->position->getWorld(), $this->position->add(0, 1, 0))) {
					$falling = true;
					$decay = 0;
				}
				$adjacent += $this->isWaterSourceAt($this->position->add(0, 0, -1)) +
					$this->isWaterSourceAt($this->position->add(0, 0, 1)) +
					$this->isWaterSourceAt($this->position->add(-1, 0, 0)) +
					$this->isWaterSourceAt($this->position->add(1, 0, 0));
				$minAdjacentSources = $this->getMinAdjacentSourcesToFormSource();
				if ($minAdjacentSources !== null && $adjacent >= $minAdjacentSources) {
					$bottomBlock = $this->position->getWorld()->getBlockAt($this->position->x, $this->position->y - 1, $this->position->z);
					if ($bottomBlock->isSolid() || ($bottomBlock instanceof Liquid && $bottomBlock->isSameType($this) && $bottomBlock->isSource())) {
						$decay = 0;
						$falling = false;
					}
				}
				if ($decay !== $this->decay || $falling !== $this->falling) {
					$this->decay = $decay;
					$this->falling = $falling;
					$this->position->getWorld()->setBlock($this->position, $this);
				}
				$this->sourceHack = true;
			}
		}
		parent::onScheduledUpdate();
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay, bool $falling): void
	{
		$this->sourceHack = false;
		parent::flowIntoBlock($block, $newFlowDecay, $falling);
	}

	public function isSource(): bool
	{
		return $this->sourceHack || parent::isSource();
	}

	private function isWaterSourceAt(Vector3 $pos): bool
	{
		$block = $this->position->getWorld()->getBlockAt($pos->x, $pos->y, $pos->z);
		return $block instanceof Liquid && $block->isSameType($this) && $block->isSource();
	}

	protected function getEffectiveFlowDecay(Block $block): int
	{
		if (WaterLogging::isWaterLogged($block)) {
			return 0;
		}
		return parent::getEffectiveFlowDecay($block);
	}
}