<?php
/**
 * @package wp-mts-fiscalization
 */

class MTSFiscalizationActivate {
	public static function activate() {
		// Reset read write rules and generate new for added custom posttypes
		flush_rewrite_rules();
	}
}