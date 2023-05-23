<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\item\Bucket;
use pocketmine\item\LiquidBucket;
use pocketmine\item\VanillaItems;

class EventListener implements Listener
{
	/**
	 * @priority LOW
	 */
	public function onInteract(PlayerInteractEvent $event): void
	{
		if (WaterLoggableBlocks::isWaterLoggable($event->getBlock())) {
			$block = $event->getBlock();
		} elseif (WaterLoggableBlocks::isWaterLoggable($event->getBlock()->getSide($event->getFace()))) {
			$block = $event->getBlock()->getSide($event->getFace());
		} else {
			return;
		}
		$item = $event->getItem();
		$player = $event->getPlayer();
		if ($item instanceof LiquidBucket && $item->getLiquid() instanceof Water && !WaterLogging::isSourceWaterLogged($block)) {
			$ev = new PlayerBucketEmptyEvent($player, $block, $event->getFace(), $item, VanillaItems::BUCKET());
			$ev->call();
			if (!$ev->isCancelled()) {
				$event->cancel();
				WaterLogging::addWaterLogging($block);
				$player->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), VanillaBlocks::WATER()->getBucketEmptySound());

				if ($player->hasFiniteResources()) {
					$player->getInventory()->setItemInHand($ev->getItem());
				}
			}
		} elseif ($item instanceof Bucket && WaterLogging::isSourceWaterLogged($block)) {
			$ev = new PlayerBucketFillEvent($player, $block, $event->getFace(), $item, VanillaItems::WATER_BUCKET());
			$ev->call();
			if (!$ev->isCancelled()) {
				$event->cancel();
				WaterLogging::removeWaterLogging($block);
				$player->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), VanillaBlocks::WATER()->getBucketFillSound());

				$item = clone $item;
				$item->pop();
				if ($player->hasFiniteResources()) {
					if ($item->getCount() === 0) {
						$player->getInventory()->setItemInHand($ev->getItem());
					} else {
						$player->getInventory()->setItemInHand($item);
						$player->getInventory()->addItem($ev->getItem());
					}
				} else {
					$player->getInventory()->addItem($ev->getItem());
				}
			}
		}
	}

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