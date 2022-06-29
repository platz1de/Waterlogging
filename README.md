# Waterlogging

Did you ever miss waterlogging on your server? <br>
Give your blocks all the H₂O they deserve

## Features

Pretty much like in vanilla, if you want a detailed list:
- Add or remove waterlogging using Buckets
- place blocks in water to waterlog them
- break waterlogged blocks to free the water
- Waterlogged blocks act like a normal water source
  - spreads water to nearby blocks
  - hardens lava to cobblestone or obsidian
  - stairs and slabs can be used to restrict waterflow
- tripwires and some redstone components can be waterlogged by flowing water, no longer being destroyed
- automatic removal of invalid waterlogged blocks (produced by other plugins)

Note: Entity movement caused by flowing water logged blocks is NOT implemented and probably never will.

## Removing this plugin
This plugin uses blocklayers to store information, while this is the intended storage by mojang, it is only poorly supported by pocketmine.<br>
Removing this plugin will NOT remove waterlogged blocks. They remain saved in your world wothout any functionallity. <br>
A plugin to remove all waterlogged blocks will follow soon.
