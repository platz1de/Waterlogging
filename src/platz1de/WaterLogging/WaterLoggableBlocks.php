<?php

namespace platz1de\WaterLogging;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SupportType;

//Fun with type ids...
class WaterLoggableBlocks
{
	/**
	 * @var int[]
	 */
	private static array $waterLoggable = [
		BlockTypeIds::ACTIVATOR_RAIL,
		BlockTypeIds::AMETHYST_CLUSTER,
		BlockTypeIds::ANVIL,
		//Azalea
		BlockTypeIds::BANNER,
		BlockTypeIds::WALL_BANNER,
		BlockTypeIds::BARRIER,
		BlockTypeIds::BEACON,
		BlockTypeIds::BED,
		BlockTypeIds::BELL,
		//Border
		BlockTypeIds::BREWING_STAND,
		//Bubble Column

		BlockTypeIds::STONE_BUTTON,
		BlockTypeIds::OAK_BUTTON,
		BlockTypeIds::SPRUCE_BUTTON,
		BlockTypeIds::BIRCH_BUTTON,
		BlockTypeIds::JUNGLE_BUTTON,
		BlockTypeIds::ACACIA_BUTTON,
		BlockTypeIds::DARK_OAK_BUTTON,
		BlockTypeIds::MANGROVE_BUTTON,
		//Bamboo Button
		BlockTypeIds::CHERRY_BUTTON,
		BlockTypeIds::CRIMSON_BUTTON,
		BlockTypeIds::WARPED_BUTTON,
		BlockTypeIds::POLISHED_BLACKSTONE_BUTTON,

		BlockTypeIds::CACTUS,
		BlockTypeIds::CAKE,
		BlockTypeIds::CAKE_WITH_CANDLE,
		BlockTypeIds::CAKE_WITH_DYED_CANDLE,
		//Campfire / Soul Campfire
		BlockTypeIds::CANDLE,
		BlockTypeIds::DYED_CANDLE,
		BlockTypeIds::CARPET,
		//Calibrated Sculk Sensor
		BlockTypeIds::CAULDRON,

		BlockTypeIds::WATER_CAULDRON,
		BlockTypeIds::LAVA_CAULDRON,
		BlockTypeIds::POTION_CAULDRON,
		BlockTypeIds::POWDER_SNOW_CAULDRON,

		BlockTypeIds::CHAIN,
		BlockTypeIds::CHEST,
		BlockTypeIds::TRAPPED_CHEST,
		BlockTypeIds::COBWEB,
		//Composter
		//Conduit
		//Copper Grate
		BlockTypeIds::CORAL,
		BlockTypeIds::CORAL_FAN,
		BlockTypeIds::WALL_CORAL_FAN,
		BlockTypeIds::DAYLIGHT_SENSOR,
		BlockTypeIds::DEAD_BUSH,
		//Dead Coral Fan
		//Decorated Pot
		BlockTypeIds::DETECTOR_RAIL,

		BlockTypeIds::IRON_DOOR,
		BlockTypeIds::OAK_DOOR,
		BlockTypeIds::SPRUCE_DOOR,
		BlockTypeIds::BIRCH_DOOR,
		BlockTypeIds::JUNGLE_DOOR,
		BlockTypeIds::ACACIA_DOOR,
		BlockTypeIds::DARK_OAK_DOOR,
		BlockTypeIds::MANGROVE_DOOR,
		//Bamboo Door
		BlockTypeIds::CHERRY_DOOR,
		BlockTypeIds::CRIMSON_DOOR,
		BlockTypeIds::WARPED_DOOR,

		BlockTypeIds::DRAGON_EGG,
		BlockTypeIds::ENCHANTING_TABLE,
		BlockTypeIds::END_PORTAL_FRAME,
		BlockTypeIds::ENDER_CHEST,

		BlockTypeIds::OAK_FENCE,
		BlockTypeIds::SPRUCE_FENCE,
		BlockTypeIds::BIRCH_FENCE,
		BlockTypeIds::JUNGLE_FENCE,
		BlockTypeIds::ACACIA_FENCE,
		BlockTypeIds::DARK_OAK_FENCE,
		BlockTypeIds::MANGROVE_FENCE,
		//Bamboo Fence
		BlockTypeIds::CHERRY_FENCE,
		BlockTypeIds::CRIMSON_FENCE,
		BlockTypeIds::WARPED_FENCE,
		BlockTypeIds::NETHER_BRICK_FENCE,

		BlockTypeIds::OAK_FENCE_GATE,
		BlockTypeIds::SPRUCE_FENCE_GATE,
		BlockTypeIds::BIRCH_FENCE_GATE,
		BlockTypeIds::JUNGLE_FENCE_GATE,
		BlockTypeIds::ACACIA_FENCE_GATE,
		BlockTypeIds::DARK_OAK_FENCE_GATE,
		BlockTypeIds::MANGROVE_FENCE_GATE,
		//Bamboo Fence Gate
		BlockTypeIds::CHERRY_FENCE_GATE,
		BlockTypeIds::CRIMSON_FENCE_GATE,
		BlockTypeIds::WARPED_FENCE_GATE,

		BlockTypeIds::FLOWER_POT,
		//Flowering Azalea
		BlockTypeIds::GLASS_PANE,
		BlockTypeIds::STAINED_GLASS_PANE,
		BlockTypeIds::GLOW_LICHEN,
		BlockTypeIds::GLOWING_ITEM_FRAME,
		//Grindstone
		BlockTypeIds::HARDENED_GLASS_PANE,
		BlockTypeIds::STAINED_HARDENED_GLASS_PANE,
		BlockTypeIds::HOPPER,
		BlockTypeIds::IRON_BARS,
		BlockTypeIds::ITEM_FRAME,
		//Kelp
		//Kelp Plant
		BlockTypeIds::LADDER,
		BlockTypeIds::LANTERN,
		BlockTypeIds::SOUL_LANTERN,

		BlockTypeIds::OAK_LEAVES,
		BlockTypeIds::SPRUCE_LEAVES,
		BlockTypeIds::BIRCH_LEAVES,
		BlockTypeIds::JUNGLE_LEAVES,
		BlockTypeIds::ACACIA_LEAVES,
		BlockTypeIds::DARK_OAK_LEAVES,
		BlockTypeIds::MANGROVE_LEAVES,
		BlockTypeIds::CHERRY_LEAVES,
		BlockTypeIds::AZALEA_LEAVES,
		BlockTypeIds::FLOWERING_AZALEA_LEAVES,

		BlockTypeIds::LECTERN,
		BlockTypeIds::LIGHTNING_ROD,
		BlockTypeIds::MANGROVE_ROOTS,
		BlockTypeIds::MOB_HEAD,
		//Piston / Piston Head
		//Poster
		//Pointed Dripstone
		BlockTypeIds::POWERED_RAIL,

		BlockTypeIds::WEIGHTED_PRESSURE_PLATE_LIGHT,
		BlockTypeIds::WEIGHTED_PRESSURE_PLATE_HEAVY,
		BlockTypeIds::STONE_PRESSURE_PLATE,
		BlockTypeIds::POLISHED_BLACKSTONE_PRESSURE_PLATE,
		BlockTypeIds::OAK_PRESSURE_PLATE,
		BlockTypeIds::SPRUCE_PRESSURE_PLATE,
		BlockTypeIds::BIRCH_PRESSURE_PLATE,
		BlockTypeIds::JUNGLE_PRESSURE_PLATE,
		BlockTypeIds::ACACIA_PRESSURE_PLATE,
		BlockTypeIds::DARK_OAK_PRESSURE_PLATE,
		BlockTypeIds::MANGROVE_PRESSURE_PLATE,
		//Bamboo Pressure Plate
		BlockTypeIds::CHERRY_PRESSURE_PLATE,
		BlockTypeIds::CRIMSON_PRESSURE_PLATE,
		BlockTypeIds::WARPED_PRESSURE_PLATE,

		BlockTypeIds::RAIL,
		//Scaffolding
		//Skulk Sensor
		//Skulk Shrieker
		//Skulk Vein
		BlockTypeIds::SEA_PICKLE,
		BlockTypeIds::SHULKER_BOX,
		BlockTypeIds::DYED_SHULKER_BOX,

		BlockTypeIds::OAK_SIGN,
		BlockTypeIds::OAK_WALL_SIGN,
		BlockTypeIds::SPRUCE_SIGN,
		BlockTypeIds::SPRUCE_WALL_SIGN,
		BlockTypeIds::BIRCH_SIGN,
		BlockTypeIds::BIRCH_WALL_SIGN,
		BlockTypeIds::JUNGLE_SIGN,
		BlockTypeIds::JUNGLE_WALL_SIGN,
		BlockTypeIds::ACACIA_SIGN,
		BlockTypeIds::ACACIA_WALL_SIGN,
		BlockTypeIds::DARK_OAK_SIGN,
		BlockTypeIds::DARK_OAK_WALL_SIGN,
		BlockTypeIds::MANGROVE_SIGN,
		BlockTypeIds::MANGROVE_WALL_SIGN,
		//Bamboo Sign
		BlockTypeIds::CHERRY_SIGN,
		BlockTypeIds::CRIMSON_SIGN,
		BlockTypeIds::CRIMSON_WALL_SIGN,
		BlockTypeIds::WARPED_SIGN,
		BlockTypeIds::WARPED_WALL_SIGN,

		BlockTypeIds::OAK_SLAB,
		BlockTypeIds::SPRUCE_SLAB,
		BlockTypeIds::BIRCH_SLAB,
		BlockTypeIds::JUNGLE_SLAB,
		BlockTypeIds::ACACIA_SLAB,
		BlockTypeIds::DARK_OAK_SLAB,
		BlockTypeIds::MANGROVE_SLAB,
		//Bamboo Slab
		BlockTypeIds::CHERRY_SLAB,
		BlockTypeIds::CRIMSON_SLAB,
		BlockTypeIds::WARPED_SLAB,
		BlockTypeIds::STONE_SLAB,
		BlockTypeIds::SMOOTH_STONE_SLAB,
		BlockTypeIds::GRANITE_SLAB,
		BlockTypeIds::POLISHED_GRANITE_SLAB,
		BlockTypeIds::DIORITE_SLAB,
		BlockTypeIds::POLISHED_DIORITE_SLAB,
		BlockTypeIds::ANDESITE_SLAB,
		BlockTypeIds::POLISHED_ANDESITE_SLAB,
		BlockTypeIds::COBBLESTONE_SLAB,
		BlockTypeIds::MOSSY_COBBLESTONE_SLAB,
		BlockTypeIds::STONE_BRICK_SLAB,
		BlockTypeIds::MOSSY_STONE_BRICK_SLAB,
		BlockTypeIds::BRICK_SLAB,
		BlockTypeIds::END_STONE_BRICK_SLAB,
		BlockTypeIds::NETHER_BRICK_SLAB,
		BlockTypeIds::RED_NETHER_BRICK_SLAB,
		BlockTypeIds::SANDSTONE_SLAB,
		BlockTypeIds::CUT_SANDSTONE_SLAB,
		BlockTypeIds::SMOOTH_SANDSTONE_SLAB,
		BlockTypeIds::RED_SANDSTONE_SLAB,
		BlockTypeIds::CUT_RED_SANDSTONE_SLAB,
		BlockTypeIds::SMOOTH_RED_SANDSTONE_SLAB,
		BlockTypeIds::QUARTZ_SLAB,
		BlockTypeIds::SMOOTH_QUARTZ_SLAB,
		BlockTypeIds::PURPUR_SLAB,
		BlockTypeIds::PRISMARINE_SLAB,
		BlockTypeIds::PRISMARINE_BRICKS_SLAB,
		BlockTypeIds::DARK_PRISMARINE_SLAB,
		BlockTypeIds::BLACKSTONE_SLAB,
		BlockTypeIds::POLISHED_BLACKSTONE_SLAB,
		BlockTypeIds::POLISHED_BLACKSTONE_BRICK_SLAB,
		BlockTypeIds::CUT_COPPER_SLAB,
		//Copper variants
		BlockTypeIds::COBBLED_DEEPSLATE_SLAB,
		BlockTypeIds::POLISHED_DEEPSLATE_SLAB,
		BlockTypeIds::DEEPSLATE_BRICK_SLAB,
		BlockTypeIds::DEEPSLATE_TILE_SLAB,
		BlockTypeIds::FAKE_WOODEN_SLAB,

		//Slate
		BlockTypeIds::SMALL_DRIPLEAF,
		BlockTypeIds::MONSTER_SPAWNER,

		BlockTypeIds::OAK_STAIRS,
		BlockTypeIds::SPRUCE_STAIRS,
		BlockTypeIds::BIRCH_STAIRS,
		BlockTypeIds::JUNGLE_STAIRS,
		BlockTypeIds::ACACIA_STAIRS,
		BlockTypeIds::DARK_OAK_STAIRS,
		BlockTypeIds::MANGROVE_STAIRS,
		//Bamboo Stairs
		BlockTypeIds::CHERRY_STAIRS,
		BlockTypeIds::CRIMSON_STAIRS,
		BlockTypeIds::WARPED_STAIRS,
		BlockTypeIds::STONE_STAIRS,
		BlockTypeIds::GRANITE_STAIRS,
		BlockTypeIds::POLISHED_GRANITE_STAIRS,
		BlockTypeIds::DIORITE_STAIRS,
		BlockTypeIds::POLISHED_DIORITE_STAIRS,
		BlockTypeIds::ANDESITE_STAIRS,
		BlockTypeIds::POLISHED_ANDESITE_STAIRS,
		BlockTypeIds::COBBLESTONE_STAIRS,
		BlockTypeIds::MOSSY_COBBLESTONE_STAIRS,
		BlockTypeIds::STONE_BRICK_STAIRS,
		BlockTypeIds::MOSSY_STONE_BRICK_STAIRS,
		BlockTypeIds::BRICK_STAIRS,
		BlockTypeIds::END_STONE_BRICK_STAIRS,
		BlockTypeIds::NETHER_BRICK_STAIRS,
		BlockTypeIds::RED_NETHER_BRICK_STAIRS,
		BlockTypeIds::SANDSTONE_STAIRS,
		BlockTypeIds::SMOOTH_SANDSTONE_STAIRS,
		BlockTypeIds::RED_SANDSTONE_STAIRS,
		BlockTypeIds::SMOOTH_RED_SANDSTONE_STAIRS,
		BlockTypeIds::QUARTZ_STAIRS,
		BlockTypeIds::SMOOTH_QUARTZ_STAIRS,
		BlockTypeIds::PURPUR_STAIRS,
		BlockTypeIds::PRISMARINE_STAIRS,
		BlockTypeIds::PRISMARINE_BRICKS_STAIRS,
		BlockTypeIds::DARK_PRISMARINE_STAIRS,
		BlockTypeIds::BLACKSTONE_STAIRS,
		BlockTypeIds::POLISHED_BLACKSTONE_STAIRS,
		BlockTypeIds::POLISHED_BLACKSTONE_BRICK_STAIRS,
		BlockTypeIds::CUT_COPPER_STAIRS,
		//Copper variants
		BlockTypeIds::COBBLED_DEEPSLATE_STAIRS,
		BlockTypeIds::POLISHED_DEEPSLATE_STAIRS,
		BlockTypeIds::DEEPSLATE_BRICK_STAIRS,
		BlockTypeIds::DEEPSLATE_TILE_STAIRS,

		BlockTypeIds::STONECUTTER,
		//Tall Seagrass

		BlockTypeIds::IRON_TRAPDOOR,
		BlockTypeIds::OAK_TRAPDOOR,
		BlockTypeIds::SPRUCE_TRAPDOOR,
		BlockTypeIds::BIRCH_TRAPDOOR,
		BlockTypeIds::JUNGLE_TRAPDOOR,
		BlockTypeIds::ACACIA_TRAPDOOR,
		BlockTypeIds::DARK_OAK_TRAPDOOR,
		BlockTypeIds::MANGROVE_TRAPDOOR,
		//Bamboo Trapdoor
		BlockTypeIds::CHERRY_TRAPDOOR,
		BlockTypeIds::CRIMSON_TRAPDOOR,
		BlockTypeIds::WARPED_TRAPDOOR,
		//Copper Trapdoor

		//Turtle Egg
		BlockTypeIds::UNDERWATER_TORCH,
		BlockTypeIds::VINES,

		BlockTypeIds::COBBLESTONE_WALL,
		BlockTypeIds::MOSSY_COBBLESTONE_WALL,
		BlockTypeIds::STONE_BRICK_WALL,
		BlockTypeIds::MOSSY_STONE_BRICK_WALL,
		BlockTypeIds::ANDESITE_WALL,
		BlockTypeIds::DIORITE_WALL,
		BlockTypeIds::GRANITE_WALL,
		BlockTypeIds::SANDSTONE_WALL,
		BlockTypeIds::RED_SANDSTONE_WALL,
		BlockTypeIds::BRICK_WALL,
		BlockTypeIds::PRISMARINE_WALL,
		BlockTypeIds::NETHER_BRICK_WALL,
		BlockTypeIds::RED_NETHER_BRICK_WALL,
		BlockTypeIds::END_STONE_BRICK_WALL,
		BlockTypeIds::BLACKSTONE_WALL,
		BlockTypeIds::POLISHED_BLACKSTONE_WALL,
		BlockTypeIds::POLISHED_BLACKSTONE_BRICK_WALL,
		BlockTypeIds::COBBLED_DEEPSLATE_WALL,
		BlockTypeIds::POLISHED_DEEPSLATE_WALL,
		BlockTypeIds::DEEPSLATE_BRICK_WALL,
		BlockTypeIds::DEEPSLATE_TILE_WALL,
		BlockTypeIds::MUD_BRICK_WALL
	];

