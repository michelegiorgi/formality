<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the inputs for file fields.
 *
 * @since 3.0
 */
class File extends Field {
	/**
	 * Holds the allowed file type.
	 *
	 * @since 3.0
	 * @var boolean
	 */
	protected $file_type = 'all';

	/**
	 * Whenever a new file field is created, this index will be used
	 * in order to have various fields have various nonces, hooks and checks.
	 *
	 * @since 3.0
	 * @var int
	 */
	static $last_index = 0;

	/**
	 * Holds the actual index of the file field, see above.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $index = -1;

	/**
	 * Indicates the expected format when the_field() is used.
	 *
	 * @since 3.0
	 */
	protected $output_type = 'link';

	/**
	 * Indicates the if the field should use a normal input or a basic one.
	 *
	 * The basic input is not using the media uploader and is better suitable for the front-end.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $basic = false;

	/**
	 * Holds the maximum upload size in bytes.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $max_filesize = 0;

	/**
	 * Indicates if the field works with multiple files or not.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Adds additional actions and hooks.
	 *
	 * @since 3.0
	 */
	protected function __constructed() {
		$this->index = ++self::$last_index;

		if( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) {
			$this->use_basic_uploader();
		}
	}

	/**
	 * Enqueues the needed scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( $this->basic ) {
			wp_enqueue_script( 'uf-field-file-uploader' );
			wp_plupload_default_settings();
			Template::add( 'file-uploader', 'field/file-uploader' );
		} else {
			wp_enqueue_media();
			wp_enqueue_script( 'uf-field-file' );
			Template::add( 'overlay-wrapper', 'overlay-wrapper' );
			Template::add( 'overlay-alert',   'overlay-alert' );
		}

		# Localize strings
		ultimate_fields()
			->localize( 'file-select',        _x( 'Select', 'file', 'ultimate-fields' ) )
			->localize( 'file-select-other',  __( 'Select another file', 'ultimate-fields' ) )
			->localize( 'file-edit',          __( 'Edit', 'ultimate-fields' ) )
			->localize( 'file-remove',        __( 'Remove', 'ultimate-fields' ) )
			->localize( 'file-save',          __( 'Save & Use', 'ultimate-fields' ) )
			->localize( 'file-or',            __( 'OR', 'ultimate-fields' ) );

		# Add the necessary templates
		Template::add( 'file', 'field/file' );
	}

	/**
	 * Sets the required file type.
	 *
	 * @since 3.0
	 *
	 * @param mixed $type A single string with type(s) or extionsion(s). Can be comma-separated.
	 * @return Ultimate_Fields\Field\File
	 */
	public function set_file_type( $type ) {
		/**
		 * Allows the allowed file types for the field.
		 *
		 * @since 3.0
		 *
		 * @param string $type The type that is being set.
		 * @param Ultimate_Fields\Field\File $field The instance of the field.
		 * @return string
		 */
		$this->file_type = apply_filters( 'uf.field.file.file_type', $type, $this );

		return $this;
	}

	/**
	 * Returns the currently supported post type(s).
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_file_type() {
		return $this->file_type;
	}

	/**
	 * Modifies the data, which gets sent to the JS for the fields' settings
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'file_type' ] = $this->file_type;
		$settings[ 'basic' ]     = $this->basic;
		$settings[ 'nonce' ]     = wp_create_nonce( $this->get_nonce_action() );

		return $settings;
	}

	/**
	 * Exports the fields' data.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		static $cached;

		# Ensure that the cache is an array
		if( is_null( $cached ) ) {
			$cached = array();
		}

		# This will hold all files
		$data   = array();
		$value  = $this->get_value();
		$needed = array();

		# Grab the default value
		if( ! $value ) {
			$value = $this->default_value;
		}

		# Check if the field can prepare data
		if( ! empty( $value ) ) {
			$ids    = $this->extract_ids_from_value( $value );
			$needed = array();


			# Group into cached, non-existing and needed
			foreach( $ids as $id ) {
				if( isset( $cached[ $id ] ) ) {
					if( $cached[ $id ] )
						$data[] = $cached[ $id ];
				} else {
					$needed[] = $id;
				}
			}
		}

		# Load additional attachments
		if( ! empty( $needed ) ) {
			# Load new posts
			$loaded = array();
			$posts  = get_posts( array(
				'post_type'      => 'attachment',
				'post__in'       => $needed,
				'posts_per_page' => -1
			));

			# Turn to IDs
			foreach( $posts as $p )
				$loaded[ $p->ID ] = $p;

			# Fetch
			foreach( $needed as $id ) {
				if( isset( $loaded[ $id ] ) ) {
					$exported = $this->generate_preview_data_for_id( $id );
					$data[] = $cached[ $id ] = $exported;
				} else {
					$cached[ $id ] = false;
				}
			}
		}

		# Prepare the export
		$result = array(
			$this->name               => $this->generate_value_from_attachments( $data ),
			$this->name . '_prepared' => $data
		);

		return $result;
	}

	/**
	 * Based on the current value of the field this will convert an array of
	 * JS-prepared attachments into a value that should be sent to JavaScript.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $items The items that are preloaded for the field.
	 * @return mixed
	 */
	protected function generate_value_from_attachments( $items ) {
		if( $this->multiple ) {
			return wp_list_pluck( $items, 'id' );
		} else {
			$attachment = array_shift( $items );
			return $attachment ? $attachment[ 'id' ] : false;
		}
	}

