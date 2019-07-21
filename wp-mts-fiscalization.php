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

function mts_prepare_fiscalization_package_from_order( $order_id ) {
	$order = wc_get_order($order_id);

	$fisc = (object) [
		'external_id' => '17052917561851307',
		'external_od' => '1705291' . $order->get_date_paid()->getTimestamp(),
		'timestamp' => $order->get_date_paid()->format('Y-m-d H:i:s'),
		'date_complete' => $order->get_date_paid(),
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
		'payments' => [
			(object) [
				'type' => 1,
				'sum' => $order->get_total(),
			],
		],
		'total' => $order->get_total(),
	];

	foreach ($order->get_items() as $item) {
		array_push( $fisc->items_pre, $item);
	}

	return $fisc;
}

add_action( 'woocommerce_order_status_completed', 'test_mts_postback' );

// test without payment
add_action( 'woocommerce_thankyou', 'mts_debug_order' );

function test_mts_postback( $order_id ) {
	$url = 'https://ptsv2.com/t/x95pn-1563710775/post';
	$body = mts_prepare_fiscalization_package_from_order( $order_id );

	$args = array(
		'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
		'body' => json_encode( $body ),
		'method' => 'POST',
		'data_format' => 'body',
	);

	wp_remote_post( $url, $args );
}

function mts_debug_order( $order_id ) {
	echo 'Order info:';
	echo '<pre>';
	print_r( mts_prepare_fiscalization_package_from_order( $order_id ) );
	echo '</pre>';
}