	/**
	 * @var int[]
	 */
	private static array $flowingWaterLoggable = [
		BlockTypeIds::BIG_DRIPLEAF_HEAD,
		BlockTypeIds::BIG_DRIPLEAF_STEM,
		BlockTypeIds::COMPOUND_CREATOR,
		BlockTypeIds::ELEMENT_CONSTRUCTOR,
		BlockTypeIds::END_ROD,
		BlockTypeIds::LAB_TABLE,
		BlockTypeIds::LEVER,
		BlockTypeIds::LIGHT,
		BlockTypeIds::MATERIAL_REDUCER,
		//Mangrove Propagule
		BlockTypeIds::REDSTONE_COMPARATOR,
		BlockTypeIds::REDSTONE_REPEATER,
		BlockTypeIds::TRIPWIRE,
		BlockTypeIds::TRIPWIRE_HOOK
	];

	/**
	 * @var int[]
	 */
	private static array $snowLoggable = [
		BlockTypeIds::FERN,
		//Crimson & Warped Fungus
		BlockTypeIds::TALL_GRASS,
		BlockTypeIds::BROWN_MUSHROOM,
		BlockTypeIds::RED_MUSHROOM,
		//Nether Sprouts
		BlockTypeIds::DANDELION,
		BlockTypeIds::POPPY,
		BlockTypeIds::BLUE_ORCHID,
		BlockTypeIds::ALLIUM,
		BlockTypeIds::AZURE_BLUET,
		BlockTypeIds::RED_TULIP,
		BlockTypeIds::ORANGE_TULIP,
		BlockTypeIds::WHITE_TULIP,
		BlockTypeIds::PINK_TULIP,
		BlockTypeIds::OXEYE_DAISY,
		BlockTypeIds::CORNFLOWER,
		BlockTypeIds::LILY_OF_THE_VALLEY,
		BlockTypeIds::WITHER_ROSE,
		BlockTypeIds::TORCHFLOWER,
		BlockTypeIds::PITCHER_PLANT,
		BlockTypeIds::CRIMSON_ROOTS,
		BlockTypeIds::WARPED_ROOTS
	];

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterloggable
	 */
	public static function isWaterLoggable(Block $block): bool
	{
		return in_array($block->getTypeId(), self::$waterLoggable, true) || self::isFlowingWaterLoggable($block);
	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterloggable by flowing water (mojang what even is this; bedrock exclusive)
	 */
	public static function isFlowingWaterLoggable(Block $block): bool
	{
		return in_array($block->getTypeId(), self::$flowingWaterLoggable, true);
	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is snowloggable
	 */
	public static function isSnowLoggable(Block $block): bool
	{
		return in_array($block->getTypeId(), self::$snowLoggable, true);
	}

	/**
	 * @param Block $block
	 * @param int   $facing
	 * @return bool Whether water is blocked from exiting the given block facing
	 */
	public static function blocksWaterFlow(Block $block, int $facing): bool
	{
		return ($block instanceof Slab || $block instanceof Stair) && $block->getSupportType($facing) === SupportType::FULL();
	}

	/**
	 * @return int[]
	 */
	public static function getSnowLoggable(): array
	{
		return self::$snowLoggable;
	}
}