<?php
/**
 * Render callback for the profile editor block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Check if user is logged in
if (!is_user_logged_in()) {
	return '<div class="chuyennhanali-profile-editor-block"><p>' . __('Vui lòng đăng nhập để chỉnh sửa thông tin.', 'nali-custom-block') . '</p></div>';
}

$current_user = wp_get_current_user();

// Handle form submission
if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['nali_update_profile_nonce']) && wp_verify_nonce($_POST['nali_update_profile_nonce'], 'nali_update_profile_action')) {
	// Update user info
	$user_id = $current_user->ID;

	if (isset($_POST['first_name'])) {
		update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
	}
	if (isset($_POST['last_name'])) {
		update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
	}
	if (isset($_POST['display_name'])) {
		wp_update_user(array('ID' => $user_id, 'display_name' => sanitize_text_field($_POST['display_name'])));
	}
	if (isset($_POST['email'])) {
		// Check if email is valid and not used by another user
		$new_email = sanitize_email($_POST['email']);
		if (is_email($new_email) && !email_exists($new_email)) {
			wp_update_user(array('ID' => $user_id, 'user_email' => $new_email));
		}
	}

	// Update billing and shipping addresses
	$billing_fields = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_city', 'billing_state', 'billing_district', 'billing_ward', 'billing_postcode', 'billing_phone');
	$shipping_fields = array('shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_city', 'shipping_state', 'shipping_district', 'shipping_ward', 'shipping_postcode', 'shipping_phone');

	foreach ($billing_fields as $field) {
		if (isset($_POST[$field])) {
			update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
		}
	}

	// Set country to VN by default
	update_user_meta($user_id, 'billing_country', 'VN');

	foreach ($shipping_fields as $field) {
		if (isset($_POST[$field])) {
			update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
		}
	}

	// Set shipping country to VN by default
	update_user_meta($user_id, 'shipping_country', 'VN');

	// Save checkbox state - use "same_as_billing" instead of "ship_to_different_address"
	// If checkbox is checked (same as billing), save '1', otherwise save '0'
	$same_as_billing = isset($_POST['same_as_billing']) ? '1' : '0';
	update_user_meta($user_id, 'shipping_same_as_billing', $same_as_billing);

	echo '<div id="nali-success-message" class="nali-success-message"><p><span class="success-icon">✓</span>' . __('Thông tin đã được cập nhật thành công!', 'nali-custom-block') . '</p></div>';

	// Refresh user data
	$current_user = wp_get_current_user();
}

// Get user data
$user_id = $current_user->ID;
$first_name = get_user_meta($user_id, 'first_name', true);
$last_name = get_user_meta($user_id, 'last_name', true);
$display_name = $current_user->display_name;
$email = $current_user->user_email;

// Get billing and shipping addresses
$billing_address = array();
$shipping_address = array();
if (function_exists('WC') && WC()->customer) {
	$billing_address = WC()->customer->get_billing();
	$shipping_address = WC()->customer->get_shipping();
}

?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<form method="post" class="nali-profile-editor-form">
		<?php wp_nonce_field('nali_update_profile_action', 'nali_update_profile_nonce'); ?>

		<h3><?php _e('Thông tin cá nhân', 'nali-custom-block'); ?></h3>
		<div class="nali-personal-info">
			<p>
				<label for="last_name"><?php _e('Họ', 'nali-custom-block'); ?></label>
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>" class="regular-text" />
			</p>
			<p>
				<label for="first_name"><?php _e('Tên', 'nali-custom-block'); ?></label>
				<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>" class="regular-text" />
			</p>
			<p>
				<label for="display_name"><?php _e('Tên hiển thị', 'nali-custom-block'); ?></label>
				<input type="text" name="display_name" id="display_name" value="<?php echo esc_attr($display_name); ?>" class="regular-text" />
			</p>
			<p>
				<label for="email"><?php _e('Địa chỉ email', 'nali-custom-block'); ?></label>
				<input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" class="regular-text" />
			</p>
		</div>

		<!-- Billing Address Section -->
		<div class="nali-profile-section nali-billing-section">
			<h3><?php _e('Địa chỉ thanh toán', 'nali-custom-block'); ?></h3>
			<div class="nali-profile-fields" id="billing-fields">
				<!-- Row 1: Last Name, First Name, Phone -->
				<p>
					<label for="billing_last_name"><?php _e('Họ', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="billing_last_name" id="billing_last_name" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_last_name', true)); ?>" class="regular-text" required />
				</p>
				<p>
					<label for="billing_first_name"><?php _e('Tên', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="billing_first_name" id="billing_first_name" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_first_name', true)); ?>" class="regular-text" required />
				</p>
				<p>
					<label for="billing_phone"><?php _e('Số điện thoại', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="tel" name="billing_phone" id="billing_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_phone', true)); ?>" class="regular-text" required />
				</p>
				
				<!-- Row 2: Province, District, Ward -->
				<p>
					<label for="billing_state"><?php _e('Tỉnh/Thành phố', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="billing_state" id="billing_state" class="regular-text" required data-selected="<?php echo esc_attr(get_user_meta($user_id, 'billing_state', true)); ?>">
						<option value=""><?php _e('Chọn Tỉnh/Thành phố', 'nali-custom-block'); ?></option>
					</select>
				</p>
				<p>
					<label for="billing_city"><?php _e('Quận/Huyện', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="billing_city" id="billing_city" class="regular-text" required disabled data-selected="<?php echo esc_attr(get_user_meta($user_id, 'billing_city', true)); ?>">
						<option value=""><?php _e('Chọn Quận/Huyện', 'nali-custom-block'); ?></option>
					</select>
				</p>
				<p>
					<label for="billing_district"><?php _e('Phường/Xã', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="billing_district" id="billing_district" class="regular-text" required disabled data-selected="<?php echo esc_attr(get_user_meta($user_id, 'billing_district', true)); ?>">
						<option value=""><?php _e('Chọn Phường/Xã', 'nali-custom-block'); ?></option>
					</select>
				</p>
				
				<!-- Row 3: Full-width Address -->
				<p class="full-width">
					<label for="billing_address_1"><?php _e('Địa chỉ chi tiết', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_address_1', true)); ?>" class="regular-text" placeholder="<?php _e('Số nhà, tên đường', 'nali-custom-block'); ?>" required />
				</p>
				
				<!-- Row 4: Company and Postcode -->
				<p>
					<label for="billing_company"><?php _e('Công ty', 'nali-custom-block'); ?></label>
					<input type="text" name="billing_company" id="billing_company" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_company', true)); ?>" class="regular-text" />
				</p>
				<p>
					<label for="billing_postcode"><?php _e('Mã bưu điện', 'nali-custom-block'); ?></label>
					<input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_postcode', true)); ?>" class="regular-text" readonly placeholder="<?php _e('Tự động điền', 'nali-custom-block'); ?>" />
				</p>
			</div>
		</div>

		<!-- Shipping Address Section -->
		<div class="nali-profile-section nali-shipping-section">
			<h3><?php _e('Địa chỉ giao hàng', 'nali-custom-block'); ?></h3>
			<p class="nali-same-as-billing-checkbox">
				<label for="same_as_billing" class="checkbox-label">
					<input type="checkbox" id="same_as_billing" name="same_as_billing" value="1" <?php checked(get_user_meta($user_id, 'shipping_same_as_billing', true), '1'); ?> />
					<?php _e('Giống với địa chỉ thanh toán', 'nali-custom-block'); ?>
				</label>
			</p>
			<div class="nali-profile-fields nali-shipping-fields hidden" id="shipping-fields">
				<!-- Row 1: Last Name, First Name, Phone -->
				<p>
					<label for="shipping_last_name"><?php _e('Họ', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="shipping_last_name" id="shipping_last_name" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_last_name', true)); ?>" class="regular-text" />
				</p>
				<p>
					<label for="shipping_first_name"><?php _e('Tên', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="shipping_first_name" id="shipping_first_name" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_first_name', true)); ?>" class="regular-text" />
				</p>
				<p>
					<label for="shipping_phone"><?php _e('Số điện thoại', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="tel" name="shipping_phone" id="shipping_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_phone', true)); ?>" class="regular-text" />
				</p>
				
				<!-- Row 2: Province, District, Ward -->
				<p>
					<label for="shipping_state"><?php _e('Tỉnh/Thành phố', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="shipping_state" id="shipping_state" class="regular-text" data-selected="<?php echo esc_attr(get_user_meta($user_id, 'shipping_state', true)); ?>">
						<option value=""><?php _e('Chọn Tỉnh/Thành phố', 'nali-custom-block'); ?></option>
					</select>
				</p>
				<p>
					<label for="shipping_city"><?php _e('Quận/Huyện', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="shipping_city" id="shipping_city" class="regular-text" disabled data-selected="<?php echo esc_attr(get_user_meta($user_id, 'shipping_city', true)); ?>">
						<option value=""><?php _e('Chọn Quận/Huyện', 'nali-custom-block'); ?></option>
					</select>
				</p>
				<p>
					<label for="shipping_district"><?php _e('Phường/Xã', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<select name="shipping_district" id="shipping_district" class="regular-text" disabled data-selected="<?php echo esc_attr(get_user_meta($user_id, 'shipping_district', true)); ?>">
						<option value=""><?php _e('Chọn Phường/Xã', 'nali-custom-block'); ?></option>
					</select>
				</p>
				
				<!-- Row 3: Full-width Address -->
				<p class="full-width">
					<label for="shipping_address_1"><?php _e('Địa chỉ chi tiết', 'nali-custom-block'); ?> <span class="required">*</span></label>
					<input type="text" name="shipping_address_1" id="shipping_address_1" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_address_1', true)); ?>" class="regular-text" placeholder="<?php _e('Số nhà, tên đường', 'nali-custom-block'); ?>" />
				</p>
				
				<!-- Row 4: Company and Postcode -->
				<p>
					<label for="shipping_company"><?php _e('Công ty', 'nali-custom-block'); ?></label>
					<input type="text" name="shipping_company" id="shipping_company" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_company', true)); ?>" class="regular-text" />
				</p>
				<p>
					<label for="shipping_postcode"><?php _e('Mã bưu điện', 'nali-custom-block'); ?></label>
					<input type="text" name="shipping_postcode" id="shipping_postcode" value="<?php echo esc_attr(get_user_meta($user_id, 'shipping_postcode', true)); ?>" class="regular-text" readonly placeholder="<?php _e('Tự động điền', 'nali-custom-block'); ?>" />
				</p>
			</div>
		</div>

		<div class="nali-submit-wrapper">
			<input type="submit" name="submit" value="<?php _e('Cập nhật thông tin', 'nali-custom-block'); ?>" class="button button-primary" />
		</div>
	</form>
</div>
