<?php

namespace platz1de\WaterLogging;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\World;
use SplQueue;

class LayerUpdateTask extends Task
{
	/** @var array<string, SplQueue<Vector3>> */
	private array $cache = [];

	/**
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function onRun(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			if (!isset($this->cache[$world->getFolderName()])) {
				$this->cache[$world->getFolderName()] = new SplQueue();
			}
			$cache = $this->cache[$world->getFolderName()];
			while ($cache->count() > 0) {
				$world->scheduleDelayedBlockUpdate($cache->dequeue(), VanillaBlocks::WATER()->tickRate());
			}
			/** @var ReversePriorityQueue<int, Vector3> $updateQueue */
			$updateQueue = (function (): ReversePriorityQueue {
				return $this->scheduledBlockUpdateQueue;
			})->call($world);
			$updateQueue = clone $updateQueue;

			while ($updateQueue->count() > 0 && $updateQueue->current()["priority"] <= Server::getInstance()->getTick()) {
				$pos = $updateQueue->extract()["data"];
				$this->check($world, $pos);
			}

			/** @var SplQueue<int> $updates */
			$updateData = (function (): SplQueue {
				return $this->neighbourBlockUpdateQueue;
			})->call($world);
			$updateData = clone $updateData;
			while ($updateData->count() > 0) {
				$hash = $updateData->dequeue();
				World::getBlockXYZ($hash, $x, $y, $z);
				if (WaterLogging::isWaterLoggedAt($world, $v = new Vector3($x, $y, $z))) {
					$cache->enqueue($v);
				}
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
			$block = WaterLogging::WATER()->setDecay(WaterLogging::getWaterDecayAt($world, $pos));
			$block->position($world, $pos->getX(), $pos->getY(), $pos->getZ());
			$block->onScheduledUpdate();
		}
	}
}