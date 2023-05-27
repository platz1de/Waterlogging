<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkLoadEvent;

class EventListener implements Listener
{
	/**
	 * @priority MONITOR
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		foreach ($event->getTransaction()->getBlocks() as $b) {
			/** @var Block $new */
			$new = $b[3];
			$old = $new->getPosition()->getWorld()->getBlockAt($b[0], $b[1], $b[2]);
			if ($old instanceof Water && (
					($old->isSource() && WaterLoggableBlocks::isWaterLoggable($new)) ||
					(!$old->isSource() && WaterLoggableBlocks::isFlowingWaterLoggable($new))
				)) {
				WaterLogging::addWaterLogging($new, $old->getDecay(), $old->isFalling());
			}
		}
	}

	public function onLoad(ChunkLoadEvent $event): void
	{
		$event->getWorld()->registerChunkListener(BlockListener::getForWorld($event->getWorld()), $event->getChunkX(), $event->getChunkZ());
	}

	/**
	 * @priority MONITOR
	 */
	public function onUpdate(BlockUpdateEvent $event): void
	{
		if (WaterLogging::isWaterLogged($event->getBlock())) {
			$event->getBlock()->getPosition()->getWorld()->scheduleDelayedBlockUpdate($event->getBlock()->getPosition(), VanillaBlocks::WATER()->tickRate());
		}
	}
}