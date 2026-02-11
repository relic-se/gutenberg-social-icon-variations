=== Social Icon Block Variations ===
Contributors: Cooper Dalrymple
Donate link: https://dcdalrymple.com
Tags: block, icons, social, variations, phone
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.9
Stable tag: 1.0.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Demonstration of block variations for the social icon block.

== Description ==

This plugin adds the following icons to the Social Icons block:

* Location
* Phone

Known issues:

* Icons do not render properly within the block editor content and will display the default link icon.
* Default titles are not currently supported. The icon variations included in this plugin will have a default title of "Social Icon".

== Screenshots ==

1. Demonstration of social icon variations within block editor.
2. Demonstration of social icon variations on frontend.

== Changelog ==

= 1.0.3 - 2026-02-11 =
* DEV: Added `GSIV\get_icon_attributes` function.
* DEV: Added `"gsiv_icon_attributes"` filter.
* DEV: Added composer support.
* DEV: Added `"gsiv_icon_filename"` filter.

= 1.0.2 - 2025-08-01 =
* NEW: Allow icon data to be overwritten by providing `icons.json` file in either parent or child theme.

= 1.0.1 - 2025-07-11 =
* NEW: Dynamically generate icon data.

= 1.0.0 - 2025-07-11 =
* Initial build.
