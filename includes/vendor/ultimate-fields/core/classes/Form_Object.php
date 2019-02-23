<?php
namespace Ultimate_Fields;

/**
 * Handles objects that front-end forms work with.
 *
 * @since 3.0
 */
abstract class Form_Object {
	protected $item;
	protected $title = '';
	protected $content = '';

	/**
	 * Holds the fornt-end fields for the object.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $fields;

	/**
	 * Creates a new object based on form arguments.
	 *
	 * Please avoid calling this method directly - it should only
	 * be used when creating an object within Ultimate_Fields\Form.
	 *
	 * @since 3.0
	 * @see Form.php
	 *
	 * @param mixed $args The arguments for the form.
	 * @return Form_Object
	 */
	public static function create( $args ) {
		static $keywords;

		# Check for an existing object
		if( isset( $args[ 'item' ] ) && is_a( $args[ 'item' ], Form_Object::class ) ) {
			return $args[ 'item' ];
		}

		if( is_null( $keywords ) ) {
			/**
			* Allows Form_Object classes to be modified.
			*
			* @since 3.0
			*
			* @param string[] $class_names The names of classes which can handle objects.
			* @return string[]
			*/
			$types = apply_filters( 'uf.form.object_types', array(
				Form_Object\Post::class,
				Form_Object\Term::class,
				Form_Object\User::class,
				Form_Object\Comment::class,
				Form_Object\Options::class
			));

			$keywords = array();
			foreach( $types as $type ) {
				$keywords = array_merge( $keywords, call_user_func( array( $type, 'get_keywords' ) ) );
			}
		}

		# An ID could be used, but only eventually
		$id   = '';
		$type = '';

		if( $args[ 'create_new' ] ) {
			/**
			 * Prepare a new item to be created
			 */
			$type = $args[ 'create_new' ];

			if( isset( $keywords[ $type ] ) ) {
				$class_name = $keywords[ $type ];
			} else {
				$message = __( '%s is not a valid form object type!', 'ultimate-fields' );
				wp_die( sprintf( $message, $type ) );
			}
		} else {
			/**
			 * Load an existing item
			 */
			if( $args[ 'item' ] ) {
				# Manually specified
				$item = $args[ 'item' ];
			} else {
				# Simply the current item
				$item = get_queried_object();
			}

			# Parse the type/id if that format is used
			$types = array_map( 'preg_quote', array_keys( $keywords ) );
			$regex = '~^(' . implode( $types, '|' ) . ')_(\d+)$~i';

			if( is_object( $item ) ) {
				$type = get_class( $item );
			} else {
				if( is_string( $item ) ) {
					if( preg_match( $regex, $item, $matches ) ) {
						$type = $matches[ 1 ];
						$id   = $matches[ 2 ];
					} elseif( 'user' == $item ) {
						// The user object can load the current user if any.
						$type = 'user';
						$id   = null;
					} elseif( 'options' == $item ) {
						// The options object does not need an item
						$type = 'options';
						$id   = null;
					} else {
						$message = __( '&quot;%s&quot; is not a valid form item format! Maybe you want to use the &quot;create_new&quot; parameter?', 'ultimate-fields' );
						wp_die( sprintf( $message, $item ) );
					}
				} elseif( is_int( $item ) ) {
					$type = 'post';
					$id   = $item;
				} else {
					wp_die( __( 'Unknown item type.', 'ultimate-fields' ) );
				}
			}

			$class_name = $keywords[ $type ];
		}

		# Prepare the single argument for the constructor:
		# an ID or an object for existing items.
		$arg = null;
		if( $id ) {
			$arg = $id;
		} elseif( isset( $item ) ) {
			$arg = $item;
		}

		# Create the object and set the type if no existing items are used.
		$object = new $class_name( $arg );
		if( $type && $args[ 'create_new' ] ) {
			$object->set_type( $type );
		}

		return $object;
	}

	/**
	 * Creates a datastore that works with the given object.
	 *
	 * @since 3.0
	 *
	 * @return Datastore The datastore that should be used with the front-end container.
	 */
	abstract public function get_datastore();

	/**
	 * Saves the object if needed.
	 *
	 * @since 3.0
	 */
	abstract public function save();

	/**
	 * Returns the original item.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_raw_object() {
		return $this->item;
	}

	/**
	 * Changes the internal type.
	 * This could be a post type or a taxonomy.
	 *
	 * @since 3.0
	 *
	 * @param string $type The type to use.
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * Returns the post type for the object.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Returns the fields, which will be used for the object in the front end.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_fields() {
		return apply_filters( 'uf.form_object.fields', $this->fields, $this );
	}

	/**
	 * Generates the fields, which would be used for the object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include.
	 */
	abstract public function setup_fields( $include = 'all' );
}
