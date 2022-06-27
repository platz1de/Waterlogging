<?php

namespace platz1de\WaterLogging;

use pocketmine\block\BlockLegacyMetadata;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\World;

class LayerUpdateTask extends Task
{
	/**
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function onRun(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			/** @var ReversePriorityQueue<int, Vector3> $updateQueue */
			$updateQueue = (function (): ReversePriorityQueue {
				return $this->scheduledBlockUpdateQueue;
			})->call($world);
			$updateQueue = clone $updateQueue;

			while ($updateQueue->count() > 0 && $updateQueue->current()["priority"] <= Server::getInstance()->getTick()) {
				$pos = $updateQueue->extract()["data"];
				$this->check($world, $pos);
			}
		}
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @return void
	 */
	private function check(World $world, Vector3 $pos): void
	{
		if (WaterLogging::isWaterLoggedAt($world, $pos)) {
			$data = WaterLogging::getWaterDataAt($world, $pos);
			$block = WaterLogging::WATER()->setDecay($data & 0x07);
			$block->setFalling(($data & BlockLegacyMetadata::LIQUID_FLAG_FALLING) !== 0);
			$block->position($world, $pos->getX(), $pos->getY(), $pos->getZ());
			$block->onScheduledUpdate(true);
		}
	}
}