	/**
	 * Filters out the IDs from a field's value.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value that is being prepared.
	 * @return int[]
	 */
	public function extract_ids_from_value( $value ) {
		return array_map( 'intval', array( $value ) );
	}

	/**
	 * Returns the action for a nonce field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'uf_file_preview_' . $this->index;
	}

	/**
	 * Generates the preview data for a specific ID.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the attachment.
	 * @return mixed[]
	 */
	public function generate_preview_data_for_id( $id ) {
		if( 'attachment' == get_post_type( $id ) ) {
			return wp_prepare_attachment_for_js( $id );
		} else {
			return array(
				'missing' => true
			);
		}
	}

	/**
	 * Performs AJAX.
	 *
	 * In this AJAX action, file data will be prepared in case it's not already cached in JS.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		if( 'file_preview_' . $this->name == $action ) {
			$this->generate_ajax_data();
		} elseif( $this->basic && 'file_upload_' . $this->name == $action ) {
			$this->handle_upload();
		}
	}

	/**
	 * Outputs prepared data for a file for JS.
	 *
	 * @since 3.0
	 */
	protected function generate_ajax_data() {
		if(
			! isset( $_POST[ 'file_ids' ] )
			|| ! isset( $_POST[ 'nonce' ] )
		 	|| ! wp_verify_nonce( $_POST[ 'nonce' ], $this->get_nonce_action() )
		 	|| ! is_array( $_POST[ 'file_ids' ] )
		) {
			exit;
		}

		$data = array();

		foreach( $_POST[ 'file_ids' ] as $id ) {
			$id   = intval( $id );
			$file = $this->generate_preview_data_for_id( $id );
			$file[ 'id' ] = $id;
			$data[] = $file;
		}

		echo json_encode( $data ); exit;
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		$this->proxy_data_to_setters( $data, array(
			'file_output_type' => 'set_output_type'
		));

		if( File::class == get_class( $this ) && isset( $data[ 'allowed_filetype' ] ) ) {
			$this->set_file_type( $data[ 'allowed_filetype' ] );
		}
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$this->export_properties( $settings, array(
			'file_type'   => array( 'allowed_filetype', 'all' ),
			'output_type' => array( 'file_output_type', 'link' )
		));

		return $settings;
	}

	/**
	 * Changes the output type of the field.
	 *
	 * @since 3.0
	 *
	 * @param string $output_type The needed type ('link', 'url', 'id').
	 * @return Ultimate_Fields\Field\File
	 */
	public function set_output_type( $type ) {
		$this->output_type = $type;

		return $this;
	}

	/**
	 * Forces the field into basic upload mode.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field\File
	 */
	public function use_basic_uploader() {
		$this->basic = true;

		return $this;
	}

	/**
	 * Handles file uploads in basic mode.
	 *
	 * @since 3.0
	 *
	 * @param string $url The URL to use.
	 */
	protected function handle_upload() {
		# Check if there is something to handle
		if( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] || empty( $_FILES ) || ! isset( $_FILES[ 'uf_file' ] ) ){
			return;
		}

		# Check for a nonce
		if(
			! isset( $_POST[ '_wpnonce' ] )
			|| ! wp_verify_nonce( $_POST[ '_wpnonce' ], $this->get_nonce_action() )
		) {
			$message = __( 'Your session has expired. Please refresh the page and try again!', 'ultimate-fields' );

			die( json_encode( array(
				'error' => $message
			)));
		}

		# Include functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		# Check for a filetype
		$allowed_types = get_allowed_mime_types( is_user_logged_in() ? wp_get_current_user() : false );
		$filetype      = wp_check_filetype( basename( $_FILES[ 'uf_file' ][ 'name' ] ), null );
		$mime_type     = $filetype[ 'type' ];

