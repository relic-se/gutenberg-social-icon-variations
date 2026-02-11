<?php
/**
 * Social Icon Block Variations
 * 
 * @package social-icon-block-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.1.0
 * @since 1.0.0
 * 
 * @wordpress-plugin
 * Plugin Name: Social Icon Block Variations
 * Plugin URI: https://dcdalrymple.com
 * Description: Demonstration of block variations for the social icon block.
 * Version: 1.1.0
 * Author: Cooper Dalrymple
 * Author URI: https://dcdalrymple.com
 * Text Domain: social-icon-block-variations
 * Domain Path: /lang
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

namespace GSIV;

use DOMDocument;
use WP_Block;

defined('ABSPATH') || exit;

function get_data(string $key = ''):array|string {
    $data = get_plugin_data(__FILE__);
    if (empty($key)) return $data;
    return (string)$data[$key] ?? '';
}

function get_version():string {
    return (string) get_data('Version');
}

function get_icons():array {
    $filename = trim((string)apply_filters('gsiv_icon_filename', 'icons.json'), '/');

    // Support theme icons
    $paths = (array)apply_filters('gsiv_icon_paths', [
        trailingslashit(get_stylesheet_directory()) . $filename,
        trailingslashit(get_template_directory()) . $filename,
    ]);
    $paths = array_filter($paths, 'file_exists');
    $paths = array_reverse($paths); // Change order (child overrides parent)

    // Use default icons if theme doesn't exist
    if (empty($paths)) $paths[] = trailingslashit(plugin_dir_path(__FILE__)) . $filename;

    // Decode and merge icon data
    $icons = [];
    foreach ($paths as $path) {
        $_icons = wp_json_file_decode($path, [
            'associative'=> true,
        ]);
        if (is_null($_icons)) continue;
        $icons = array_merge($icons, $_icons);
    }

    return (array) apply_filters('gsiv_icons', $icons);
}

function get_icon_attributes(array $icon):array {
    return (array) apply_filters(
        'gsiv_icon_attributes',
        array_filter([
            'height' => $icon['height'] ?? '',
            'width' => $icon['width'] ?? '',
            'viewBox' => $icon['viewBox'] ?? '',
        ]),
        $icon
    );
}

function get_icon_html(array $icon):string {
    $attrs = get_icon_attributes($icon);
    return (string) apply_filters(
        'gsiv_icon_html',
            sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg"%s><path d="%s"/></svg>',
            rtrim(' ' . implode(' ', array_map(
                fn ($key, $value) => sprintf('%s="%s"', $key, esc_attr($value)),
                array_keys($attrs),
                array_values($attrs)
            ))),
            esc_attr($icon['path'])
        ),
        $icon
    );
}

function render_block(string $block_content, array $block, WP_Block $instance):string {
    $icons = get_icons();
    if (empty($icons)) return $block_content;

    $service = trim($block['attrs']['service'] ?? '');
    $key = array_search($service, wp_list_pluck($icons, 'name'));
    if ($key === false) return $block_content;

    $icon = get_icon_html($icons[$key]);

    $dom = new DOMDocument();
    $dom->loadXML($block_content, LIBXML_NOERROR | LIBXML_NOXMLDECL);
    
    $fragment = $dom->createDocumentFragment();
    $fragment->appendXML($icon);
    $newElement = $fragment->firstElementChild;
    
    $oldElement = $dom->getElementsByTagName('svg')->item(0);
    $oldElement->parentNode->insertBefore($newElement, $oldElement);
    $oldElement->parentNode->removeChild($oldElement);

    $html = $dom->saveXML();
    $html = substr($html, strpos($html, '?>') + 2);
    return $html;
}
add_filter('render_block_core/social-link', __NAMESPACE__ . '\render_block', 10, 3);

function enqueue_block_editor_assets():void {
	$path = plugin_dir_path(__FILE__);
	if (!str_starts_with($path, WP_CONTENT_DIR)) return;
	$url = trailingslashit(WP_CONTENT_URL . substr($path, strlen(WP_CONTENT_DIR)));

    wp_enqueue_script(
        'social-icon-block-variations',
        $url . 'block-editor.js',
        [
            'wp-blocks',
            'wp-dom-ready',
            'wp-i18n'
        ],
        get_version(),
        true
    );
    wp_localize_script('social-icon-block-variations', 'gsiv_icons', get_icons());
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets');
