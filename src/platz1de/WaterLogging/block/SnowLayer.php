<?php

namespace platz1de\WaterLogging\block;

use platz1de\WaterLogging\WaterLoggableBlocks;
use platz1de\WaterLogging\WaterLogging;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\SnowLayer as PMSnowLayer;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class SnowLayer extends PMSnowLayer
{
	//Cache for previous block (this breaks if an interaction has multiple blocks)
	private static ?int $plant = null;

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock): bool
	{
		return (!$isClickedBlock && WaterLoggableBlocks::isSnowLoggable($blockReplace)) || parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
	{
		if (WaterLoggableBlocks::isSnowLoggable($blockReplace)) {
			self::$plant = $blockReplace->getStateId();
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onPostPlace(): void
	{
		if (self::$plant !== null) {
			WaterLogging::addSnowLogging($this, self::$plant);
			self::$plant = null;
		}
		parent::onPostPlace();
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
	{
		$plant = WaterLogging::getSnowPlant($this);
		parent::onBreak($item, $player, $returnedItems);
		if ($plant === false) {
			return true;
		}
		WaterLogging::clearBlockLayerId($this);
		$this->getPosition()->getWorld()->setBlock($this->getPosition(), RuntimeBlockStateRegistry::getInstance()->fromStateId($plant));
		return true;
	}
}