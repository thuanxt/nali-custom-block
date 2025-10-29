<?php
/**
 * Render callback for the WooCommerce Orders block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Check if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
    ?>
    <div class="chuyennhanali-woo-orders">
        <p><?php esc_html_e( 'WooCommerce không được kích hoạt.', 'nali-custom-block' ); ?></p>
    </div>
    <?php
    return;
}

// Check if user is logged in
if ( ! is_user_logged_in() ) {
    ?>
    <div class="chuyennhanali-woo-orders">
        <p><?php esc_html_e( 'Vui lòng đăng nhập để xem đơn hàng của bạn.', 'nali-custom-block' ); ?></p>
    </div>
    <?php
    return;
}

// Get the orders per page attribute
$orders_per_page = isset( $attributes['ordersPerPage'] ) ? intval( $attributes['ordersPerPage'] ) : 10;
$show_pagination = isset( $attributes['showPagination'] ) ? $attributes['showPagination'] : true;
$status_colors = isset( $attributes['statusColors'] ) ? $attributes['statusColors'] : array(
    'pending'    => '#f59e0b',
    'processing' => '#3b82f6',
    'on-hold'    => '#6b7280',
    'completed'  => '#10b981',
    'cancelled'  => '#ef4444',
    'refunded'   => '#8b5cf6',
    'failed'     => '#dc2626',
);

// Get current user ID
$customer_id = get_current_user_id();

// Build query args
if ( $show_pagination ) {
    // With pagination
    $current_page = isset( $_GET['orders_page'] ) ? absint( $_GET['orders_page'] ) : 1;
    
    $args = array(
        'customer_id' => $customer_id,
        'limit'       => $orders_per_page,
        'orderby'     => 'date',
        'order'       => 'DESC',
        'paginate'    => true,
        'page'        => $current_page,
    );
    
    $result = wc_get_orders( $args );
    
    // Handle the result properly
    if ( is_object($result) && isset($result->orders) ) {
        $customer_orders = is_array($result->orders) ? $result->orders : array();
        $total_orders = isset($result->total) ? $result->total : count($customer_orders);
        $max_num_pages = isset($result->max_num_pages) ? $result->max_num_pages : 1;
    } else {
        // Fallback: result might be array directly
        $customer_orders = is_array($result) ? $result : array();
        $total_orders = count($customer_orders);
        $max_num_pages = 1;
    }
} else {
    // Without pagination - get all orders
    $args = array(
        'customer_id' => $customer_id,
        'limit'       => -1, // Get all orders
        'orderby'     => 'date',
        'order'       => 'DESC',
        'paginate'    => false,
    );
    
    $customer_orders = wc_get_orders( $args );
    $total_orders = count( $customer_orders );
    $max_num_pages = 1;
    $current_page = 1;
}
?>

<div class="chuyennhanali-woo-orders">
    <?php if ( isset( $_GET['nali_order_cancelled'] ) && '1' === $_GET['nali_order_cancelled'] ) : ?>
        <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
            <p><?php esc_html_e( 'Đơn hàng đã được hủy thành công.', 'nali-custom-block' ); ?></p>
        </div>
    <?php endif; ?>
    <?php if ( ! empty( $customer_orders ) ) : ?>
        <?php if ( ! $show_pagination ) : ?>
            <div class="orders-table-wrapper" style="max-height: 600px; overflow-y: auto;">
        <?php endif; ?>
        
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders">
            <thead>
                <tr>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e( 'Đơn hàng', 'nali-custom-block' ); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?php esc_html_e( 'Ngày', 'nali-custom-block' ); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e( 'Trạng thái', 'nali-custom-block' ); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php esc_html_e( 'Tổng cộng', 'nali-custom-block' ); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr"><?php esc_html_e( 'Hành động', 'nali-custom-block' ); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ( $customer_orders as $order ) : 
                    // $order is already a WC_Order object
                    if ( ! is_a( $order, 'WC_Order' ) ) {
                        continue;
                    }
                    $item_count = $order->get_item_count();
                ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'Đơn hàng', 'nali-custom-block' ); ?>">
                            <a href="<?php echo esc_url( home_url( '/nali-order/' . $order->get_id() ) ); ?>">
                                #<?php echo esc_html( $order->get_order_number() ); ?>
                            </a>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="<?php esc_attr_e( 'Ngày', 'nali-custom-block' ); ?>">
                            <time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="<?php esc_attr_e( 'Trạng thái', 'nali-custom-block' ); ?>">
                            <?php 
                            $status = $order->get_status();
                            $status_name = wc_get_order_status_name( $status );
                            $status_color = isset( $status_colors[ $status ] ) ? $status_colors[ $status ] : '#6b7280';
                            ?>
                            <span class="order-status-badge" style="background-color: <?php echo esc_attr( $status_color ); ?>; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">
                                <?php echo esc_html( $status_name ); ?>
                            </span>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="<?php esc_attr_e( 'Tổng cộng', 'nali-custom-block' ); ?>">
                            <?php
                            /* translators: 1: formatted order total 2: total order items */
                            echo wp_kses_post( sprintf( _n( '%1$s cho %2$s sản phẩm', '%1$s cho %2$s sản phẩm', $item_count, 'nali-custom-block' ), $order->get_formatted_order_total(), $item_count ) );
                            ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="<?php esc_attr_e( 'Hành động', 'nali-custom-block' ); ?>">
                            <div class="order-actions">
                                <a href="<?php echo esc_url( home_url( '/nali-order/' . $order->get_id() ) ); ?>" class="woocommerce-button button view"><?php esc_html_e( 'Xem', 'nali-custom-block' ); ?></a>
                                
                                <?php
                                $actions = array();
                                
                                // Pay action - for pending/failed orders
                                if ( $order->needs_payment() ) {
                                    $actions['pay'] = array(
                                        'url'  => $order->get_checkout_payment_url(),
                                        'name' => __( 'Thanh toán', 'nali-custom-block' ),
                                        'class' => 'pay',
                                    );
                                }
                                
                                // Build redirect back to current list with success flag
                                $list_url = remove_query_arg( array( 'nali_order_cancelled', '_wpnonce', 'cancel_order', 'order' ) );
                                $redirect_back_url = add_query_arg( 'nali_order_cancelled', '1', $list_url );

                                // Cancel action - for pending/failed/draft orders
                                if ( in_array( $order->get_status(), array( 'pending', 'failed', 'draft' ), true ) ) {
                                    $actions['cancel'] = array(
                                        'url'  => $order->get_cancel_order_url( $redirect_back_url ),
                                        'name' => __( 'Hủy', 'nali-custom-block' ),
                                        'class' => 'cancel',
                                    );
                                }
                                
                                // Downloads section link (if applicable)
                                if ( $order->has_downloadable_item() && $order->is_download_permitted() ) {
                                    $actions['downloads'] = array(
                                        'url'  => home_url( '/nali-order/' . $order->get_id() . '#downloads' ),
                                        'name' => __( 'Tải xuống', 'nali-custom-block' ),
                                        'class' => 'downloads',
                                    );
                                }
                                
                                foreach ( $actions as $key => $action ) {
                                    $onclick = '';
                                    if ( $key === 'cancel' ) {
                                        $onclick = ' onclick="return confirm(\'' . esc_js( __( 'Bạn có chắc chắn muốn hủy đơn hàng này?', 'nali-custom-block' ) ) . '\');"';
                                    }
                                    echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . esc_attr( $action['class'] ) . '"' . $onclick . '>' . esc_html( $action['name'] ) . '</a>';
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ( ! $show_pagination ) : ?>
            </div><!-- .orders-table-wrapper -->
        <?php endif; ?>
        
        <?php if ( $show_pagination && $max_num_pages > 1 ) : ?>
            <nav class="woocommerce-pagination">
                <?php
                $current_url = remove_query_arg( 'orders_page' );
                
                echo '<ul class="page-numbers">';
                
                // Previous button
                if ( $current_page > 1 ) {
                    $prev_page = $current_page - 1;
                    $prev_url = add_query_arg( 'orders_page', $prev_page, $current_url );
                    echo '<li><a class="prev page-numbers" href="' . esc_url( $prev_url ) . '">' . esc_html__( '← Trước', 'nali-custom-block' ) . '</a></li>';
                }
                
                // Page numbers
                for ( $i = 1; $i <= $max_num_pages; $i++ ) {
                    if ( $i == $current_page ) {
                        echo '<li><span aria-current="page" class="page-numbers current">' . $i . '</span></li>';
                    } else {
                        $page_url = add_query_arg( 'orders_page', $i, $current_url );
                        echo '<li><a class="page-numbers" href="' . esc_url( $page_url ) . '">' . $i . '</a></li>';
                    }
                }
                
                // Next button
                if ( $current_page < $max_num_pages ) {
                    $next_page = $current_page + 1;
                    $next_url = add_query_arg( 'orders_page', $next_page, $current_url );
                    echo '<li><a class="next page-numbers" href="' . esc_url( $next_url ) . '">' . esc_html__( 'Tiếp →', 'nali-custom-block' ) . '</a></li>';
                }
                
                echo '</ul>';
                ?>
            </nav>
        <?php endif; ?>
        
    <?php else : ?>
        <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
            <p><?php esc_html_e( 'Chưa có đơn hàng nào.', 'nali-custom-block' ); ?></p>
        </div>
    <?php endif; ?>
</div>
