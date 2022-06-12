<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\plugin\PluginBase;
use pocketmine\world\format\PalettedBlockArray;
use UnexpectedValueException;

class WaterLogging extends PluginBase
{
	public const WATERLOGGING_LAYER = 1;

	protected function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterlogged
	 */
	public static function isWaterLogged(Block $block): bool
	{
		$pos = $block->getPosition();
		$layer = self::getBlockLayer($block);
		return $layer->get($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f) >> Block::INTERNAL_METADATA_BITS === BlockLegacyIds::FLOWING_WATER;
	}

	/**
	 * @param Block $block
	 */
	public static function addWaterLogging(Block $block): void
	{
		self::setBlockLayerId($block, BlockLegacyIds::FLOWING_WATER << Block::INTERNAL_METADATA_BITS);
	}

	/**
	 * @param Block $block
	 */
	public static function removeWaterLogging(Block $block): void
	{
		$pos = $block->getPosition();
		$subChunk = $pos->getWorld()->getChunk($pos->getX() >> 4, $pos->getZ() >> 4)?->getSubChunk($pos->getY() >> 4);
		self::setBlockLayerId($block, $subChunk->getEmptyBlockId());
	}

	/**
	 * @param Block    $block
	 * @param int $id
	 * @return void
	 */
	private static function setBlockLayerId(Block $block, int $id): void
	{
		$pos = $block->getPosition();
		$layer = self::getBlockLayer($block);
		$layer->set($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f, $id);
	}

	/**
	 * @param Block $block
	 * @return PalettedBlockArray
	 * @noinspection PhpUndefinedFieldInspection
	 */
	private static function getBlockLayer(Block $block): PalettedBlockArray
	{
		$pos = $block->getPosition();
		$subChunk = $pos->getWorld()->getChunk($pos->getX() >> 4, $pos->getZ() >> 4)?->getSubChunk($pos->getY() >> 4);
		if ($subChunk === null || !isset($subChunk->getBlockLayers()[0])) {
			throw new UnexpectedValueException("Block is not in an existing subchunk");
		}
		if (!isset($subChunk->getBlockLayers()[self::WATERLOGGING_LAYER])) {
			(function () {
				$this->blockLayers[WaterLogging::WATERLOGGING_LAYER] = new PalettedBlockArray($this->emptyBlockId);
			})->call($subChunk);
		}
		return $subChunk->getBlockLayers()[self::WATERLOGGING_LAYER];
	}
}