		# If there is a local file type, filter out the allowed mimes based on it
		if( $this->get_file_type() && 'all' !== $this->get_file_type() ) {
			$filtered = array();
			$local    = (array) $this->get_file_type();

			foreach( $local as $l ) {
				$exact = false !== strpos( $l, '/' );

				foreach( $allowed_types as $ext => $mime ) {
					if( $exact && $mime === $l ) {
						$filtered[ $ext ] = $mime;
					}

					if( ! $exact && 0 === stripos( $mime, $l . '/' ) ) {
						$filtered[ $ext ] = $mime;
					}
				}
			}

			$allowed_types = $filtered;
		}

		if( ! in_array( $mime_type, $allowed_types ) ) {
			$message = __( 'You cannot upload this file because of security reasons.', 'ultimate-fields' );

			die( json_encode( array(
				'error' => $message
			)));
		}

		# Check the filesize if needed
		$size = $_FILES[ 'uf_file' ][ 'size' ];
		$max  = $this->max_filesize > 0
			? min( $this->max_filesize, wp_max_upload_size() )
			: wp_max_upload_size();

		if( $size > $max ) {
			$message = __( 'The file exceeds the maximum allowed size of %s.', 'ultimate-fields' );
			$message = sprintf( $message, size_format( $max ) );

			die( json_encode( array(
				'error' => $message
			)));
		}

		# Process
		$file      = $_FILES[ 'uf_file' ];
		$overrides = array( 'test_form' => false );
		$moved     = wp_handle_upload( $file, $overrides );

		if( isset( $moved[ 'error' ] ) ) {
			die( json_encode( array(
				'success' => false,
				'message' => $moved[ 'error' ]
			)));
		}

		# Prepare attachment data
		$upload_dir = wp_upload_dir();
		$filename   = $moved[ 'file' ];
		$filetype   = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'guid'           => $upload_dir[ 'url' ] . '/' . basename( $filename ),
			'post_mime_type' => $filetype[ 'type' ],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'publish'
		);

		# Insert the attachment.
		$attach_id   = wp_insert_attachment( $attachment, $filename );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		# Read out the data
		die( json_encode( array(
			'success' => true,
			'data'    => wp_prepare_attachment_for_js( $attach_id )
		)));
	}

	/**
	 * Returns the dimentions of a certain image size by its name.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the size.
	 * @return int[]
	 */
	public function get_image_size_dimentions( $name ) {
		global $_wp_additional_image_sizes;

		$sizes = array(
			'width'  => false,
			'height' => false
		);

		if ( in_array( $name, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes['width']  = get_option( "{$name}_size_w" );
			$sizes['height'] = get_option( "{$name}_size_h" );
		} elseif ( isset( $_wp_additional_image_sizes ) ) {
			$sizes = array(
				'width'  => $_wp_additional_image_sizes['width'],
				'height' => $_wp_additional_image_sizes['height']
			);
		}

		return $sizes;
	}

	/**
	 * When the data API is being used, this method will "handle" a value.
	 *
	 * @param  mixed       $value  The raw value.
	 * @param  Data_Source $source The data source that the value is associated with.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value   = parent::handle( $value, $source );

		if( $this->multiple ) {
			$selected = array();

			foreach( $value as $id ) {
				if( $id ) {
					$selected[] = intval( $id );
				}
			}

			$value = $selected;
		} else {
			if( $value ) {
				$value = intval( $value );
			} else {
				$value = false;
			}
		}

		return $value;
	}

	/**
	 * Processes the value for the_field.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return string
	 */
	public function process( $value ) {
		# IF there is no value, don't use it
		if( ! $value ) {
			return '';
		}

		# Convert to an array, no matter what type of field
		$value = (array) $value;

		# Force everything in the cache simultaneously
		if( 1 == count( $value ) ) {
			$existing = array( get_post( $value[ 0 ] ) );
		} else {
			$existing = get_posts( array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post__in'       => $value,
				'orderby'        => 'post__in',
				'order'          => 'ASC'
			));
		}

		# Process
		$processed = array();
		foreach( $existing as $p ) {
			if( $p ) {
				$processed[] = $this->process_single_value( $p->ID );
			}
		}

		# Merge if needed/return
		if( $this->multiple ) {

		} else {
			return array_shift( $processed );
		}
	}

	/**
	 * Proceses a single value.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the value.
	 * @return string
	 */
	protected function process_single_value( $id ) {
		switch( $this->output_type ) {
			case 'link':
				$url = wp_get_attachment_url( $id );
				return sprintf(
					'<a href="%s">%s</a>',
					$url,
					basename( $url )
				);

			case 'url':
				return wp_get_attachment_url( $id );

			case 'id':
				return $id;
		}
	}
}
