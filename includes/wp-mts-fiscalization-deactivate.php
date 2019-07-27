<?php
/**
 * @package wp-mts-fiscalization
 */

class MTSFiscalizationDeactivate {
	public static function deactivate() {
		// Reset read write rules and generate new for added custom posttypes
		flush_rewrite_rules();
	}
}