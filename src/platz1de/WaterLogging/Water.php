<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\Water as PMWater;
use pocketmine\event\block\BlockSpreadEvent;
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
			$adjacent = 0;
			$decay = -1;
			$this->getSmallestDecay($this->position->add(0, 0, -1), $decay, $adjacent);
			$this->getSmallestDecay($this->position->add(0, 0, 1), $decay, $adjacent);
			$this->getSmallestDecay($this->position->add(-1, 0, 0), $decay, $adjacent);
			$this->getSmallestDecay($this->position->add(1, 0, 0), $decay, $adjacent);
			$decay += $this->getFlowDecayPerBlock();
			if ($decay > 0 && $decay < self::MAX_DECAY) {
				$falling = false;

				if ($this->getEffectiveFlowDecay($this->position->getWorld()->getBlock($this->position->add(0, 1, 0))) >= 0) {
					$falling = true;
					$decay = 0;
				}

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
		if ($this->canFlowInto($block) && WaterLoggableBlocks::isFlowingWaterLoggable($block)) {
			$new = clone $this;
			$new->falling = $falling;
			$new->decay = $falling ? 0 : $newFlowDecay;

			$ev = new BlockSpreadEvent($block, $this, $new);
			$ev->call();
			if (!$ev->isCancelled()) {
				WaterLogging::addWaterLogging($block, $newFlowDecay);
			}
			return;
		}
		parent::flowIntoBlock($block, $newFlowDecay, $falling);
	}

	public function isSource(): bool
	{
		return $this->sourceHack || parent::isSource();
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $decay
	 * @param int     $sources
	 */
	private function getSmallestDecay(Vector3 $pos, int &$decay, int &$sources): void
	{
		$block = $this->position->getWorld()->getBlockAt($pos->x, $pos->y, $pos->z);
		if ($block instanceof Liquid && $block->isSameType($this)) {
			$blockDecay = $block->decay;

			if ($block->isSource()) {
				++$sources;
			} elseif ($block->falling) {
				$blockDecay = 0;
			}
		} elseif (WaterLogging::isSourceWaterLoggedAt($this->position->getWorld(), $pos)) {
			$blockDecay = 0;
			++$sources;
		} else {
			$blockDecay = WaterLogging::getWaterDecayAt($this->position->getWorld(), $pos);
		}

		if ($blockDecay !== false && ($blockDecay < $decay || $decay < 0)) {
			$decay = $blockDecay;
		}
	}

	protected function getEffectiveFlowDecay(Block $block): int
	{
		if (WaterLogging::isWaterLogged($block)) {
			return WaterLogging::getWaterDecayAt($block->getPosition()->getWorld(), $block->getPosition());
		}
		return parent::getEffectiveFlowDecay($block);
	}
}