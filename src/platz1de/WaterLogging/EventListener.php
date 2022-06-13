<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Bucket;
use pocketmine\item\LiquidBucket;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener
{
	/**
	 * @priority LOW
	 */
	public function onInteract(PlayerInteractEvent $event): void
	{
		if (!WaterLoggableBlocks::isWaterLoggable($event->getBlock())) {
			return;
		}
		$item = $event->getItem();
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if ($item instanceof LiquidBucket && $item->getLiquid() instanceof Water && !WaterLogging::isWaterLogged($block)) {
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
		} elseif ($item instanceof Bucket && WaterLogging::isWaterLogged($block)) {
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
	public function onBreak(BlockBreakEvent $event): void
	{
		if (WaterLogging::isWaterLogged($event->getBlock())) {
			WaterLogging::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function () use ($event): void {
				if ($event->getBlock()->getPosition()->getWorld()->getBlock($event->getBlock()->getPosition()) instanceof Air) {
					WaterLogging::removeWaterLogging($event->getBlock());
					$event->getBlock()->getPosition()->getWorld()->setBlock($event->getBlock()->getPosition(), VanillaBlocks::WATER());
				}
			}));
		}
	}
}