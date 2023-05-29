# Waterlogging

Did you ever miss waterlogging on your server? <br>
Give your blocks all the H₂O they deserve

Also supports frozen forms (snowlogging)

## Features

Pretty much like in vanilla, if you want a detailed list:
- Add or remove waterlogging using Buckets
- place blocks in water to waterlog them
- break waterlogged blocks to free the water
- Waterlogged blocks act like normal water sources
  - spreads water to nearby blocks
  - hardens lava to cobblestone or obsidian
  - stairs and slabs can be used to restrict waterflow
- tripwires and some redstone components can be waterlogged by flowing water, no longer being destroyed
- automatic removal of invalid waterlogged blocks (produced by other plugins)
- snowlog small plants by placing snow layers on them or snow layers falling on them

Note: Entity movement caused by flowing water logged blocks is NOT implemented and probably never will.

## Removing this plugin
This plugin uses block layers to store information, while this is the intended storage by mojang, it is only poorly supported by pocketmine.<br>
Removing this plugin will NOT remove waterlogged blocks. They remain saved in your world without any functionality. <br>
Use [BulkLayerEditor](https://poggit.pmmp.io/ci/platz1de/BulkLayerEditor/) to remove them properly.