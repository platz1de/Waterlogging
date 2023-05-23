<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkListener;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class BlockListener implements ChunkListener
{
	private World $world;
	/**
	 * @var BlockListener[]
	 */
	public static array $listeners = [];

	/**
	 * @param World $world
	 */
	public function __construct(World $world)
	{
		$this->world = $world;
	}

	/**
	 * @param World $world
	 * @return BlockListener
	 */
	public static function getForWorld(World $world): BlockListener
	{
		if (!isset(self::$listeners[$world->getFolderName()])) {
			self::$listeners[$world->getFolderName()] = new self($world);
		}
		return self::$listeners[$world->getFolderName()];
	}

	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk): void
	{
		// Noop
	}

	public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk): void
	{
		// Noop
	}

	public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk): void
	{
		$this->world->unregisterChunkListener($this, $chunkX, $chunkZ);
	}

	public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk): void
	{
		// Noop
	}

	public function onBlockChanged(Vector3 $block): void
	{
		if (WaterLogging::isWaterLoggedAt($this->world, $block, false) && (
				!WaterLoggableBlocks::isWaterLoggable($b = $this->world->getBlock($block)) ||
				(WaterLogging::getWaterDataAt($this->world, $block, false) !== 0 && !WaterLoggableBlocks::isFlowingWaterLoggable($b = $this->world->getBlock($block)))
			)) {
			if ($b instanceof Air) {
				$data = WaterLogging::getWaterDataAt($this->world, $block, false);
				$this->world->setBlock($block, VanillaBlocks::WATER()->setDecay($data & 0x07)->setFalling(($data & 0x08) !== 0));
			}
			WaterLogging::removeWaterLogging($b);
		}
	}
}