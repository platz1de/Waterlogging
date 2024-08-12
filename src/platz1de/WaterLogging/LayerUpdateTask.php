<?php

namespace platz1de\WaterLogging;

use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\World;
use SplPriorityQueue;

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
			$updateQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

			/** @phpstan-ignore-next-line */
			while ($updateQueue->count() > 0 && $updateQueue->current()["priority"] <= Server::getInstance()->getTick()) {
				/** @phpstan-ignore-next-line */
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
			$block = WaterLogging::WATER()->setDecay($data->getDecay());
			$block->setFalling($data->isFalling());
			$block->position($world, $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
			$block->onScheduledUpdate(true);
		}
	}
}