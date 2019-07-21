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

add_action( 'before_woocommerce_pay', 'test_before_mts_postback' );

function test_before_mts_postback() {
	$url = 'https://ptsv2.com/t/x95pn-1563710775/post';
	$args = array(
		'timeout' => 45,
		'redirection' => 5,
		'http_version' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array( 'test' => 'before_woocommerce_pay' );
	);
	$response = wp_remote_post( $url, $args );

	// if( is_wp_error( $response ) ) {
	// 	$error_message = $response->get_error_message();
	// 	echo "Something gone wrong: $error_message";
	// } else {
	// 	echo "postback send: $response";
	// }

	echo $response;
}