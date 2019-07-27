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

	//define('API_ENDPOINT', 'https://in.litebox.ru/fiscalization/v1/shops/43/sell');
	define('API_ENDPOINT', 'https://ptsv2.com/t/x95pn-1563710775/post');

	class MTSFiscalization {
		public static function register() {
			// Add admin settings page for plugin
			add_action('admin_menu', array('MTSFiscalization', 'add_admin_menu'));

			// Add settings to admin page
			add_action('admin_init', array('MTSFiscalization', 'add_admin_settings'));

			// Add actions to links in a plugin description
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('MTSFiscalization', 'addActionLinks'));

			// Send order info to fiscalization api
			add_action('woocommerce_order_status_completed', array('MTSFiscalization', 'send_postback'));
			add_action('woocommerce_thankyou', array('MTSFiscalization', 'send_postback'));
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
			register_setting('mts_fiscalization', 'mts_fiscalization_email');
			register_setting('mts_fiscalization', 'mts_fiscalization_inn');
			register_setting('mts_fiscalization', 'mts_fiscalization_address');
			register_setting('mts_fiscalization', 'mts_fiscalization_tax_system');
			register_setting('mts_fiscalization', 'mts_fiscalization_api_token');
		}

		public static function addActionLinks($links) {
			$plugin_links = array(
				'<a href="' . admin_url('options-general.php?page=' . PLUGIN_SLUG ) . '">Settings</a>',
			);

			return array_merge($links, $plugin_links);
		}

		public static function send_postback($order_id) {
			$order = wc_get_order($order_id);
			$body = json_encode(MTSFiscalization::getPackagedOrder($order), JSON_UNESCAPED_UNICODE);
			$body = preg_replace('/"(\d+)\.(\d{2})"/', '$1.$2', $body);

			echo 'Request:';
			echo '<pre>';
			print_r($body);
			echo '</pre>';

			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'Authorization' => 'token ' . get_option('mts_fiscalization_api_token'),
				),
				'body' => $body,
				'method' => 'POST',
				'data_format' => 'body',
			);

			$response = wp_remote_post( API_ENDPOINT, $args );

			echo 'Response:';
			echo '<pre>';
			print_r($response);
			echo '</pre>';

			if(is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$order->add_order_note("Ошибка фискализации:\n$error_message");
			} else {
				if($response->response->code > 299) {
					$error_message = $response->body;
					$order->add_order_note("Ошибка фискализации:\n$error_message");
				} else {
					$order->add_order_note('Успешно фискализирован');
				}
			}
		}

		protected static function getPackagedOrder($order) {
			$fisc = (object) [
				'external_id' => '1705292' . $order->get_date_paid()->getTimestamp(),
				'timestamp' => $order->get_date_paid()->format('Y.m.d H:i:s'),
				'receipt' => (object) [
					'client' => (object) [
						'email' => $order->get_billing_email(),
					],
				],
				'company' => (object) [
					'email' => get_option('mts_fiscalization_email'),
					'inn' => get_option('mts_fiscalization_inn'),
					'sno' => get_option('mts_fiscalization_tax_system'),
					'payment_address' => get_option('mts_fiscalization_address'),
				],
				'items' => [],
				'payments' => [
					(object) [
						'type' => 1,
						'sum' => number_format($order->get_total(), 2),
					],
				],
				'total' => number_format($order->get_total(), 2),
			];

			foreach ($order->get_items() as $item_id => $item_data) {
				array_push( $fisc->items, (object) [
					'name' => $item_data->get_name(),
					'price' => number_format($item_data->get_total() / $item_data->get_quantity(), 2),
					'quantity' => $item_data->get_quantity(),
					'sum' => number_format( $item_data->get_total(), 2),
					'measurement_unit' => 'шт',
					'payment_method' => 'full_prepayment',
					'payment_object' => 'service',
					'vat' => (object) [
						'type' => 'none',
						'sum' => 0,
					],
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




