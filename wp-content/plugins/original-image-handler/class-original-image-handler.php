<?php
/**
 * The is the plugin class for Original Image Handler.
 *
 * The Original Image Handler is build to resize the original uploaded image to a more useable size so your website stays fast.
 * It will delete the original file after resizing so it saves diskspace on your webhosting.
 *
 * @package   original-image-handler
 * @author    Internetbureau Haboes <wp@haboes.nl>
 * @license   GPL-2.0+
 * @link      http://www.haboes.nl
 * @copyright 2013 Internetbureau Haboes B.V.
 */

/**
 * Plugin class.
 *
 * @package original-image-handler
 * @author  Internetbureau Haboes <wp@haboes.nl>
 */
class Original_Image_Handler {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'oih';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Default value for max original image width
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $default_max_image_width = 1024;

	/**
	 * Default value for max original image height
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $default_max_image_height = 1024;

	/**
	 * Default value for auto convert bmp to jpg
	 *
	 * @since 1.0.0
	 * @var boolean
	 */
	protected $default_auto_convert_bmp = true;

	/**
	 * Default value for remove original after resizing
	 *
	 * @since 1.0.0
	 * @var boolean
	 */
	protected $default_remove_original_after_resizing = true;

	/**
	 * Default value for the BMP conversion quality
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $default_bmp_conversion_quality = 90;

	/**
	 * Default value for the uploads folder size on activation
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $default_start_upload_size = null;

	/**
	 * Start value for the upload folder size after activating the plugin
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	protected $default_uploaded_size = 0;

	/**
	 * Start value for the current upload folder
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	protected $default_current_upload_size = 0;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init',								                     array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu',						                     array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts',                                 array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts',                                 array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'post-upload-ui',                                        array( $this, 'media_settings_screen' ) );
		add_filter( 'wp_handle_upload',                                      array( $this, 'handle_upload' ) );

		// Actions to add the plugin to the bulk action dropdown on the media page
		add_action('load-upload.php',                                        array( $this, 'resize_images_bulk_action') );
		add_action('admin_notices',                                          array( $this, 'uploadpage_admin_notification' ) );

		//Actions to add an extra column to the upload page
		add_filter( 'manage_media_columns',                                  array( $this, 'uploadpage_column_width_head' ) );
		add_action( 'manage_media_custom_column',                            array( $this, 'column_width_height_value' ), 10, 2 );
		add_filter( 'manage_media_columns',                                  array( $this, 'uploadpage_column_height_head' ) );
		add_action( 'manage_media_custom_column',                            array( $this, 'uploadpage_add_column_filesize_field' ), 10, 2 );
		add_filter( 'manage_media_columns',                                  array( $this, 'uploadpage_column_filesize_head' ) );
		add_filter( 'wp_update_attachment_metadata',                         array( $this, 'update_uploaded_count' ), 10, 2 );

		add_action( 'wp_ajax_recalculate_difference',                        array( $this, 'recalculate_difference' ) );

		add_filter( 'media_row_actions',                                     array( $this, 'add_row_action' ), 10, 2 );

		add_action(	'admin_init',                                            array( $this, 'resize_image_row_action' ) ) ;
	}

	/**
	 * Update the uploaded file size to see the differences
	 *
	 * @param  mixed  $data    The post data of the uploaded image
	 * @param  int    $post_id The ID of the uploaded image
	 */
	public function update_uploaded_count( $post_id, $data ) {
		if ( ! isset( $_REQUEST['history'] ) ) {
			$new_files_size = 0;
			$uploads_dir = wp_upload_dir();
			if ( ! empty( $data['sizes'] ) && is_array( $data['sizes'] ) ) {
				foreach( $data['sizes'] as $size => $values ) {
					$file = trailingslashit( $uploads_dir['path'] ) . $values['file'];
					if ( file_exists( $file ) && 'image' == substr( $values['mime-type'], 0, strlen( 'image' ) ) ) {
						$new_files_size += filesize( $file );
					}
				}
			}
			$file = trailingslashit( $uploads_dir['basedir'] ) . $data['file'];
			if ( file_exists( $file ) ) {
				$new_files_size += filesize( $file );
			}

			update_option( 'oih_uploaded_size', $this->uploaded_size + $new_files_size );
		}
	}

	/**
	 * Adds the "resize" row-action to the media items on the upload page
	 *
	 * @since     1.0.0
	 * @param 	  mixed 	$param
	 * @param 	  mixed 	$post 	 The post element
	 * @return    array 	$actions An array containing all the row-action hyperlinks (with the added resize row-action hyperlink)
	 */
	public function add_row_action( $actions, $post ) {
		global $pagenow;

		if ( $pagenow === 'upload.php' && $this->is_resizable( $this->get_image_path_from_id( $post->ID ) ) ) {
			$actions['resize_image'] = "<a class='resize_image_row_action' href='" . admin_url( "upload.php?page=" . $this->plugin_slug . "&action=resize_image&post=" . $post->ID . "&_wpnonce=" . wp_create_nonce( 'resize_image' ) ) . "'>" . __( 'Resize', $this->plugin_slug ) . "</a>";
		}

		return $actions;
	}

