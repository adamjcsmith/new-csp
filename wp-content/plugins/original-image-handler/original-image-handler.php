<?php
/**
 * @package   original-image-handler
 * @author    Internetbureau Haboes <info@haboes.nl>
 * @license   GPL-2.0+
 * @link      http://www.haboes.nl
 * @copyright 2013 Internetbureau Haboes
 *
 * @wordpress-plugin
 * Plugin Name: Original Image Handler
 * Plugin URI:  http://www.haboes.nl
 * Description: After uploading an file it will resize and remove the original file from the server to save diskspace. It also converts BMP files to JPEG files to save diskspace.
 * Version:     1.0.0
 * Author:      Internetbureau Haboes B.V.
 * Author URI:  http://www.haboes.nl
 * Text Domain: oih
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-original-image-handler.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Original_Image_Handler', 'activate' ) );

Original_Image_Handler::get_instance();