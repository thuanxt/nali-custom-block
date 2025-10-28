<?php
/**
 * Loader for registering all custom blocks.
 *
 * @package ChuyenNhaNaLi
 */

namespace ChuyenNhaNaLi;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register all blocks found in the blocks directory.
 */
function chuyennhanali_register_blocks() {
    // Get all block.json files from blocks directory.
    $block_json_files = glob( NALI_CUSTOM_BLOCK_PATH . 'blocks/*/block.json' );

    // Register each block.
    foreach ( $block_json_files as $block_json_file ) {
        $block_dir = dirname( $block_json_file );
        register_block_type( $block_dir );
    }
}

// Hook into init to register blocks.
add_action( 'init', __NAMESPACE__ . '\chuyennhanali_register_blocks' );
