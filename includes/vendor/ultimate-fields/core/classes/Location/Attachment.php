<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Attachment as Controller;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Works as a location definition for containers inthe media popup.
 *
 * @since 3.0
 */
class Attachment extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'attachment';

	/**
	 * Holds all data types, which the location works with.
	 * By default, the attachment location works with all file types.
	 *
	 * @since 3.0
	 */
	protected $file_types = array();

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $file_types = array() ) {
		# Send all arguments to the appropriate setter.
		$this->set_file_types( $file_types );
	}

	/**
	 * Returns an instance of the controller, which controls the location (posts).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Comment
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Handles file types for locations.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $types The types to show/hide the container on.
	 * @return Ultimate_Fields\Location\Attachment
	 */
	protected function set_file_types( $file_types ) {
		$this->file_types = $this->handle_value( $this->file_types, $file_types );
		return $this;
	}

	/**
	 * Returns the settings for the location, sendable to JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings() {
		$this->parse_arguments( $this->arguments );

		$data = array();

		if( ! empty( $this->file_types ) ) {
			$data[ 'file_types' ] = $this->file_types;
		}

		return $data;
	}

	/**
	 * Checks if the location supports a certain type.
	 *
	 * @since 3.0
	 *
	 * @param string $type The MIME type to check.
	 * @return bool
	 */
	public function supports_mime_type( $type ) {
		$this->parse_arguments( $this->arguments );

		if( empty( $this->file_types ) ) {
			return true;
		}

		if( in_array( $type, $this->file_types[ 'hidden' ] ) ) {
			return false;
		}

		if( ! empty( $this->file_types[ 'visible' ] ) && ! in_array( $type, $this->file_types[ 'visible' ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	public function works_with( $source ) {
		# Check for the attachment type
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		# If there is no object, then we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Convert to a proper post
		if( is_a( $source, 'WP_Post' ) ) {
			$post = $source;
		} elseif( is_a( $source, Data_Source::class ) && 'post_meta' == $source->type && $existing = get_post( $source->item ) ) {
			$post = $existing;
		} else {
			return false;
		}

		if( 'attachment' != $post->post_type ) {
			return false;
		}

		return $post;
	}

	/**
	 * Generates a datastore based on an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The post to create a datastore for.
	 * @return mixed
	 */
	public function create_datastore( $object ) {
		if( ! is_a( $object, 'WP_Post' ) && ! is_int( $object ) ) {
			return false;
		}

		$post = get_post( $object );
		if( ! $post || 'attachment' != $post->post_type ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( $post->ID );

		return $datastore;
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();
		$this->export_rule( $settings, 'file_types' );

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		if( isset( $args[ 'file_types' ] ) ) {
			$this->file_types = $args[ 'file_types' ];
		}
	}
}
