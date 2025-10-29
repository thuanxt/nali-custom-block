<?php
/**
 * Template for custom order details page
 *
 * @package ChuyenNhaNaLi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$order_id = get_query_var('nali-order');

// Check if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
    wp_die( __( 'WooCommerce không được kích hoạt.', 'nali-custom-block' ) );
}

// Get the order
$order = wc_get_order( $order_id );

// Check if order exists and belongs to current user
if ( ! $order || ! is_user_logged_in() ) {
    wp_die( __( 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.', 'nali-custom-block' ) );
}

if ( $order->get_customer_id() != get_current_user_id() ) {
    wp_die( __( 'Bạn không có quyền xem đơn hàng này.', 'nali-custom-block' ) );
}

// Handle order cancellation
$notice = '';
$notice_type = '';

if ( isset( $_GET['cancel_order'] ) && $_GET['cancel_order'] === 'true' ) {
    // Verify nonce
    if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'woocommerce-cancel_order' ) ) {
        // Check if order can be cancelled
        if ( $order->has_status( apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'on-hold', 'failed' ), $order ) ) ) {
            // Cancel the order
            $order->update_status( 'cancelled', __( 'Đơn hàng đã bị hủy bởi khách hàng.', 'nali-custom-block' ) );
            
            // Restore stock
            wc_maybe_increase_stock_levels( $order );
            
            $notice = __( 'Đơn hàng của bạn đã được hủy thành công.', 'nali-custom-block' );
            $notice_type = 'success';
        } else {
            $notice = __( 'Đơn hàng này không thể hủy.', 'nali-custom-block' );
            $notice_type = 'error';
        }
    } else {
        $notice = __( 'Yêu cầu không hợp lệ.', 'nali-custom-block' );
        $notice_type = 'error';
    }
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main nali-order-details">
        
        <div class="order-details-container">
            
            <?php if ( $notice ) : ?>
                <div class="order-notice order-notice-<?php echo esc_attr( $notice_type ); ?>">
                    <p><?php echo esc_html( $notice ); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Order Header -->
            <div class="order-header">
                <h1 class="order-title">
                    <?php printf( __( 'Đơn hàng #%s', 'nali-custom-block' ), $order->get_order_number() ); ?>
                </h1>
                <div class="order-meta">
                    <span class="order-date">
                        <strong><?php esc_html_e( 'Ngày đặt:', 'nali-custom-block' ); ?></strong>
                        <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                    </span>
                    <span class="order-status-badge" style="background-color: <?php echo esc_attr( get_order_status_color( $order->get_status() ) ); ?>;">
                        <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                    </span>
                </div>
                <a href="javascript:history.back()" class="back-to-orders">
                    ← <?php esc_html_e( 'Quay lại danh sách đơn hàng', 'nali-custom-block' ); ?>
                </a>
                
                <!-- Order Actions -->
                <div class="order-actions-bar">
                    <?php
                    $actions = array();
                    
                    // Pay action - for pending/failed orders
                    if ( $order->needs_payment() ) {
                        $actions['pay'] = array(
                            'url'  => $order->get_checkout_payment_url(),
                            'name' => __( 'Thanh toán ngay', 'nali-custom-block' ),
                            'class' => 'pay button-primary',
                        );
                    }
                    
                    // Cancel action - for pending/failed orders
                    if ( in_array( $order->get_status(), array( 'pending', 'failed' ), true ) ) {
                        $actions['cancel'] = array(
                            'url'  => $order->get_cancel_order_url( home_url( '/nali-order/' . $order->get_id() ) ),
                            'name' => __( 'Hủy đơn hàng', 'nali-custom-block' ),
                            'class' => 'cancel button-secondary',
                        );
                    }
                    
                    if ( ! empty( $actions ) ) {
                        echo '<div class="order-actions">';
                        foreach ( $actions as $key => $action ) {
                            $onclick = '';
                            if ( $key === 'cancel' ) {
                                $onclick = ' onclick="return confirm(\'' . esc_js( __( 'Bạn có chắc chắn muốn hủy đơn hàng này?', 'nali-custom-block' ) ) . '\');"';
                            }
                            echo '<a href="' . esc_url( $action['url'] ) . '" class="order-action-button ' . esc_attr( $action['class'] ) . '"' . $onclick . '>' . esc_html( $action['name'] ) . '</a>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Downloads Section (if available) -->
            <?php if ( $order->has_downloadable_item() && $order->is_download_permitted() ) : ?>
            <div class="order-section downloads-section" id="downloads">
                <h2><?php esc_html_e( 'Tải xuống', 'nali-custom-block' ); ?></h2>
                <table class="order-downloads-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Sản phẩm', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Lượt tải còn lại', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Hết hạn', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Tải xuống', 'nali-custom-block' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $downloads = $order->get_downloadable_items();
                        foreach ( $downloads as $download ) :
                            ?>
                            <tr>
                                <td><?php echo esc_html( $download['product_name'] ); ?></td>
                                <td>
                                    <?php
                                    echo esc_html( $download['downloads_remaining'] === '' 
                                        ? __( 'Không giới hạn', 'nali-custom-block' ) 
                                        : $download['downloads_remaining'] );
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo esc_html( $download['access_expires'] === '' 
                                        ? __( 'Không bao giờ', 'nali-custom-block' ) 
                                        : date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) );
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( $download['download_url'] ); ?>" class="download-button">
                                        <?php echo esc_html( $download['download_name'] ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Order Items -->
            <div class="order-section order-items-section">
                <h2><?php esc_html_e( 'Sản phẩm đã đặt', 'nali-custom-block' ); ?></h2>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Sản phẩm', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Số lượng', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Đơn giá', 'nali-custom-block' ); ?></th>
                            <th><?php esc_html_e( 'Tổng', 'nali-custom-block' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( $order->get_items() as $item_id => $item ) :
                            $product = $item->get_product();
                            ?>
                            <tr>
                                <td class="product-name">
                                    <?php
                                    if ( $product ) {
                                        echo '<strong>' . esc_html( $item->get_name() ) . '</strong>';
                                        
                                        // Product metadata
                                        $metadata = $item->get_formatted_meta_data();
                                        if ( ! empty( $metadata ) ) {
                                            echo '<ul class="product-meta">';
                                            foreach ( $metadata as $meta ) {
                                                echo '<li>' . wp_kses_post( $meta->display_key ) . ': ' . wp_kses_post( $meta->display_value ) . '</li>';
                                            }
                                            echo '</ul>';
                                        }
                                    } else {
                                        echo '<strong>' . esc_html( $item->get_name() ) . '</strong>';
                                    }
                                    ?>
                                </td>
                                <td class="product-quantity">
                                    <?php echo esc_html( $item->get_quantity() ); ?>
                                </td>
                                <td class="product-price">
                                    <?php echo wp_kses_post( wc_price( $order->get_item_subtotal( $item, false, true ) ) ); ?>
                                </td>
                                <td class="product-total">
                                    <?php echo wp_kses_post( wc_price( $order->get_line_subtotal( $item, false, true ) ) ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="order-subtotal">
                            <th colspan="3"><?php esc_html_e( 'Tạm tính:', 'nali-custom-block' ); ?></th>
                            <td><?php echo wp_kses_post( $order->get_subtotal_to_display() ); ?></td>
                        </tr>
                        <?php if ( $order->get_total_shipping() > 0 ) : ?>
                        <tr class="order-shipping">
                            <th colspan="3"><?php esc_html_e( 'Phí vận chuyển:', 'nali-custom-block' ); ?></th>
                            <td><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $order->get_total_tax() > 0 ) : ?>
                        <tr class="order-tax">
                            <th colspan="3"><?php esc_html_e( 'Thuế:', 'nali-custom-block' ); ?></th>
                            <td><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $order->get_total_discount() > 0 ) : ?>
                        <tr class="order-discount">
                            <th colspan="3"><?php esc_html_e( 'Giảm giá:', 'nali-custom-block' ); ?></th>
                            <td>-<?php echo wp_kses_post( wc_price( $order->get_total_discount() ) ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="order-total">
                            <th colspan="3"><strong><?php esc_html_e( 'Tổng cộng:', 'nali-custom-block' ); ?></strong></th>
                            <td><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Order Details Grid -->
            <div class="order-details-grid">
                
                <!-- Billing Address -->
                <div class="order-section billing-section">
                    <h2><?php esc_html_e( 'Địa chỉ thanh toán', 'nali-custom-block' ); ?></h2>
                    <address>
                        <?php echo wp_kses_post( $order->get_formatted_billing_address() ?: __( 'Không có địa chỉ thanh toán.', 'nali-custom-block' ) ); ?>
                    </address>
                    <?php if ( $order->get_billing_phone() ) : ?>
                        <p class="phone">
                            <strong><?php esc_html_e( 'Điện thoại:', 'nali-custom-block' ); ?></strong>
                            <?php echo esc_html( $order->get_billing_phone() ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $order->get_billing_email() ) : ?>
                        <p class="email">
                            <strong><?php esc_html_e( 'Email:', 'nali-custom-block' ); ?></strong>
                            <?php echo esc_html( $order->get_billing_email() ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Shipping Address -->
                <div class="order-section shipping-section">
                    <h2><?php esc_html_e( 'Địa chỉ giao hàng', 'nali-custom-block' ); ?></h2>
                    <address>
                        <?php echo wp_kses_post( $order->get_formatted_shipping_address() ?: __( 'Không có địa chỉ giao hàng.', 'nali-custom-block' ) ); ?>
                    </address>
                    <?php if ( $order->get_shipping_method() ) : ?>
                        <p class="shipping-method">
                            <strong><?php esc_html_e( 'Phương thức vận chuyển:', 'nali-custom-block' ); ?></strong>
                            <?php echo esc_html( $order->get_shipping_method() ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Payment Method -->
                <div class="order-section payment-section">
                    <h2><?php esc_html_e( 'Thanh toán', 'nali-custom-block' ); ?></h2>
                    <p>
                        <strong><?php esc_html_e( 'Phương thức:', 'nali-custom-block' ); ?></strong>
                        <?php echo esc_html( $order->get_payment_method_title() ); ?>
                    </p>
                    <?php if ( $order->get_transaction_id() ) : ?>
                        <p>
                            <strong><?php esc_html_e( 'Mã giao dịch:', 'nali-custom-block' ); ?></strong>
                            <?php echo esc_html( $order->get_transaction_id() ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Order Notes -->
                <?php if ( $order->get_customer_note() ) : ?>
                <div class="order-section notes-section">
                    <h2><?php esc_html_e( 'Ghi chú', 'nali-custom-block' ); ?></h2>
                    <p><?php echo wp_kses_post( nl2br( $order->get_customer_note() ) ); ?></p>
                </div>
                <?php endif; ?>
                
            </div>

        </div>

        <style>
        .nali-order-details {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .order-details-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .order-notice {
            padding: 15px 20px;
            margin: 20px 30px;
            border-radius: 6px;
            border-left: 4px solid;
            font-weight: 500;
        }

        .order-notice-success {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }

        .order-notice-error {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .order-notice p {
            margin: 0;
        }

        .order-header {
            padding: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-title {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 28px;
        }

        .order-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .order-date {
            color: #666;
        }

        .order-status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .back-to-orders {
            color: #0073aa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .back-to-orders:hover {
            color: #005177;
        }

        .order-actions-bar {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .order-action-button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 15px;
            border: none;
            cursor: pointer;
        }

        .order-action-button.button-primary {
            background-color: #10b981;
            color: #fff;
        }

        .order-action-button.button-primary:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .order-action-button.button-secondary {
            background-color: #ef4444;
            color: #fff;
        }

        .order-action-button.button-secondary:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .downloads-section {
            background-color: #f8f9fa;
        }

        .order-downloads-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-downloads-table th,
        .order-downloads-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-downloads-table thead th {
            background: #fff;
            font-weight: 600;
            color: #333;
        }

        .download-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #8b5cf6;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .download-button:hover {
            background-color: #7c3aed;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
        }

        .order-section {
            padding: 30px;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-section:last-child {
            border-bottom: none;
        }

        .order-section h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #333;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-items-table th,
        .order-items-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-items-table thead th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .order-items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .order-items-table tfoot th,
        .order-items-table tfoot td {
            padding: 12px 15px;
            border-top: 1px solid #e0e0e0;
        }

        .order-items-table tfoot .order-total th,
        .order-items-table tfoot .order-total td {
            border-top: 2px solid #333;
            font-size: 18px;
        }

        .product-meta {
            margin: 8px 0 0 0;
            padding: 0;
            list-style: none;
            font-size: 13px;
            color: #666;
        }

        .product-meta li {
            margin: 4px 0;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 0;
        }

        .order-details-grid .order-section {
            border-right: 1px solid #f0f0f0;
        }

        .order-details-grid .order-section:last-child {
            border-right: none;
        }

        address {
            font-style: normal;
            line-height: 1.8;
        }

        .phone, .email, .shipping-method {
            margin: 10px 0;
            line-height: 1.8;
        }

        @media (max-width: 768px) {
            .nali-order-details {
                padding: 20px 10px;
            }

            .order-header {
                padding: 20px;
            }

            .order-title {
                font-size: 22px;
            }

            .order-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-section {
                padding: 20px;
            }

            .order-items-table {
                font-size: 14px;
            }

            .order-items-table thead {
                display: none;
            }

            .order-items-table tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
            }

            .order-items-table tbody td {
                display: block;
                text-align: right;
                padding: 10px 15px;
            }

            .order-items-table tbody td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
            }

            .order-details-grid .order-section {
                border-right: none;
            }
        }
        </style>

    </main>
</div>

<?php
get_footer();

/**
 * Helper function to get order status color
 */
function get_order_status_color( $status ) {
    $colors = array(
        'pending'    => '#f59e0b',
        'processing' => '#3b82f6',
        'on-hold'    => '#6b7280',
        'completed'  => '#10b981',
        'cancelled'  => '#ef4444',
        'refunded'   => '#8b5cf6',
        'failed'     => '#dc2626',
    );
    
    return isset( $colors[ $status ] ) ? $colors[ $status ] : '#6b7280';
}
