<?php
/**
 * Plugin Name: NaLi Custom Blocks
 * Plugin URI: https://chuyennhanali.com
 * Description: Tập hợp các Gutenberg custom blocks được phát triển bởi NaLi.
 * Version: 0.1.0
 * Author: NaLi
 * Author URI: https://chuyennhanali.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nali-custom-block
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package ChuyenNhaNaLi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'NALI_CUSTOM_BLOCK_VERSION', '0.1.0' );
define( 'NALI_CUSTOM_BLOCK_PATH', plugin_dir_path( __FILE__ ) );
define( 'NALI_CUSTOM_BLOCK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function chuyennhanali_register_blocks()
{
    /**
     * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
     * based on the registered block metadata.
     * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
     *
     * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
     */
    if (function_exists('wp_register_block_types_from_metadata_collection')) {
        wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
        return;
    }

    /**
     * Registers the block(s) metadata from the `blocks-manifest.php` file.
     * Added to WordPress 6.7 to improve the performance of block type registration.
     *
     * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
     */
    if (function_exists('wp_register_block_metadata_collection')) {
        wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
    }
    /**
     * Registers the block type(s) in the `blocks-manifest.php` file.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     */
    $manifest_data = require __DIR__ . '/build/blocks-manifest.php';
    foreach (array_keys($manifest_data) as $block_type) {
        register_block_type(__DIR__ . "/build/{$block_type}");
    }
}

/**
 * Register custom block category for NaLi blocks
 */
function chuyennhanali_block_categories($categories, $post) {
    return array_merge(
        array(
            array(
                'slug'  => 'nali-blocks',
                'title' => __('NaLi Blocks', 'nali-custom-block'),
                'icon'  => 'dashicons-admin-customizer',
            ),
        ),
        $categories
    );
}

// Hook into init to register blocks.
add_action('init', 'chuyennhanali_register_blocks');

// Register custom block category
add_filter('block_categories_all', 'chuyennhanali_block_categories', 10, 2);

/**
 * Add custom rewrite rule for order details page
 */
function chuyennhanali_add_order_details_endpoint() {
    add_rewrite_endpoint('nali-order', EP_ROOT | EP_PAGES);
}
add_action('init', 'chuyennhanali_add_order_details_endpoint');

/**
 * Flush rewrite rules on plugin activation
 */
function chuyennhanali_activate_plugin() {
    chuyennhanali_add_order_details_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'chuyennhanali_activate_plugin');

/**
 * Flush rewrite rules on plugin deactivation
 */
function chuyennhanali_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'chuyennhanali_deactivate_plugin');

/**
 * Handle order details page display
 */
function chuyennhanali_handle_order_details() {
    $order_id = get_query_var('nali-order');
    
    if ($order_id) {
        // Load order details template
        $template = NALI_CUSTOM_BLOCK_PATH . 'templates/order-details.php';
        
        if (file_exists($template)) {
            include $template;
            exit;
        }
    }
}
add_action('template_redirect', 'chuyennhanali_handle_order_details');

/**
 * Register query var for order details
 */
function chuyennhanali_query_vars($vars) {
    $vars[] = 'nali-order';
    return $vars;
}
add_filter('query_vars', 'chuyennhanali_query_vars');
