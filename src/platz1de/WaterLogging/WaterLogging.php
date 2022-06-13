<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifierFlattened;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\World;
use UnexpectedValueException;

class WaterLogging extends PluginBase
{
	use SingletonTrait;

	public const WATERLOGGING_LAYER = 1;

	public function onLoad(): void
	{
		self::setInstance($this);
		//TODO: setBlock Support using chunk listener
		BlockFactory::getInstance()->register(new Water(new BlockIdentifierFlattened(BlockLegacyIds::FLOWING_WATER, [BlockLegacyIds::STILL_WATER], 0), "Water", BreakInfo::indestructible(500.0)), true);
	}

	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new LayerUpdateTask(), 1);
	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterlogged
	 */
	public static function isWaterLogged(Block $block): bool
	{
		return self::isWaterLoggedAt($block->getPosition()->getWorld(), $block->getPosition());
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @return bool Whether the block is waterlogged
	 */
	public static function isWaterLoggedAt(World $world, Vector3 $pos): bool
	{
		if ($world->getBlockAt($pos->getX(), $pos->getY(), $pos->getZ()) instanceof Air) {
			return false; //technically, this is still waterlogged, but shouldn't be rendered as such (created by direct chunk manipulation)
		}
		$layer = self::getBlockLayer($world, $pos);
		return $layer->get($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f) >> Block::INTERNAL_METADATA_BITS === BlockLegacyIds::FLOWING_WATER;
	}

	/**
	 * @param Block $block
	 */
	public static function addWaterLogging(Block $block): void
	{
		self::setBlockLayerId($block, BlockLegacyIds::FLOWING_WATER << Block::INTERNAL_METADATA_BITS);
		$block->getPosition()->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), VanillaBlocks::WATER()->tickRate());
		$block->getPosition()->getWorld()->notifyNeighbourBlockUpdate($block->getPosition());
	}

	/**
	 * @param Block $block
	 */
	public static function removeWaterLogging(Block $block): void
	{
		$pos = $block->getPosition();
		$subChunk = $pos->getWorld()->getChunk($pos->getX() >> 4, $pos->getZ() >> 4)?->getSubChunk($pos->getY() >> 4);
		self::setBlockLayerId($block, $subChunk->getEmptyBlockId());
		$block->getPosition()->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), VanillaBlocks::WATER()->tickRate());
		$block->getPosition()->getWorld()->notifyNeighbourBlockUpdate($block->getPosition());
	}

	/**
	 * @param Block $block
	 * @param int   $id
	 * @return void
	 */
	private static function setBlockLayerId(Block $block, int $id): void
	{
		$pos = $block->getPosition();
		$layer = self::getBlockLayer($pos->getWorld(), $pos);
		$layer->set($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f, $id);
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @return PalettedBlockArray
	 * @noinspection PhpUndefinedFieldInspection
	 */
	private static function getBlockLayer(World $world, Vector3 $pos): PalettedBlockArray
	{
		$subChunk = $world->getChunk($pos->getX() >> 4, $pos->getZ() >> 4)?->getSubChunk($pos->getY() >> 4);
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