<?php

namespace platz1de\WaterLogging\item;

use platz1de\WaterLogging\EventListener;
use platz1de\WaterLogging\WaterLogging;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\item\Bucket as PMBucket;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Bucket extends PMBucket
{
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
	{
		if (EventListener::$sneakHack !== null) {
			EventListener::$sneakHack->setSneaking(false); //This should be the same player
			EventListener::$sneakHack = null;
		}

		if (WaterLogging::isSourceWaterLogged($blockClicked)) {
			return $this->handleWaterRemoval($player, $blockClicked, $face, $returnedItems);
		}

		return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector, $returnedItems);
	}

	/**
	 * @param Player $player
	 * @param Block  $block
	 * @param int    $face
	 * @param array  $returnedItems
	 * @return ItemUseResult
	 */
	private function handleWaterRemoval(Player $player, Block $block, int $face, array &$returnedItems): ItemUseResult
	{
		$stack = clone $this;
		$stack->pop();

		$ev = new PlayerBucketFillEvent($player, $block, $face, $this, VanillaItems::WATER_BUCKET());
		$ev->call();
		if (!$ev->isCancelled()) {
			WaterLogging::removeWaterLogging($block);
			$player->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), VanillaBlocks::WATER()->getBucketFillSound());

			$this->pop();
			$returnedItems[] = $ev->getItem();
			return ItemUseResult::SUCCESS();
		}

		return ItemUseResult::FAIL();
	}
}