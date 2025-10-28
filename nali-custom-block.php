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

// Load the plugin loader.
require_once NALI_CUSTOM_BLOCK_PATH . 'includes/loader.php';
