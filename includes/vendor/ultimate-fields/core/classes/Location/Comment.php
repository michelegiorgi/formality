<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Comment as Controller;
use Ultimate_Fields\Form_Object\Comment as Form_Object;
use Ultimate_Fields\Helper\Data_Source;
use Ultimate_Fields\Datastore\Comment_Meta as Datastore;

/**
 * Works as a location definition for comments within containers.
 *
 * @since 3.0
 */
class Comment extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'comment';

	/**
	 * The stati, which the locationw works with.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $stati = array();

	/**
	 * Holds the priority of the meta box.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $priority = 'default';

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $args = array() ) {
		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
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
	 * Returns the priority for the location/meta box.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Allows the priority of the meta box.
	 *
	 * @since 3.0
	 *
	 * @param string $priority The priority.
	 * @return Ultimate_Fields\Location\Comment
	 */
	public function set_priority( $priority ) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Changes the stati, the location works with.
	 *
	 * @since 3.0
	 *
	 * @param mixed $stati The stati to use.
	 * @return Ultimate_Fields\Location\Comment The instance of the location.
	 */
	public function set_stati( $stati ){
		$this->stati = $this->handle_value( $this->stati, $stati );

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
		if( ! empty( $this->stati ) ) {
			$data[ 'stati' ] = $this->stati;
		}

		return $data;
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
		# Check for the comment keyword
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		# Initialize arguments if needed
		$this->parse_arguments( $this->arguments );

		# If there is no object, then we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Convert to a proper comment
		$comment = null;

		if( is_a( $source, 'WP_Comment' ) ) {
			$comment = $source;
		}

		if( is_null( $comment ) && is_a( $source, Data_Source::class ) && 'comment' == $source->type ) {
			$id      = intval( $source->item );
			$comment = get_comment( $id );
		}

		if( is_null( $comment ) ) {
			return false;
		}

		# Check the status
		if( ! empty( $this->stati ) ) {
			switch( $comment->comment_approved ) {
				case '1':
					$status = 'approved';
					break;
				case 'spam':
					$status = 'spam';
					break;
				default:
					$status = 'pending';
					break;
			}

			if( ! $this->check_single_value( $status, $this->stati ) ) {
				return false;
			}
		}

		return $comment;
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
		if( ! is_a( $object, 'WP_Comment' ) && ! is_int( $object ) ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( is_int( $object ) ? $object : $object->comment_ID );

		return $datastore;
	}

	/**
	 * Checks if the location works with a front-end forms object.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Form_Object $object The object to check.
	 * @return bool
	 */
	public function works_with_object( $object ) {
		if( ! is_a( $object, Form_Object::class ) ) {
			return false;
		}

		// ToDo: Check actual rules
		$raw = $object->get_raw_object();


		return true;
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

		if( 'default' != $this->priority ) {
			$settings[ 'priority' ] = $this->priority;
		}
		$this->export_rule( $settings, 'stati' );

		# Export REST data
		$this->export_rest_data( $settings );

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
		if( isset( $args[ 'priority' ] ) ) {
			$this->priority = $args[ 'priority' ];
		}

		if( isset( $args[ 'stati' ] ) ) {
			$this->__set( 'stati', $args[ 'stati' ] );
		}

		# Check for rest data
		$this->import_rest_data( $args );
	}
}
