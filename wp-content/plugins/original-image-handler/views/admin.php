<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package   original-image-handler
 * @author    Internetbureau Haboes <info@haboes.nl>
 * @license   GPL-2.0+
 * @link      http://www.haboes.nl
 * @copyright 2013 Internetbureau Haboes
 */

?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2 class="nav-tab-wrapper">
		<a href="#upload-settings" class="oih-tab nav-tab nav-tab-active"><?php _e( 'Upload Settings', $this->plugin_slug ); ?></a>
		<a href="#info-page" class="oih-tab nav-tab"><?php _e( 'Specs', $this->plugin_slug ); ?></a>
	</h2>

	<form method="post">
		<?php wp_nonce_field( 'save_upload_settings', $this->plugin_slug ) ?>
		<div id="upload-settings" class="oih-tab-section">
			<h3><?php _e( 'Upload Settings', $this->plugin_slug ); ?></h3>
			<table class="oih-settings-table">
				<tr>
					<td class="left"><?php _e( 'After resizing:', $this->plugin_slug ); ?></td>
					<td class="right">
						<label>
							<input type="checkbox" name="oih_remove_original_after_resizing" id="oih_remove_original_after_resizing"<?php if( $this->remove_original_after_resizing == true ) echo ' checked'; ?> />
							<?php _e( 'Remove original image after resizing', $this->plugin_slug ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td class="left aligntop"><?php _e( 'Convert BMP files:', $this->plugin_slug ); ?></td>
					<td class="right">
						<label>
							<input type="checkbox" name="oih_auto_convert_bmp" id="oih_auto_convert_bmp"<?php if( $this->auto_convert_bmp == true ) echo ' checked'; ?> />
							<?php _e( 'Automatically convert uploaded image to JPG format', $this->plugin_slug ); ?><br />
						</label>
					</td>
				</tr>
				<tr>
					<td class="left"><?php _e( 'Select JPG quality:', $this->plugin_slug ) ?></td>
					<td class="right"><div id="oih-slider-range"></div> <input type="text" name="oih_bmp_conversion_quality" size="1" id="oih_bmp_conversion_quality" value="<?php echo $this->bmp_conversion_quality; ?>" />% </td>
				</tr>

				<tr>
					<td class="left aligntop"><?php _e( 'Set max image size:', $this->plugin_slug ) ?></td>
					<td class="right">
						<input type="text" class="oih-small" name="oih_max_image_width" id="oih_max_image_width" value="<?php echo $this->max_image_width; ?>" placeholder="<?php _e( 'width', $this->plugin_slug ) ?>" /> px (<?php _e( 'width', $this->plugin_slug ) ?>)<br />
						<input type="text" class="oih-small" name="oih_max_image_height" id="oih_max_image_height" value="<?php echo $this->max_image_height; ?>" placeholder="<?php _e( 'height', $this->plugin_slug ) ?>" /> px (<?php _e( 'height', $this->plugin_slug ) ?>)
					</td>
				</tr>
				<tr>
					<td class="left"></td>
					<td class="right"><input type="submit" value="<?php _e( 'Save settings', $this->plugin_slug ); ?>" class="button button-primary button-large" /></td>
				</tr>
			</table>
		</div>

		<div id="info-page" class="oih-tab-section closed">
			<table class="oih-settings-table">
				<tr>
					<td><?php _e( 'Uploads total size on activation', $this->plugin_slug ); ?></td>
					<td><?php echo $this->format_size_units( $this->start_upload_size ); ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Saved disk space since activation', $this->plugin_slug ); ?></td>
					<td id="calculated_difference"><?php echo $this->calculate_difference(); ?> <a href="#" id="calculate_difference" class="button button-primary"><?php _e( 'Calculate', $this->plugin_slug ); ?></a></td>
				</tr>
				<tr>
					<td><?php _e( 'Current uploads directory size:', $this->plugin_slug ); ?></td>
					<td id="current_upload_size"><?php echo $this->format_size_units( $this->current_upload_size ); ?></td>
				</tr>
				<tr>
					<td class="left aligntop"><?php _e( 'Used image sizes:', $this->plugin_slug ); ?></td>
					<td class="right"><?php echo $this->image_sizes(); ?></td>
				</tr>
			</table>
		</div>
	</form>

</div>
