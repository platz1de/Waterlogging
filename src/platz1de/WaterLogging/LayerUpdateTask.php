<?php

namespace platz1de\WaterLogging;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\ReversePriorityQueue;
use pocketmine\world\World;
use SplPriorityQueue;
use SplQueue;

class LayerUpdateTask extends Task
{
	/**
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function onRun(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			/** @var array{ReversePriorityQueue<int, Vector3>, SplQueue<int>} $updates */
			$updateData = (function (): array {
				return [$this->scheduledBlockUpdateQueue, $this->neighbourBlockUpdateQueue];
			})->call($world);
			/** @var ReversePriorityQueue<int, Vector3> $scheduledBlockUpdateQueue */
			$updates = clone $updateData[0];
			/** @var SplQueue<int> $neighbourBlockUpdateQueue */
			$add = clone $updateData[1];
			$updates->setExtractFlags(SplPriorityQueue::EXTR_DATA);

			$done = [];
			while ($updates->valid()) {
				$pos = $updates->extract();
				$done[World::blockHash($pos->getX(), $pos->getY(), $pos->getZ())] = true;
				$this->check($world, $pos);
			}

			while ($add->count() > 0) {
				$hash = $add->dequeue();
				if (!isset($done[$hash])) {
					World::getBlockXYZ($hash, $x, $y, $z);
					$this->check($world, new Vector3($x, $y, $z));
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
			$block = VanillaBlocks::WATER()->setDecay(WaterLogging::getWaterDecayAt($world, $pos));
			$block->position($world, $pos->getX(), $pos->getY(), $pos->getZ());
			$block->onScheduledUpdate();
		}
	}
}