<?php

namespace platz1de\WaterLogging;

use platz1de\WaterLogging\item\Bucket;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\player\Player;

class EventListener implements Listener
{
	/**
	 * @var Player|null
	 */
	public static ?Player $sneakHack = null;

	/**
	 * @priority MONITOR
	 */
	public function onInteract(PlayerInteractEvent $event): void
	{
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
			return;
		}

		if (self::$sneakHack !== null) {
			//Small failsafe, ideally this should never happen
			$event->getPlayer()->setSneaking(false);
			self::$sneakHack = null;
		}

		//Stupid Hack:
		//Picking up water by clicking on a neighbor block sends an interaction with the waterlogged block itself
		//This is a problem, because there is no supported way to distinguish between actually interacting with the block and picking up water
		$item = $event->getItem();
		$block = $event->getBlock(); //This has to be an overwritten bucket
		if ($item instanceof Bucket && WaterLogging::isSourceWaterLogged($block) && !$event->getPlayer()->isSneaking()) {
			//get packet data of this click
			/** @var UseItemTransactionData $data */
			$data = (function () {
				/** @noinspection all */
				return $this->lastRightClickData;
			})->call($event->getPlayer()->getNetworkSession()->getHandler());
			$target = TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->generateDataFromStateId($data->getBlockRuntimeId());
			if ($target?->getName() === BlockTypeNames::WATER || $target?->getName() === BlockTypeNames::FLOWING_WATER) {
				self::$sneakHack = $event->getPlayer();
				$event->getPlayer()->setSneaking();
				//WARNING: If any plugin cancels the event after (which isn't supposed to be done in monitor priority), the player will be stuck in sneaking mode
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