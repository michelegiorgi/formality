<?php
namespace Ultimate_Fields\Helper;

use Ultimate_Fields\Datastore;

/**
 * Describes the context a value should be retrieved from in the data API.
 *
 * @since 3.0
 */
class Data_Source {
	/**
	 * The type of data source (ex. post_meta).
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $type;

	/**
	 * The name of the field, which will be fetched.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $name;

	/**
	 * An item (ID) to work with.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $item;

	/**
	 * The class of the datastore, which works with the current object.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $datastore_class;

	/**
	 * Indicates if the value should be processed by the field it comes from.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $process = false;

	/*
	 * @param [type] $type
	 * @param [type] $name
	 * @return [type]
	 */
	/**
	 * Generates a new instance of the source based on a type and a name.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $type The type/location of a vaue, ex. post_3, user_5 and etc.
	 * @param string $name The name of a field, whose data is needed.
	 * @return Data_Source
	 */
	public static function parse( $type, $name = '' ) {
		return new self( $type, $name );
	}

	/**
	 * Parses a source.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $type The type/location of a vaue, ex. post_3, user_5 and etc.
	 * @param string $name The name of a field, whose data is needed.
	 */
	protected function __construct( $type, $name ) {
		$this->name = $name;

		# Convert the type to string for proper checks
		$type = $type . '';

		foreach( self::get_available_types() as $t ) {
			# Check for the current post
			if( ! $type && ! $t[ 'keyword' ] && ! $t[ 'item' ] ) {
				$this->type = $t[ 'type' ];
				$this->item = call_user_func( array( $t[ 'class' ], 'load_anonymous_item' ) );
				break;
			}

			# Check based on a simple ID
			if( preg_match( '~^\d+$~', $type ) && ! $t[ 'keyword' ] && ! $t[ 'item' ] ) {
				$this->type = $t[ 'type' ];
				$this->item = $type;
				break;
			}

			# Check for a keyword-only call
			if( ! $t[ 'item' ] && $type == $t[ 'keyword' ] ) {
				$this->type = $t[ 'type' ];
				if( method_exists( $t[ 'class' ], 'load_anonymous_item' ) ) {
					$this->item = call_user_func( array( $t[ 'class' ], 'load_anonymous_item' ) );
				} else {
					$this->item = false;
				}
				break;
			}

			# Check for a type-item combination
			if( $t[ 'item' ] && $t[ 'keyword' ] ) {
				# Generate a regular expression to check
				$regex = '~^' . $t[ 'keyword' ] . '_';
				if( true == $t[ 'item' ] ) {
					$regex .= '(\d+)';
				} else {
					$regex .= $t[ 'item' ];
				}
				$regex .= '$~';

				# Check the expression
				if( preg_match( $regex, $type, $matches ) ) {
					$this->type = $t[ 'type' ];
					$this->item = $matches[ 1 ];
					break;
				}
			}
		}

		$this->datastore_class = $t[ 'class' ];

		# If there is nothing found, throw an exception
		if( ! $this->type ) {
			$message = __( '%s could not be resolved to an existing type.', 'ultimate-fields' );
			$message = sprintf( $message, $type );
			wp_die( $message );
		}
	}

	/**
	 * Returns all available source types.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_available_types() {
		static $types;

		if( ! is_null( $types ) ) {
			return $types;
		}

		/**
		 * If you need to add additional datastores to the data API of
		 * the plugin, you can do it here. The datastore must after that
		 * define what keywords can be used with it and if it supports items.
		 *
		 * @since 3.0
		 *
		 * @param string[] $datastore_classes The classes to work with
		 * @return string
		 */
		$classes = apply_filters( 'uf.data_api.datastore_classes', array(
			Datastore\Post_Meta::class,
			Datastore\Options::class,
			Datastore\Network_Options::class,
			Datastore\Term_Meta::class,
			Datastore\User_Meta::class,
			Datastore\Widget::class,
			Datastore\Shortcode::class,
			Datastore\Comment_Meta::class,
			Datastore\Gutenberg_Block::class,
		));

		# Go through each class and check for keywords
		$types = array();
		foreach( $classes as $class_name ) {
			if( ! method_exists( $class_name, 'get_data_api_options' ) ) {
				$message = __( 'The %s datastore is not compatible with the data API.' );
				$message = sprintf( $message, $class_name );
				wp_die( $message );
			}

			$subtypes = call_user_func( array( $class_name, 'get_data_api_options' ) );

			foreach( $subtypes as $type ) {
				$type[ 'class' ] = $class_name;
				$types[] = $type;
			}
		}

		return $types;
	}

	/**
	 * Serializes source in order to use them in the Data_API tree.
	 *
	 * @since 3.0
	 *
	 * @return string A simplified version of the source
	 */
	public function hash() {
		$str = $this->type;
		$str .= ':' . $this->name;

		if( $this->item ) {
			$str .= ':' . $this->item;
		}

		return $str;
	}

	/**
	 * Returns one of the internal values when needed.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the internal value.
	 * @return mixed
	 */
	public function __get( $name ) {
		// ToDo: Add support for extra arguments
		return property_exists( $this, $name ) ? $this->$name : false;
	}

	/**
	 * Tells the source that values should be processed.
	 *
	 * @since 3.0
	 *
	 * @return Data_Source
	 */
	public function process() {
		$this->process = true;

		return $this;
	}

	/**
	 * Generates a datastore that works with the current source.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Datastore
	 */
	public function generate_datastore() {
		$datastore = new $this->datastore_class;
		if( $this->item ) {
			$datastore->set_id( $this->item );
		}
		return $datastore;
	}
}
