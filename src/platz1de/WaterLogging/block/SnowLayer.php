<?php

namespace platz1de\WaterLogging\block;

use platz1de\WaterLogging\WaterLoggableBlocks;
use platz1de\WaterLogging\WaterLogging;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\SnowLayer as PMSnowLayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\Sound;

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
		} else {
			self::$plant = null;
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
		WaterLogging::clearBlockLayerId($this);
		parent::onBreak($item, $player, $returnedItems);
		if ($plant === false) {
			return true;
		}
		$this->getPosition()->getWorld()->setBlock($this->getPosition(), RuntimeBlockStateRegistry::getInstance()->fromStateId($plant));
		return true;
	}

	public function tickFalling(): ?Block
	{
		//Hack as not all snowloggable blocks can be replaced
		$block = $this->getPosition()->getWorld()->getBlock($this->getPosition());
		if (WaterLoggableBlocks::isSnowLoggable($block)) {
			self::$plant = $block->getStateId();
			$this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::AIR());
			return $this; //force block to be replaced
		}

		self::$plant = null;
		return null;
	}

	public function getLandSound(): ?Sound
	{
		//Another hack to recover the plant block
		if (self::$plant !== null) {
			WaterLogging::addSnowLogging($this, self::$plant);
			self::$plant = null;
		}
		return parent::getLandSound();
	}
}