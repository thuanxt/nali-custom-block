<?php
/**
 * Render callback for the example block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$message = isset( $attributes['message'] ) ? $attributes['message'] : 'Xin chào từ NaLi Side Bar Menu!';
?>

<div class="chuyennhanali-sidebar-menu-block">
    <p><?php echo esc_html( $message ); ?></p>
</div>
