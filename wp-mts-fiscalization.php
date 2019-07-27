<?php
/**
 * @package wp-mts-fiscalization
 */

/*
Plugin Name: MTS Fiscalization
Plugin URI: https://github.com/PsykeSPB/wp-mts-fiscalization
Description: WordPress plugin for check fiscalization with MTS cashbox
Version: 1.0.0
Author: Vitaly "PsykeSPB" Tikhoplav
Author URL: http://cv.psykespb.com
License: MIT
Text Domain: wp-mts-fiscalization
*/

/*
MIT License

Copyright (c) [year] [fullname]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

defined('ABSPATH') or die('not allowed');

if(!class_exists('MTSFiscalization')) {

	define('PLUGIN_NAME', 'MTS Fiscalization');
	define('PLUGIN_SLUG', 'mts_fisc_settings');
	define('PLUGIN_PATH', plugin_dir_path(__FILE__));

	define('API_ENDPOINT', 'https://ptsv2.com/t/x95pn-1563710775/post');

	class MTSFiscalization {
		public static function register() {
			// Add event handlers to globall WP hooks

			// Add admin settings page for plugin
			add_action('admin_menu', array('MTSFiscalization', 'add_admin_menu'));

			// Add settings to admin page
			add_action('admin_init', array('MTSFiscalization', 'add_admin_settings'));

			// Show order information on the thankyou page
			// should be used only in dev
			add_action('woocommerce_thankyou', array('MTSFiscalization', 'debug_order'));

			// Send order info to fiscalization api
			add_action('woocommerce_order_status_completed', array('MTSFiscalization', 'send_postback'));

			// Add actions to links in a plugin description
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('MTSFiscalization', 'addActionLinks'));
		}

		public static function add_admin_menu() {
			add_menu_page(
				PLUGIN_NAME . ' settings',
				PLUGIN_NAME,
				'administrator',
				PLUGIN_SLUG,
				function() {
					require_once PLUGIN_PATH . 'assets/admin.php';
				},
				'dashicons-admin-generic',
				110
			);
		}

		public static function add_admin_settings() {
			add_option('mts_fiscalization_organization_email', 'example@example.com');

			register_settings('mts_fiscalization_options_organization', 'mts-mts_fiscalization_organization_email', function($input) {
				return $input;
			});
		}

		public static function addActionLinks($links) {
			$plugin_links = array(
				'<a href="' . admin_url('options-general.php?page=' . PLUGIN_SLUG ) . '">Settings</a>',
			);

			return array_merge($links, $plugin_links);
		}

		public static function debug_order($order_id) {
			echo 'Order info:';
			echo '<pre>';
			print_r(MTSFiscalization::getPackageByOrderID($order_id));
			echo '</pre>';
		}

		public static function send_postback($order_id) {
			$url = API_ENDPOINT;
			$body = MTSFiscalization::getPackageByOrderID($order_id);

			$args = array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
				'body' => json_encode( $body ),
				'method' => 'POST',
				'data_format' => 'body',
			);

			wp_remote_post( $url, $args );
		}

		protected static function getPackageByOrderID($order_id) {
			$order = wc_get_order($order_id);

			$fisc = (object) [
				'external_id' => '1705292' . $order->get_date_paid()->getTimestamp(),
				'timestamp' => $order->get_date_paid()->format('Y.m.d H:i:s'),
				'receipt' => (object) [
					'client' => (object) [
						'email' => $order->get_billing_email(),
					],
				],
				'company' => (object) [
					'email' => '79219417383@litebox.ru', // Var from DB
					'inn' => '782000336124', // Var from DB
					'sno' => 'usn_income', // Система налогообложения. Возможные значения: «osn» – общая СН; «usn_income» – упрощенная СН (доходы); «usn_income_outcome» – упрощенная СН (доходы минус расходы); «envd» – единый налог на вмененный доход; «esn» – единый сельскохозяйственный налог; «patent» – патентная СН.
					'payment_address' => '194291, РОССИЯ, 78, Санкт-Петербург, Культуры, 6, корп. 1', // Var from DB
				],
				'items' => [],
				'items_pre' => $order->get_items(),
				'payments' => [
					(object) [
						'type' => 1,
						'sum' => floatval( $order->get_total() ),
					],
				],
				'total' => floatval( $order->get_total() ),
			];

			foreach ($order->get_items() as $item_id => $item_data) {
				array_push( $fisc->items, (object) [
					'name' => $item_data->get_name(),
					'price' => $item_data->get_total() / $item_data->get_quantity(),
					'quantity' => $item_data->get_quantity(),
					'sum' => floatval( $item_data->get_total() ),
					'measurement_unit' => 'шт',
					'payment_method' => 'full_prepayment',
					'payment_object' => 'service',
					'vat' => (object) [
						'type' => $item_data->get_tax_class(), // Get from item and reformat
						'sum' => $item_data->get_total_tax(), // Can get from item?
					],
					'prod' => $item_data->get_product(),
				]);
			}

			return $fisc;
		}		
	}

	// init plugin if its activated
	MTSFiscalization::register();

	// activation
	require_once PLUGIN_PATH . 'includes/wp-mts-fiscalization-activate.php';
	register_activation_hook(__FILE__, array('MTSFiscalizationActivate', 'activate'));

	// deactivation
	require_once PLUGIN_PATH . 'includes/wp-mts-fiscalization-deactivate.php';
	register_deactivation_hook(__FILE__, array('MTSFiscalizationDeactivate', 'deactivate'));
}




