<?php
/**
 * Gutenberg Social Icon Variations
 * 
 * @package gutenberg-social-icon-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.0.1
 * @since 1.0.0
 * 
 * @wordpress-plugin
 * Plugin Name: Gutenberg Social Icon Variations
 * Plugin URI: https://dcdalrymple.com
 * Description: Demonstration of block variations for the social icon block.
 * Version: 1.0.1
 * Author: Cooper Dalrymple
 * Author URI: https://dcdalrymple.com
 * Text Domain: gsiv
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

define('GSIV_FILE', __FILE__);
define('GSIV_DIR', plugin_dir_path(GSIV_FILE));
define('GSIV_URL', plugin_dir_url(GSIV_FILE));

function get_data(string $key = ''):array|string {
    $data = get_plugin_data(GSIV_FILE);
    if (empty($key)) return $data;
    return (string)$data[$key] ?? '';
}

function get_version():string {
    return get_data('Version');
}

function get_icons():array {
    $icons = wp_json_file_decode(GSIV_DIR . 'icons.json', [
        'associative'=> true,
    ]);
    if (is_null($icons)) $icons = [];
    foreach ($icons as &$icon) {
        $icon['title'] = __($icon['title'], 'gsiv');
    }
    return apply_filters('gsiv_icons', $icons);
}

function get_icon_html(array $icon):string {
    return apply_filters(
        'gsiv_icon_html',
            sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" height="%s" width="%s" viewBox="%s"><path d="%s"/></svg>',
            esc_attr($icon['height']),
            esc_attr($icon['width']),
            esc_attr($icon['viewBox']),
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
    wp_enqueue_script(
        'gsiv',
        GSIV_URL . 'block-editor.js',
        [
            'wp-blocks',
            'wp-dom-ready',
            'wp-i18n'
        ],
        get_version(),
        true
    );
    wp_localize_script('gsiv', 'gsiv_icons', get_icons());
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets');
