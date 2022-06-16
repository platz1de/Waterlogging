<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Bamboo;
use pocketmine\block\BaseRail;
use pocketmine\block\Beacon;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Button;
use pocketmine\block\Carpet;
use pocketmine\block\Cobweb;
use pocketmine\block\CocoaBlock;
use pocketmine\block\DeadBush;
use pocketmine\block\DragonEgg;
use pocketmine\block\EndRod;
use pocketmine\block\Flowable;
use pocketmine\block\FlowerPot;
use pocketmine\block\ItemFrame;
use pocketmine\block\Leaves;
use pocketmine\block\Lever;
use pocketmine\block\Liquid;
use pocketmine\block\MonsterSpawner;
use pocketmine\block\NetherPortal;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\ShulkerBox;
use pocketmine\block\Skull;
use pocketmine\block\SoulSand;
use pocketmine\block\Tripwire;
use pocketmine\block\TripwireHook;
use pocketmine\block\Vine;

//Fun with instance checks ...
class WaterLoggableBlocks
{
	/**
	 * @param Block $block
	 * @return bool Whether the block is waterloggable
	 */
	public static function isWaterLoggable(Block $block): bool
	{
		//Note: In bedrock Edition a ton of blocks are waterloggable
		return (!$block->isFullCube() && !$block instanceof Flowable && !(
					//excluded blocks
					$block instanceof Bamboo || //Bamboo just needs to be different to make it more difficult
					$block instanceof CocoaBlock || //cocoa isn't marked as flowable in pmmp
					$block instanceof SoulSand || //just a bit too short of a full block, but handled as one
					$block instanceof Liquid || //obvious reasons
					$block instanceof NetherPortal //yea...
				)) || (self::isFlowingWaterLoggable($block) || ( //phpstorm please leave the formatting as is
					//included blocks
					$block instanceof Leaves ||
					$block instanceof Button ||
					$block instanceof Carpet ||
					$block instanceof Cobweb ||
					$block instanceof DeadBush ||
					$block instanceof Vine ||
					$block instanceof FlowerPot ||
					$block instanceof ItemFrame ||
					$block instanceof DragonEgg ||
					$block instanceof Skull ||
					$block instanceof BaseRail ||
					$block instanceof Beacon ||
					$block instanceof MonsterSpawner ||
					$block instanceof ShulkerBox ||
					$block->getId() === BlockLegacyIds::BARRIER //barriers do not have an own class :c
				)
			);

	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterloggable by flowing water (mojang what even is this; bedrock exclusive)
	 */
	public static function isFlowingWaterLoggable(Block $block): bool
	{
		//Note: In bedrock Edition a few blocks are even water loggable by flowing water
		return (
			$block instanceof EndRod ||
			$block instanceof Lever ||
			$block instanceof RedstoneComparator ||
			$block instanceof RedstoneRepeater ||
			$block instanceof Tripwire ||
			$block instanceof TripwireHook
		);
	}
}