	/**
	 * Processes the resize row-action
	 *
	 * @since     1.0.0
	 * @param 	  mixed 	$param
	 * @param 	  mixed 	$post 	 The post element
	 * @return    array 	$actions An array containing all the row-action hyperlinks (with the added resize row-action hyperlink)
	 */
	public function resize_image_row_action() {
		if ( isset ( $_GET['action'] ) && $_GET['action'] == 'resize_image' ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'resize_image' ) && isset( $_GET['post'] ) && is_numeric( $_GET['post'] )  ) {
				return;
			}

			$image = $this->get_image_properties( (int) $_GET['post'] );
			$this->process_image_resizing( (int)$_GET['post'] );
			wp_redirect( admin_url( 'upload.php?resize_imaged=true' ) );
			exit();
		}
	}

	/**
	 *
	 *
	 * Gets options from this plugin/class saved in the database.
	 *
	 * @param string $name
	 * @return mixed $value
	 * @since 	1.0.0
	 */
	public function __get( $name ) {
		$default = 'default_' . $name;
		if ( ( $value = get_option( 'oih_' . $name, 'NotExistingValue' ) ) === 'NotExistingValue' ) {
			if ( isset( $this->$default ) ) {
				return $this->$default;
			} else {
				return null;
			}
		}
		if ( is_bool( $this->$default ) ) {
			return (bool) $value;
		}
		return $value;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		$class = Original_Image_Handler::get_instance();

		if ( get_option( 'oih_auto_convert_bmp', 'NotExistingValue' ) == 'NotExistingValue' ) { //If the setting has not been set before
			update_option( 'oih_auto_convert_bmp', $class->default_auto_convert_bmp );
		}

		if ( get_option( 'oih_remove_original_after_resizing', 'NotExistingValue' ) == 'NotExistingValue' ) {
			update_option( 'oih_remove_original_after_resizing', $class->default_remove_original_after_resizing );
		}

		if ( get_option( 'oih_max_image_width', 'NotExistingValue' ) == 'NotExistingValue' ) {
			update_option( 'oih_max_image_width', $class->default_max_image_width );
		}

		if ( get_option( 'oih_max_image_height', 'NotExistingValue' ) == 'NotExistingValue' ) {
			update_option( 'oih_max_image_height', $class->default_max_image_height );
		}

		if ( get_option( 'oih_bmp_conversion_quality', 'NotExistingValue' ) == 'NotExistingValue' ) {
			update_option( 'oih_bmp_conversion_quality', $class->default_bmp_conversion_quality );
		}

		if ( get_option( 'oih_start_upload_size', 'NotExistingValue' ) == 'NotExistingValue' ) {
			$upload_path = wp_upload_dir();
			$output = $class->calculate_upload_dir( $upload_path['basedir'] );
			update_option( 'oih_start_upload_size', $output );
			update_option( 'oih_current_upload_size', $output );
		}
	}

	/**
	 * Add column to media overview table
	 *
	 * @since    1.0.0
	 *
	 * @param    mixed    $defaults 	Defaults
	 */
	public function uploadpage_column_width_head( $defaults ) {
		global $pagenow;
		if ( $pagenow === 'upload.php' && ( isset( $_GET['post_mime_type'] ) && $_GET['post_mime_type'] != 'image' ) ) {
			return $defaults;
		}
	    $defaults['image_width'] = __( 'Width', $this->plugin_slug );
	    return $defaults;
	}

	/**
	 * Add column to media overview table
	 *
	 * @since    1.0.0
	 *
	 * @param    mixed    $defaults 	Defaults
	 */
	public function uploadpage_column_height_head( $defaults ) {
		global $pagenow;
		if ( $pagenow === 'upload.php' && ( isset( $_GET['post_mime_type'] ) && $_GET['post_mime_type'] != 'image' ) ) {
			return $defaults;
		}
	    $defaults['image_height'] = __( 'Height', $this->plugin_slug );
	    return $defaults;
	}

	/**
	 * Add column to media overview table
	 *
	 * @since    1.0.0
	 *
	 * @param    mixed    $defaults 	Defaults
	 */
	public function uploadpage_column_filesize_head( $defaults ) {
	    $defaults['image_filesize'] = __( 'Filesize', $this->plugin_slug );
	    return $defaults;
	}

	/**
	 * Show the width or height of an image
	 *
	 * @since 1.0.0
	 * @param string $column_name Name of the displayed column
	 * @param int $image_ID ID of the attachment
	 */
	public function column_width_height_value( $column_name, $image_ID ) {
		global $pagenow;
		if ( $pagenow === 'upload.php' && ( isset( $_GET['post_mime_type'] ) && $_GET['post_mime_type'] != 'image' ) ) {
			return $defaults;
		}

		if ( in_array( $column_name, array( 'image_width', 'image_height' ) ) ) {
			$image_properties = $this->get_image_properties( $image_ID );
			if ( $image_properties !== false ) {
				if ( $image_properties['is_image'] ) {
					$error = '';
					if ( ( $column_name == 'image_width' && $image_properties['width'] > $this->max_image_width ) || ( $column_name == 'image_height' && $image_properties['height'] > $this->max_image_height ) ) {
						$error = ' error';
					}
					echo '<span class="column_image_properties' . $error . '">';
					echo ( $column_name == 'image_width' ? $image_properties['width'] : $image_properties['height'] ) . 'px';
					echo '</span>';
				} else {
					echo '<span class="column_image_properties">' . __( 'Not an image', $this->plugin_slug ) . '</span>';
				}
			} else {
				echo '<span class="column_image_properties">' . __( 'Can\'t read the image', $this->plugin_slug ) . '</span>';
			}
		}
	}

	/**
	 * Add column-field to media overview table
	 *
	 * @since    1.0.0
	 *
	 * @param    string   	$column_name 	Name of the column
	 * @param 	 integer 	$image_ID 		ID of the image
	 */
	public function uploadpage_add_column_filesize_field( $column_name, $image_ID ) {
	    if ( $column_name == 'image_filesize' ) {
	    	$image_properties = $this->get_image_properties( $image_ID );

	    	echo '<span class="column_image_properties">';
	    	if ( isset( $image_properties['size_int'] ) && $image_properties['size_int'] > 0 ) {
	    		echo $image_properties['size_str'];
	    		$image_properties = getimagesize( $image_properties['path'] );
	    		if ( $this->get_required_resize_memory( $image_properties ) > $this->get_wp_memory_limit_int() ) {
	    			echo '<span class="column_image_properties error">' . __( 'Too large to resize', $this->plugin_slug ) . '</span>';
	    		}
	    	} else {
	    		_e( 'File does not exist', $this->plugin_slug );
	    	}
	    	echo '</span>';

	    }
	}

	/**
	 * Format the filesize into a human-readable format
	 *
	 * @since 1.0.0
	 * @param int $bytes The bytes-integer
	 * @return string The formatted filesize
	 */
	private function format_size_units( $bytes ) {
        if ( $bytes >= 1073741824 ) {
            $bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
        } elseif ( $bytes >= 1048576 ) {
            $bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
        } elseif ( $bytes >= 1024 ) {
            $bytes = number_format( $bytes / 1024, 2 ) . ' KB';
        } elseif ( $bytes > 1 ) {
            $bytes = $bytes . ' bytes';
        } elseif ( $bytes == 1 ) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

	/**
	 * Perform the resize bulk action
	 *
	 * @since 1.0.0
	 */
	function resize_images_bulk_action() {
		global $pagenow;

		if ( 'upload.php' !== $pagenow ) {
			return;
		}

		// Return if top or bottom dropdown is not set to perform resize action
		if ( ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) && ( 'resize' === $_GET['action'] || 'resize' === $_GET['action2'] ) && check_admin_referer( 'bulk-media' ) ) {
			if ( is_array( $_GET['media'] )  && ! empty( $_GET['media'] ) ) {
				$resize_result = true;
				foreach( $_GET['media'] as $media_item ) {
					if ( $this->process_image_resizing( $media_item ) == false ) {
						$resize_result = false;
					}
				}

				//Remove dropdown actions from query
				$redirect_url = add_query_arg( array( 'images_resized' => $resize_result ), wp_get_referer() );
				$redirect_url = remove_query_arg( array( 'action', 'action2' ), $redirect_url );
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * Resize a single image for bulk purpose
	 *
	 * @since 1.0.0
	 * @param int $media_item_id
	 */
	private function process_image_resizing( $media_item_id = '' ) {
		if ( empty( $media_item_id ) || ! is_numeric( $media_item_id ) && 'image/' !== substr( get_post_mime_type( $media_item_id ), 0, strlen( 'image/' ) ) ) {
			return false;
		}

		$image 		= wp_get_attachment_image_src( $media_item_id, 'full_size' );
		$upload_dir = wp_upload_dir();
		$image_path = str_ireplace( $upload_dir['baseurl'], $upload_dir['basedir'], $image[0] );
		list( $image_width, $image_height ) = getimagesize( $image_path );
		if ( $image_width > $this->max_image_width || $image_height > $this->max_image_height ) {
			$result = $this->resize_image( $image_path, $this->max_image_width, $this->max_image_height, $this->bmp_conversion_quality, false, null, null, true );
			// Resize is done, now we need to edit the attachement meta
			$result = getimagesize( $result );
			$data = wp_get_attachment_metadata( $media_item_id );
			$data['width'] = $result[0];
			$data['height'] = $result[1];
			wp_update_attachment_metadata( $media_item_id, $data );
		}

		return true;
	}

	/**
	* Transforms an image URL into an internal image path
	*
	* @since 1.0.0
	* @param string $image_url The URL of the image
	*/
	private function get_image_path( $image_url ) {
		$upload_dir = wp_upload_dir();
		return str_ireplace( $upload_dir['baseurl'], $upload_dir['basedir'], $image_url );
	}

	/**
	* Transforms an image URL into an internal image path
	*
	* @since 1.0.0
	* @param int $image_id The id of the image
	* @param string Size of the image (Default: full_size)
	* @return String The image path of a specific image ID
	*/
	private function get_image_path_from_id( $image_id, $size = 'full_size' ) {
		$image = wp_get_attachment_image_src( $image_id, $size );
		if ( $image !== false) {
			return $this->get_image_path( $image[0] );
		} else {
			$file = get_attached_file( $image_id );
			return $file;
		}
	}

	/**
	* If a resize action has been performed, display an update/warning message (depending on success/failure)
	*
	* @since 1.0.0
	*/
	function uploadpage_admin_notification() {
		global $pagenow;

		if ( $pagenow !== 'upload.php' ) {
			return;
		}

		if ( isset( $_GET['resize_imaged'] ) ) {
			if ( (bool) $_GET['resize_imaged'] == true ) {
				echo '<div class="updated"><p>' . __( 'The image has been resized.', $this->plugin_slug ) . '</p></div>';
			} else {
				echo '<div class="error"><p>' . __( 'An error occured while trying to resize the image. Please try again.', $this->plugin_slug ) . '</p></div>';
			}
		}
	}

	/**
	 * Calculate the difference between the current uploads folder and on activation
	 *
	 * @since  1.0.0
	 * @param  int     $current  The size of the uploads directory
	 * @return String            The difference of the upload folder with the current size formated for human readability
	 */
	public function calculate_difference( $current = false ) {
		$upload_dir = wp_upload_dir();
		$start = $this->start_upload_size;
		if ( $current === false ) {
			if ( $this->currect_upload_size === 0 ) {
				$current = $this->calculate_upload_dir( $upload_dir['basedir'] );
			} else {
				$current = $this->current_upload_size;
			}
		}
		return $this->format_size_units( ( $start + $this->uploaded_size ) - $current );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix || $screen->id == 'upload' ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
			wp_enqueue_style( 'jquery-ui-smoothness', plugins_url( 'css/smoothness/jquery-ui-1.10.3.custom.min.css', __FILE__ ) );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		switch( $screen->id ) {
			case $this->plugin_screen_hook_suffix:
				wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'jquery-ui-slider' );
				break;
			case 'media':
				wp_enqueue_script( $this->plugin_slug . '-media-script', plugins_url( 'js/media-admin.js', __FILE__ ), array( 'jquery' ), $this->version );
				break;
			case 'upload':
				wp_enqueue_script( $this->plugin_slug . '-upload-script', plugins_url( 'js/upload-admin.js', __FILE__ ), array( 'jquery' ), $this->version, true );
				$l10n = array(
					'bulk_resize' => __( 'Resize Images', $this->plugin_slug ),
				);
				wp_localize_script( $this->plugin_slug . '-upload-script', 'upload_l10n', $l10n );
				break;
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_media_page(
			'Original Image Handler',
			__( 'Image Handler', $this->plugin_slug ),
			'upload_files',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Calculate the upload directory size.
	 *
	 * @since 1.0.0
	 * @param string $path
	 * @return int Total size of the upload directory
	 */
	private function calculate_upload_dir( $path, $save = false ) {
		$size = 0;
		$file_count = 0;

		if ( class_exists( 'DirectoryIterator' ) ) {
			foreach( new DirectoryIterator( $path ) as $file ) {
				if ( $file->isDir() && ! $file->isDot() ) {
					$size += $this->calculate_upload_dir( trailingslashit( $file->getPath() ) . $file->getFilename() );
				} else if ( $file->isFile() ) {
					$file_count++;
					$size += $file->getSize();
				}
			}
		} else {
			if ( false !== ( $handle = opendir( $path ) ) ) {
				while( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file != '.' && $file != '..' && substr( $file, 0, 1 ) != '.' ) {
						if ( is_file( trailingslashit( $path ) . $file ) !== false ) {
							$size += filesize( trailingslashit( $path ) . $file );
							$file_count++;
						} else if ( false !== is_dir( trailingslashit( $path ) . $file ) ) {
							$size += $this->calculate_upload_dir( trailingslashit( $path ) . $file );
						}
					}
				}
				closedir( $handle );
			}
		}

		if ( true === $save ) {
			update_option( 'oih_current_upload_size', $size );
		}
		return $size;
	}

	/**
	 * This will show the total size of the uploads directory in a size format.
	 *
	 * @since 1.0.0
	 * @return string The total size of the uploads directory
	 */
	public function uploads_dir_size() {
		$upload_path = wp_upload_dir();
		$output = $this->format_size_units( $this->calculate_upload_dir( $upload_path['basedir'] ) );
		return $output;
	}

	/**
	 * Get the default image size from WordPress
	 *
	 * @since  1.0.0
	 * @return array An array with all the default image sizes from WordPress
	 */
	private function get_default_image_sizes() {
		$defaults = array( 'thumbnail', 'medium', 'large' );
		$output = array();
		foreach( $defaults as $default ) {
			$output[ $default ]['width']   = get_option( $default . '_size_w' );
			$output[ $default ]['height']  = get_option( $default . '_size_h' );
			$output[ $default ]['crop']    = get_option( $default . '_crop', null );
			$output[ $default ]['default'] = true;
		}
		return $output;
	}


	/**
	 * Show the used/available image sizes to display images
	 *
	 * @since  1.0.0
	 * @return string Formatted image sizes used by WordPress and/or Theme/plugins
	 */
	public function image_sizes() {
		global $_wp_additional_image_sizes;

		$defaults = $this->get_default_image_sizes();
		$sizes = array_merge( $defaults, $_wp_additional_image_sizes );

		$output = '<ul class="image_sizes">';
		foreach( $sizes as $name => $settings ) {
			$output .= '<li>' . $name . ' ' . $settings['width'] . 'x' . $settings['height'] . ( isset( $settings['default'] ) && $settings['default'] ? ' ' . __( '(WP Default)', $this->plugin_slug ) : '' ) . '</li>';
		}
		return $output . '</ul>';
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		if ( isset( $_POST ) && isset( $_POST[ $this->plugin_slug ] ) ) {
			if ( $this->save_plugin_settings() ) {
				echo '<div id="message" class="updated"><p>';
					echo __( 'Settings have been saved.', $this->plugin_slug );
				echo '</p></div>';
			} else {
				echo '<div id="message" class="error"><p>';
					echo __( 'Error while saving the settings.', $this->plugin_slug );
				echo '</p></div>';
			}
		}
		include_once( 'views/admin.php' );
	}

	/**
	 * Function to save the plugin settings
	 *
	 * @since    1.0.0
	 * @return   boolean    If all options are saved it will return true
	 */
	private function save_plugin_settings() {
		if ( isset( $_POST[ $this->plugin_slug ] ) && wp_verify_nonce( $_POST[ $this->plugin_slug ], 'save_upload_settings' ) ) {
			if ( isset( $_POST['oih_remove_original_after_resizing'] ) ) {
				update_option( 'oih_remove_original_after_resizing', true );
			} else {
				update_option( 'oih_remove_original_after_resizing', false );
			}

			if ( isset( $_POST['oih_auto_convert_bmp'] ) ) {
				update_option( 'oih_auto_convert_bmp', true );
			} else {
				update_option( 'oih_auto_convert_bmp', false );
			}

			if ( isset( $_POST['oih_max_image_width'] ) && $_POST['oih_max_image_width'] >= 16 ) {
				update_option( 'oih_max_image_width', abs( $_POST['oih_max_image_width'] ) );
			} else {
				update_option( 'oih_max_image_width', $this->default_max_image_width );
			}

			if ( isset( $_POST['oih_max_image_height'] ) && $_POST['oih_max_image_height'] >= 16) {
				update_option( 'oih_max_image_height', abs( $_POST['oih_max_image_height'] ) );
			} else {
				update_option( 'oih_max_image_height', $this->default_max_image_height );
			}

			if ( isset( $_POST['oih_bmp_conversion_quality'] ) ) {
				update_option( 'oih_bmp_conversion_quality', abs( $_POST['oih_bmp_conversion_quality'] ) );
			} else {
				update_option( 'oih_bmp_conversion_quality', $this->default_bmp_conversion_quality );
			}

			return true;
		}
		return false;
	}

	/**
	 * After uploading, this will fire to resize the image or convert it from BMP to JPG
	 *
	 * @since  1.0.0
	 * @param  mixed $params
	 * @return mixed The parameters of the uploaded file
	 */
	public function handle_upload( $params ) {
		if ( 'image/' == substr( $params['type'], 0, strlen( 'image/' ) ) ) {
			if ( $params['type'] == 'image/bmp' && $this->auto_convert_bmp ) {
				$params = $this->create_jpg_from_bmp( $params );
			}

			if ( ! is_wp_error( $params ) && file_exists( $params['file'] ) && ( isset( $_REQUEST['remove_original'] ) ? $_REQUEST['remove_original'] : $this->remove_original_after_resizing ) && ! $this->is_custom_background_theme_upload() ) {
				list( $width, $height ) = getimagesize( $params['file'] );
				if ( $width > $this->max_image_width || $height > $this->max_image_height ) {
					list( $newWidth, $newHeight ) = wp_constrain_dimensions( $width, $height, $this->max_image_width, $this->max_image_height );
					$resizeResult = $this->resize_image( $params['file'], $newWidth, $newHeight, $this->bmp_conversion_quality );
					if ( ! is_wp_error( $resizeResult ) ) {
						$oldPath = $params['file'];
						unlink( $params['file'] );
						rename( $resizeResult, $oldPath );
					} else {
						$params = wp_handle_upload_error(
							$oldPath,
							sprintf( __( 'The image resize was unsuccessful for the following reason: %s. Please try again' ), $resizeResult->get_error_message() )
						);
					}
				}
			}
		}
		return $params;
	}

	/**
	 * Check if the upload is from a Theme custom upload. We do not want to resize these images.
	 *
	 * @since  1.0.0
	 * @return boolean if it is a Theme custom upload.
	 */
	private function is_custom_background_theme_upload() {
		if ( ( isset( $_REQUEST['post_data'] ) && isset( $_REQUEST['post_data']['context'] ) && $_REQUEST['post_data']['context'] === 'custom-background' ) || ( isset( $_REQUEST['page'] ) && 'custom-background' == $_REQUEST['page'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Replacement for deprecated resize_image function
	 *
	 * @since  1.0.0
	 * @param string $file Image file path.
	 * @param int $max_w Maximum width to resize to.
	 * @param int $max_h Maximum height to resize to.
	 * @param bool $crop Optional. Whether to crop image or resize.
	 * @param string $suffix Optional. File suffix.
	 * @param string $dest_path Optional. New image file path.
	 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
	 * @return mixed WP_Error on failure. String with new destination path.
	 */
	private function resize_image( $file, $max_w, $max_h, $jpeg_quality = 90, $crop = false, $suffix = null, $dest_path = null, $override_original = false ) {

		if ( function_exists( 'wp_get_image_editor' ) ) {
			
			// WP 3.5 and up use the image editor
			$editor = wp_get_image_editor( $file );
			if ( is_wp_error( $editor ) ) {
				return $editor;
			}
			$editor->set_quality( $jpeg_quality );
			$resized = $editor->resize( $max_w, $max_h, $crop );
			if ( is_wp_error( $resized ) ) {
				return $resized;
			}

			if ( $override_original === false ) {
				$dest_file = $editor->generate_filename( $suffix, $dest_path );
			} else {
				$dest_file = $file;
			}
			$saved = $editor->save( $dest_file );

			if ( is_wp_error( $saved ) ) {
				return $saved;
			}

			return $dest_file;
		} else {
			
			// wordpress prior to 3.5 uses the old resize_image function
			return resize_image( $file, $max_w, $max_h, $crop, $suffix, $dest_path, $jpeg_quality );
		}
	}

	/**
	 * create_jpg_from_bmp Create a JPG image from a BMP file
	 *
	 * @since 	1.0.0
	 * @param 	Mixed 	$params
	 * @return 	Array 	Array containing settings from newly created image
	 */
	private function create_jpg_from_bmp( $params ) {
		$bmp = $this->image_create_from_bmp( $params['file'] );
		$oldPath = $params['file'];

		$uploads = wp_upload_dir();
		$oldFileName = basename( $params['file'] );
		$newFileName = basename( str_ireplace( ".bmp", ".jpg", $oldFileName ) );
		$newFileName = wp_unique_filename( $uploads['path'], $newFileName );

		// Remove old bmp file
		unlink( $params['file'] );
		if ( imagejpeg( $bmp, trailingslashit( $uploads['path'] ) . $newFileName, $this->bmp_conversion_quality ) ) {
			$params['file'] = $uploads['path'] . '/' . $newFileName;
			$params['url'] = $uploads['url'] . '/' . $newFileName;
			$params['type'] = 'image/jpeg';
			return $params;
		}
		return wp_handle_upload_error( $oldPath, __( 'We couldn\'t convert the BMP to JPG. Please try again. If you continue to see this error you may need tot disable the BMP-To_JPG feauture.', $this->plugin_slug ) );
	}

	/**
	 * can_be_resized Checks whether a local image can be resized
	 *
	 * @since 	1.0.0
	 * @param 	String	image_path 	The local image path
	 * @return 	Boolean True if image can be resized, false if the image cannot (or does not need to be) be resized
	 */
	private function is_resizable( $image_path = '' ) {

		//Check if image exists local
		if ( empty( $image_path ) || substr( $image_path, 0, 4 ) == 'http' || ! file_exists( $image_path ) ) {
			return false;
		}

		//Check if the given file is indeed an image
		$image_properties = getimagesize( $image_path );
		if ( substr( $image_properties['mime'], 0, strlen( 'image/' ) ) !== 'image/' ) {
			return false;
		}

		//Check if sufficient memory is available in order to perform the resize process
		if ( $this->get_required_resize_memory( $image_properties ) > $this->get_wp_memory_limit_int() ) {
			return false;
		}

		//Check if image properties exceed the required maximum before resizing
		if ( $image_properties[0] <= $this->max_image_width && $image_properties[1] <= $this->max_image_height ) {
			return false;
		}

		return true;
	}

	/**
	 * get_wp_memory_limit_int	Get the WP memory limit, returned as an integer value in bytes
	 *
	 * @since 	1.0.0
	 *
	 * @return 	Int 	The WP_MEMORY_LIMIT value in bytes, transformed into an integer
	 */
	private function get_wp_memory_limit_int() {
		if ( preg_match( '/^(\d+)(.)$/', WP_MEMORY_LIMIT, $matches ) ) {
			if ( $matches[2] == 'M' ) {
				$memory_limit = $matches[1] * 1024 * 1024;
			} else if ($matches[2] == 'K') {
				$memory_limit = $matches[1] * 1024;
			}
			return $memory_limit;
		} else {
			return false;
		}
	}

	/**
	 * get_required_resize_memory	Get the required memory limit for resizing the image
	 *
	 * @since 	1.0.0
	 * @param   Mixed 	$image_properties  The image properties resulting from a getimagesize()
	 * @return 	Int 	The minimum required memory in order to be able to perform the resize action
	 */
	private function get_required_resize_memory( $file_properties ) {
		return ceil( $file_properties[0] * $file_properties[1] * 3.5 ) + 15104 ; //Each pixel requires about 3.5 bytes, + 15MB buffer
	}

	/**
	 * get_image_properties Returns the properties of an image
	 *
	 * @since 	1.0.0
	 * @param 	Mixed	$image May be a STRING with the local image path, or an INT with the image id
	 * @return 	Mixed 	An array with the image specifications, false if the image properties cannot be determined
	 */
	private function get_image_properties( $image = '' ) {
		if ( is_numeric( $image ) ) {
			$image = $this->get_image_path_from_id( $image );
		}

		if ( empty( $image ) || substr( $image, 0, 4 ) == 'http' || ! file_exists( $image ) ) {
			return false;
		}

		$file_size = filesize( $image );
		if ( $file_size > 0 ) {
			$image_properties 	= getimagesize( $image );

			$retval['path']		= $image;
			$retval['width']	= $image_properties[0];
			$retval['height'] 	= $image_properties[1];
			$retval['mime']		= $image_properties['mime'];
			$retval['is_image']	= ( substr( $retval['mime'], 0, strlen( 'image/' ) ) === 'image/' ) ? true : false;
			$retval['size_int']	= $file_size;
			$retval['size_str']	= $this->format_size_units( $file_size );

			return $retval;
		} else {
			return false;
		}
	}

	/**
	 * image_create_from_bmp converts a bmp to an image resource
	 *
	 * @since 1.0.0
	 * @param  String The image file name
	 * @return Image Returns the created image
	 */
	private function image_create_from_bmp( $filename ) {
		
		// version 1.00
		if ( ! ( $fh = fopen( $filename, 'rb' ) ) ) {
			trigger_error( sprintf( __( 'imagecreatefrombmp: Can not open %s!', $this->plugin_slug ), $filename ), E_USER_WARNING );
			return false;
		}
		
		// read file header
		$meta = unpack( 'vtype/Vfilesize/Vreserved/Voffset', fread( $fh, 14 ) );
		
		// check for bitmap
		if ( $meta['type'] != 19778 ) {
			trigger_error( sprintf( __( 'imagecreatefrombmp: %s is not a bitmap!', $this->plugin_slug ), $filename ), E_USER_WARNING );
			return false;
		}
		
		// read image header
		$meta += unpack( 'Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread( $fh, 40 ) );
		
		// read additional 16bit header
		if ( $meta['bits'] == 16 ) {
			$meta += unpack( 'VrMask/VgMask/VbMask', fread( $fh, 12 ) );
		}
		
		// set bytes and padding
		$meta['bytes'] = $meta['bits'] / 8;
		$meta['decal'] = 4 - ( 4 * ( ( $meta['width'] * $meta['bytes'] / 4 ) - floor( $meta['width'] * $meta['bytes'] / 4 ) ) );
		if ( $meta['decal'] == 4 ) {
			$meta['decal'] = 0;
		}
		
		// obtain imagesize
		if ( $meta['imagesize'] < 1 ) {
			$meta['imagesize'] = $meta['filesize'] - $meta['offset'];
			
			// in rare cases filesize is equal to offset so we need to read physical size
			if ( $meta['imagesize'] < 1 ) {
				$meta['imagesize'] = @filesize( $filename ) - $meta['offset'];
				if ( $meta['imagesize'] < 1 ) {
					trigger_error( sprintf( __( 'imagecreatefrombmp: Can not obtain filesize of %s !', $this->plugin_slug ), $filename ), E_USER_WARNING );
					return false;
				}
			}
		}
		
		// calculate colors
		$meta['colors'] = ! $meta['colors'] ? pow( 2, $meta['bits'] ) : $meta['colors'];
		
		// read color palette
		$palette = array();
		if ( $meta['bits'] < 16 ) {
			$palette = unpack( 'l' . $meta['colors'], fread( $fh, $meta['colors'] * 4 ) );
			
			// in rare cases the color value is signed
			if ( $palette[1] < 0 ) {
				foreach( $palette as $i => $color ) {
					$palette[ $i ] = $color + 16777216;
				}
			}
		}
		
		// create gd image
		$im = imagecreatetruecolor( $meta['width'], $meta['height'] );
		$data = fread( $fh, $meta['imagesize'] );
		$p = 0;
		$vide = chr(0);
		$y = $meta['height'] - 1;
		$error = 'imagecreatefrombmp: ' . $filename . ' has not enough data!';
		
		// loop through the image data beginning with the lower left corner
		while( $y >= 0 ) {
			$x = 0;
			while( $x < $meta['width'] ) {
				switch( $meta['bits'] ) {
					case 32:
					case 24:
						if ( ! ( $part = substr( $data, $p, 3 ) ) ) {
							trigger_error( $error, E_USER_WARNING );
							return $im;
						}
						$color = unpack( 'V', $part . $vide );
						break;
					case 16:
						if ( ! ( $part = substr( $data, $p, 2 ) ) ) {
							trigger_error( $error, E_USER_WARNING );
							return $im;
						}
						$color = unpack( 'v', $part );
						$color[1] = ( ( $color[1] & 0xf800 ) >> 8 ) * 65536 + ( ( $color[1] & 0x07e0 ) >> 3 ) * 256 + ( ( $color[1] & 0x001f ) << 3 );
						break;
					case 8:
						$color = unpack( 'n', $vide . substr( $data, $p, 1 ) );
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					case 4:
						$color = unpack( 'n', $vide . substr( $data, floor( $p ), 1 ) );
						$color[1] = ( $p * 2 ) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					case 1:
						$color = unpack( 'n', $vide . substr( $data, floor( $p ), 1 ) );
						switch( ( $p * 8 ) % 8 ) {
							case 0:
								$color[1] = $color[1] >> 7;
								break;
							case 1:
								$color[1] = ( $color[1] & 0x40 ) >> 6;
								break;
							case 2:
								$color[1] = ( $color[1] & 0x20 ) >> 5;
								break;
							case 3:
								$color[1] = ( $color[1] & 0x10 ) >> 4;
								break;
							case 4:
								$color[1] = ( $color[1] & 0x8 ) >> 3;
								break;
							case 5:
								$color[1] = ( $color[1] & 0x4 ) >> 2;
								break;
							case 6:
								$color[1] = ( $color[1] & 0x2 ) >> 1;
								break;
							case 7:
								$color[1] = ( $color[1] & 0x1 );
								break;
						}
						$color[1] = $palette[ $color[1] + 1 ];
						break;
					default:
						trigger_error( sprintf( __( 'imagecreatefrombmp: %s has %d bits and this is not supported!', $this->plugin_slug ), $filename, $meta['bits'] ), E_USER_WARNING );
						return false;
				}
				imagesetpixel( $im, $x, $y, $color[1] );
				$x++;
				$p += $meta['bytes'];
			}
			$y--;
			$p += $meta['decal'];
		}
		fclose( $fh );
		return $im;
	}

	/**
	 * Recalculates the uploads directory
	 *
	 * @return json The current size and the difference
	 */
	public function recalculate_difference() {
		$response = array();
		$upload_dirs = wp_upload_dir();
		$response['current_size'] = $this->format_size_units( $this->calculate_upload_dir( $upload_dirs['basedir'], true ) );
		$response['difference']   = $this->calculate_difference();
		echo json_encode( $response );
		exit();
	}

	/**
	 * Show the option on the upload screen
	 *
	 * @since 1.0.0
	 */
	public function media_settings_screen() {
		global $pagenow;
		if ( $pagenow !== 'media-new.php' ) {
			return;
		}

		echo '<br />' . PHP_EOL;
		echo '<p id="oih_upload_settings">';
			echo '<input type="checkbox" value="1" ' . ( $this->remove_original_after_resizing ? 'checked="checked" ' : '' ) . 'id="oih_rem_orig" /> <label for="oih_rem_orig">' . __( 'Remove original image after resizing', $this->plugin_slug ) . '</label>';
		echo '</p>';
	}
}