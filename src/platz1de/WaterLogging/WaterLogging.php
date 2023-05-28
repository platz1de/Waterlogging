<?php

namespace platz1de\WaterLogging;

use platz1de\WaterLogging\block\Lava;
use platz1de\WaterLogging\block\SnowLayer;
use platz1de\WaterLogging\block\Water;
use platz1de\WaterLogging\item\Bucket;
use platz1de\WaterLogging\item\LiquidBucket;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\World;
use ReflectionClass;
use UnexpectedValueException;

class WaterLogging extends PluginBase
{
	use SingletonTrait;

	public const WATERLOGGING_LAYER = 1;
	private static Water $water;

	public function onLoad(): void
	{
		self::setInstance($this);

		//Very hacky way to overwrite the fluid blocks, sadly there is no official way to do this anymore
		//As our blocks are pretty much only listeners (because of missing events :/),
		// there shouldn't be any unexpected behaviour (except if another plugin tries to do exactly the same or accesses protected methods via reflections)
		$blockRegistry = RuntimeBlockStateRegistry::getInstance();
		(function () {
			/** @noinspection all */
			unset($this->typeIndex[BlockTypeIds::WATER], $this->typeIndex[BlockTypeIds::LAVA], $this->typeIndex[BlockTypeIds::SNOW_LAYER]); //"free up" the ids
		})->call($blockRegistry);
		$blockRegistry->register(self::$water = new Water(VanillaBlocks::WATER()->getIdInfo(), "Water", new BlockTypeInfo(VanillaBlocks::WATER()->getBreakInfo(), VanillaBlocks::WATER()->getTypeTags())));
		$blockRegistry->register(new Lava(VanillaBlocks::LAVA()->getIdInfo(), "Lava", new BlockTypeInfo(VanillaBlocks::LAVA()->getBreakInfo(), VanillaBlocks::LAVA()->getTypeTags())));
		$blockRegistry->register(new SnowLayer(VanillaBlocks::SNOW_LAYER()->getIdInfo(), "Snow Layer", new BlockTypeInfo(VanillaBlocks::SNOW_LAYER()->getBreakInfo(), VanillaBlocks::SNOW_LAYER()->getTypeTags())));

		//There is no proper way of manipulating block interactions without messing up the whole event system
		//So we have to overwrite the default bucket items
		$reflection = new ReflectionClass(VanillaItems::class);
		$members = $reflection->getStaticPropertyValue("members");
		$members["BUCKET"] = new Bucket(new ItemIdentifier(ItemTypeIds::BUCKET), "Bucket");
		$members["WATER_BUCKET"] = new LiquidBucket(new ItemIdentifier(ItemTypeIds::WATER_BUCKET), "Water Bucket", VanillaBlocks::WATER());
		$reflection->setStaticPropertyValue("members", $members);
		//fix wrongly mapped deserializers
		$itemRegistry = GlobalItemDataHandlers::getDeserializer();
		(function () {
			/** @noinspection all */
			unset($this->deserializers[ItemTypeNames::BUCKET], $this->deserializers[ItemTypeNames::WATER_BUCKET]);
		})->call($itemRegistry);
		$itemRegistry->map(ItemTypeNames::BUCKET, fn() => clone VanillaItems::BUCKET());
		$itemRegistry->map(ItemTypeNames::WATER_BUCKET, fn() => clone VanillaItems::WATER_BUCKET());
	}

	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new LayerUpdateTask(), 1);
	}

	public static function WATER(): Water
	{
		return clone self::$water;
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
	 * @param bool    $validate
	 * @return bool Whether the block is waterlogged
	 */
	public static function isWaterLoggedAt(World $world, Vector3 $pos, bool $validate = true): bool
	{
		return self::getWaterDecayAt($world, $pos, $validate) !== false;
	}

	/**
	 * @param Block $block
	 * @return bool Whether the block is waterlogged by a source block
	 */
	public static function isSourceWaterLogged(Block $block): bool
	{
		return self::isSourceWaterLoggedAt($block->getPosition()->getWorld(), $block->getPosition());
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @param bool    $validate
	 * @return bool Whether the block is waterlogged by a source block
	 */
	public static function isSourceWaterLoggedAt(World $world, Vector3 $pos, bool $validate = true): bool
	{
		return self::getWaterDataAt($world, $pos, $validate) === 0;
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @param bool    $validate Whether to validate if the block is even able to be waterlogged
	 * @return int|false decay of waterlogged block or false if not waterlogged
	 */
	public static function getWaterDecayAt(World $world, Vector3 $pos, bool $validate = true): int|false
	{
		$data = self::getWaterDataAt($world, $pos, $validate);
		return $data === false ? false : $data & 0x07;
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 * @param bool    $validate Whether to validate if the block is even able to be waterlogged
	 * @return int|false metadata of waterlogged block or false if not waterlogged
	 */
	public static function getWaterDataAt(World $world, Vector3 $pos, bool $validate = true): int|false
	{
		try {
			$layer = self::getBlockLayer($world, $pos);
		} catch (UnexpectedValueException) {
			return false; // Water can attempt to flow next to unloaded chunks
		}
		$id = $layer->get($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f);
		if ($id >> Block::INTERNAL_STATE_DATA_BITS !== BlockTypeIds::WATER) {
			return false;
		}
		if ($validate) {
			//We expect this to return a water block, as we already checked the block type
			$tag = GlobalBlockStateHandlers::getSerializer()->serialize($id)->getState(BlockStateNames::LIQUID_DEPTH);
			if (!$tag instanceof IntTag) {
				return false;
			}
			$blockData = $tag->getValue();
			$decay = $blockData & 0x07;
			$falling = ($blockData & 0x08) !== 0;
			if (($decay === 0 && !$falling && !WaterLoggableBlocks::isWaterLoggable($world->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()))) ||
				(($decay !== 0 || $falling) && !WaterLoggableBlocks::isFlowingWaterLoggable($world->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())))) {
				self::getInstance()->getLogger()->debug("Fixed leftover water logging state at {$pos->getX()}, {$pos->getY()}, {$pos->getZ()}");
				self::removeWaterLogging($world->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()));
				return false;
			}
		}
		return $id & Block::INTERNAL_STATE_DATA_MASK;
	}

	/**
	 * @param Block $block
	 * @param int   $decay
	 * @param bool  $falling
	 */
	public static function addWaterLogging(Block $block, int $decay = 0, bool $falling = false): void
	{
		self::setBlockLayerId($block, VanillaBlocks::WATER()->setFalling($falling)->setDecay($decay)->getStateId());
		$block->getPosition()->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), VanillaBlocks::WATER()->tickRate());
		$block->getPosition()->getWorld()->notifyNeighbourBlockUpdate($block->getPosition());
	}

	/**
	 * @param Block $block
	 */
	public static function removeWaterLogging(Block $block): void
	{
		self::clearBlockLayerId($block);
		$block->getPosition()->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), VanillaBlocks::WATER()->tickRate());
		$block->getPosition()->getWorld()->notifyNeighbourBlockUpdate($block->getPosition());
	}

	/**
	 * @param Block $block
	 * @return int|false
	 */
	public static function getSnowPlant(Block $block): int|false
	{
		try {
			$layer = self::getBlockLayer($block->getPosition()->getWorld(), $block->getPosition());
		} catch (UnexpectedValueException) {
			return false; //Shouldn't happen
		}
		$id = $layer->get($block->getPosition()->getX() & 0x0f, $block->getPosition()->getY() & 0x0f, $block->getPosition()->getZ() & 0x0f);
		if (!in_array($id >> Block::INTERNAL_STATE_DATA_BITS, WaterLoggableBlocks::getSnowLoggable(), true)) {
			return false;
		}
		if ($block->getTypeId() !== BlockTypeIds::SNOW_LAYER) {
			self::getInstance()->getLogger()->debug("Fixed leftover snow logging state at {$block->getPosition()->getX()}, {$block->getPosition()->getY()}, {$block->getPosition()->getZ()}");
			self::clearBlockLayerId($block);
			return false;
		}
		return $id;
	}

	/**
	 * @param Block $block
	 * @param int   $flower
	 */
	public static function addSnowLogging(Block $block, int $flower): void
	{
		self::setBlockLayerId($block, $flower);
	}

	/**
	 * @param Block $block
	 */
	public static function clearBlockLayerId(Block $block): void
	{
		$pos = $block->getPosition();
		$subChunk = $pos->getWorld()->getChunk($pos->getX() >> 4, $pos->getZ() >> 4)?->getSubChunk($pos->getY() >> 4);
		if ($subChunk === null) {
			return;
		}
		self::setBlockLayerId($block, $subChunk->getEmptyBlockId());
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
		self::sendUpdate($block->getPosition()->getWorld(), $block->getPosition());
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
			throw new UnexpectedValueException("Block is not in loaded terrain");
		}
		if (!isset($subChunk->getBlockLayers()[self::WATERLOGGING_LAYER])) {
			(function () {
				$this->blockLayers[WaterLogging::WATERLOGGING_LAYER] = new PalettedBlockArray($this->emptyBlockId);
			})->call($subChunk);
		}
		return $subChunk->getBlockLayers()[self::WATERLOGGING_LAYER];
	}

	/**
	 * @param World   $world
	 * @param Vector3 $pos
	 */
	private static function sendUpdate(World $world, Vector3 $pos): void
	{
		$layer = self::getBlockLayer($world, $pos);
		$id = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($layer->get($pos->getX() & 0x0f, $pos->getY() & 0x0f, $pos->getZ() & 0x0f));
		$packet = UpdateBlockPacket::create(BlockPosition::fromVector3($pos), $id, UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_LIQUID);
		foreach ($world->getViewersForPosition($pos) as $player) {
			$player->getNetworkSession()->sendDataPacket($packet);
		}
	}
}