<?php
/**
 * Plugin Name: Product Giveaway Codes for WooCommerce
 * Description: Batch generates unique coupon codes for product giveaways in WooCommerce.
 * Version: 1.0.1
 * Author: Hearken Media
 * Author URI: http://hearkenmedia.com/landing-wp-plugin.php?utm_source=coupon-generator-wc&utm_medium=link&utm_campaign=wp-widget-link
 * License: GNU General Public License version 2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */


// Add the Coupon Creator to the WordPress admin
add_action('admin_menu', function() {
	add_submenu_page('woocommerce', 'Giveaway Codes', 'Giveaway Codes', 'manage_woocommerce', 'hm_cgwc', 'hm_cgwc_page');
});

// This function generates the Coupon Creator page HTML
function hm_cgwc_page() {

	// Print header
	echo('
		<div class="wrap">
			<h2>Product Giveaway Codes</h2>
	');
	
	// Check for WooCommerce
	if (!class_exists('WooCommerce')) {
		echo('<div class="error"><p>This plugin requires that WooCommerce is installed and activated.</p></div></div>');
		return;
	}
	
	// Print form
	
	echo('<form action="" method="post" target="_blank">
				<input type="hidden" name="hm_cgwc_do_generate" value="1" />
		');
	wp_nonce_field('hm_cgwc_do_generate');
	echo('
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_coupon_series_id">Code Series ID:</label>
						</th>
						<td>
							<input type="text" name="coupon_series_id" id="hm_cgwc_field_coupon_series_id" />
							<p class="description">Enter a unique name for this batch of giveaway codes. You can use this if you want to delete the entire batch later.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_product_ids">Product ID:</label>
						</th>
						<td>
							<input type="number" name="product_ids" id="hm_cgwc_field_product_ids" required="true" />
							<p class="description">Enter the ID of the product to give away. Hover over the product in the <a href="edit.php?post_type=product" target="_blank">list</a> to see the ID.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_coupon_count"># of Codes:</label>
						</th>
						<td>
							<input type="number" name="coupon_count" id="hm_cgwc_field_coupon_count" />
							<p class="description">Enter the number of giveaway codes to generate.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_usage_limit"># of Uses:</label>
						</th>
						<td>
							<input type="number" name="usage_limit" id="hm_cgwc_field_usage_limit" value="1" />
							<p class="description">Enter the number of times each code may be used.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_expiry">Expiry Date:</label>
						</th>
						<td>
							<input type="date" name="expiry" id="hm_cgwc_field_expiry" value="'.date('Y-m-d', strtotime('+1 month')).'" />
							<p class="description">Enter the giveaway code expiry date.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_cblock_length">Code Length:</label>
						</th>
						<td>
							<input type="number" name="cblock_count" id="hm_cgwc_field_cblock_count" value="3" /> blocks of
							<input type="number" name="cblock_length" id="hm_cgwc_field_cblock_length" value="4" /> characters
							<p class="description">Enter the length of the codes to be generated (default format: XXXX-XXXX-XXXX).</p>
						</td>
					</tr>
				</table>');
				
				echo('<p class="submit">
					<button type="submit" class="button-primary">Get Giveaway Codes</button>
				</p>
			</form>');
			
			
			echo('<form action="" method="post">
				<input type="hidden" name="hm_cgwc_do_delete" value="1" />
				');
			wp_nonce_field('hm_cgwc_do_delete');
			echo('
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="hm_cgwc_field_coupon_series_id_delete">Code Series ID:</label>
						</th>
						<td>
							<input type="text" name="coupon_series_id_delete" id="hm_cgwc_field_coupon_series_id_delete" />
							<p class="description">Enter the series ID specified when creating the giveaway codes.</p>
						</td>
					</tr>
			</table>');
				
			echo('<p class="submit">
					<button type="submit" class="button-primary">Delete Giveaway Codes</button>
				</p>
			</form>');
			
			
				echo('
				<div style="background-color: #fff; border: 1px solid #ccc; padding: 20px; display: inline-block;">
					<h3 style="margin: 0;">Plugin by:</h3>
					<a href="http://hearkenmedia.com/landing-wp-plugin.php?utm_source=coupon-generator-wc&amp;utm_medium=link&amp;utm_campaign=wp-widget-link" target="_blank"><img src="'.plugins_url('images/hm-logo.png', __FILE__).'" alt="Hearken Media" style="width: 250px;" /></a><br />
					<a href="https://wordpress.org/support/view/plugin-reviews/product-giveaway-codes-for-woocommerce" target="_blank"><strong>
						If you find this plugin useful, please write a brief review!
					</strong></a>
				</div>
				');

			
	echo('
		</div>
		
		<script type="text/javascript" src="'.plugins_url('js/hm-coupon-generator-wc.js', __FILE__).'"></script>
	');
	
	


}

// Hook into WordPress init; this function performs report generation when
// the admin form is submitted
add_action('init', 'hm_cgwc_on_init');
function hm_cgwc_on_init() {
	global $pagenow;
	
	// Check if we are in admin and on the report page
	if (!is_admin())
		return;
	if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'hm_cgwc') {
	
		if (!empty($_POST['hm_cgwc_do_generate']) && !empty($_POST['product_ids']) && is_numeric($_POST['product_ids'])) {
			
			// Verify the nonce
			check_admin_referer('hm_cgwc_do_generate');
			
			
			// Send headers
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="giveaway_codes.txt"');
			
			$seriesId = (empty($_POST['coupon_series_id']) ? 'series_'.time() : $_POST['coupon_series_id']);
			$productId =  $_POST['product_ids'];
			$couponCount = (!empty($_POST['coupon_count']) && is_numeric($_POST['coupon_count']) ? $_POST['coupon_count'] : 0);
			$usageLimit =  (!empty($_POST['usage_limit']) && is_numeric($_POST['usage_limit']) ? $_POST['usage_limit'] : 1);
			$expiry = (empty($_POST['expiry']) ? date('Y-m-d', time() + (86400 * 7)) : $_POST['expiry']);
			$blockLength = (!empty($_POST['cblock_length']) && is_numeric($_POST['cblock_length']) ? $_POST['cblock_length'] : 4);
			$blockCount = (!empty($_POST['cblock_count']) && is_numeric($_POST['cblock_count']) ? $_POST['cblock_count'] : 3);
			$codeChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$codeCharsCount = strlen($codeChars);
			
			echo("Code Series ID: $seriesId\r\n\r\n");
			
			$coupon = array(
				'post_content' => '',
				'post_status' => 'publish',
				'post_type' => 'shop_coupon'
			);
			
			for ($i = 0; $i < $couponCount; ++$i) {
				
				// TODO: die after too many tries
				do {
					$code = '';
					for ($j = 0; $j < $blockCount; ++$j) {
						if ($j > 0)
							$code .= '-';
						for ($k = 0; $k < $blockLength; ++$k)
							$code .= $codeChars[rand(0, $codeCharsCount - 1)];
					}
				} while (get_page_by_title($code, ARRAY_N, 'shop_coupon') !== null);
				
				$coupon['post_title'] = $code;
				
				if (($postId = wp_insert_post($coupon)) !== null) {
					
					update_post_meta($postId, 'discount_type', 'percent_product');
					update_post_meta($postId, 'coupon_amount', '100');
					update_post_meta($postId, 'individual_use', 'no');
					update_post_meta($postId, 'product_ids', $productIds);
					update_post_meta($postId, 'exclude_product_ids', '');
					update_post_meta($postId, 'usage_limit', $usageLimit);
					update_post_meta($postId, 'usage_limit_per_user', '');
					update_post_meta($postId, 'limit_usage_to_x_items', '1');
					update_post_meta($postId, 'expiry_date', $expiry);
					update_post_meta($postId, 'free_shipping', 'no');
					update_post_meta($postId, 'exclude_sale_items', 'no');
					update_post_meta($postId, 'product_categories', 'a:0:{}');
					update_post_meta($postId, 'exclude_product_categories', 'a:0:{}');
					update_post_meta($postId, 'minimum_amount', '');
					update_post_meta($postId, 'maximum_amount', '');
					update_post_meta($postId, 'customer_email', 'a:0:{}');
					update_post_meta($postId, 'hm_cgwc_series_id', $seriesId);
					
					echo("$code\r\n");
					
				}
			
			}
			
			exit;
			
			
		} else if (!empty($_POST['hm_cgwc_do_delete']) && !empty($_POST['coupon_series_id_delete'])) {
			
			// Verify the nonce
			check_admin_referer('hm_cgwc_do_delete');
			
			$deleteQuery = new WP_Query(array(
				'post_type' => 'shop_coupon',
				'meta_key' => 'hm_cgwc_series_id',
				'meta_value' => $_POST['coupon_series_id_delete'],
				'nopaging' => true
			));
			
			foreach($deleteQuery->get_posts() as $post) {
				wp_delete_post($post->ID);
			}
		}
	
	}
}

add_action('admin_enqueue_scripts', 'hm_cgwc_admin_enqueue_scripts');
function hm_cgwc_admin_enqueue_scripts() {
	wp_register_style('hm_cgwc_admin_style', plugins_url('css/hm-coupon-generator-wc.css', __FILE__));
	wp_enqueue_style('hm_cgwc_admin_style');
}
?>