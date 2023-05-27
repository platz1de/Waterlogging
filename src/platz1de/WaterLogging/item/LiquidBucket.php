<?php

namespace platz1de\WaterLogging\item;

use platz1de\WaterLogging\WaterLoggableBlocks;
use platz1de\WaterLogging\WaterLogging;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\LiquidBucket as PMLiquidBucket;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class LiquidBucket extends PMLiquidBucket
{
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
	{
		if ($this->getLiquid()->getTypeId() === BlockTypeIds::WATER) {
			if (WaterLoggableBlocks::isWaterLoggable($blockClicked)) {
				return $this->handleWaterLogging($player, $blockClicked, $face, $returnedItems);
			}

			if (WaterLoggableBlocks::isWaterLoggable($blockReplace)) {
				return $this->handleWaterLogging($player, $blockReplace, $face, $returnedItems);
			}
		}

		return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector, $returnedItems);
	}

	/**
	 * @param Player $player
	 * @param Block  $block
	 * @param int    $face
	 * @param Item[] $returnedItems
	 * @return ItemUseResult
	 */
	private function handleWaterLogging(Player $player, Block $block, int $face, array &$returnedItems): ItemUseResult
	{
		if (WaterLogging::isSourceWaterLogged($block)) {
			return ItemUseResult::NONE();
		}

		$ev = new PlayerBucketEmptyEvent($player, $block, $face, $this, VanillaItems::BUCKET());
		$ev->call();
		if (!$ev->isCancelled()) {
			WaterLogging::addWaterLogging($block);
			$player->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), VanillaBlocks::WATER()->getBucketEmptySound());

			$this->pop();
			$returnedItems[] = $ev->getItem();
			return ItemUseResult::SUCCESS();
		}

		return ItemUseResult::FAIL();
	}
}