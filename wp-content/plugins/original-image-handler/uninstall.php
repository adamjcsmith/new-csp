<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   original-image-handler
 * @author    Internetbureau Haboes <wp@haboes.nl>
 * @license   GPL-2.0+
 * @link      http://www.haboes.nl
 * @copyright 2013 Internetbureau Haboes
 */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

class Uninstall_Original_Image_Handler {

	public function __construct() {
		$this->delete_options();
	}

	/**
	 * Delete all the settings used in this plugin
	 *
	 * @since    1.0.0
	 */
	private function delete_options() {
		delete_option( 'oih_remove_original_after_resizing' );
		delete_option( 'oih_max_image_height' );
		delete_option( 'oih_max_image_width' );
		delete_option( 'oih_auto_convert_bmp' );
	}
}

new Uninstall_Original_Image_Handler();