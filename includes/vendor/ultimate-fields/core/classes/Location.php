<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Datastore;
use WP_REST_Server;
use ReflectionClass;

/**
 * This is the base class for all locations.
 *
 * Locations are objects, which determine wheter a container
 * should be shown within a particular container.
 *
 * @since 3.0
 */
abstract class Location {
	/**
	 * This property will hold all arguments that are to be
	 * processed when the location is really needed. This way
	 * no additional PHP will be executed until the location
	 * is really in use.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $arguments = array();

	/**
	 * If there is a datastore, which is manually set for the location,
	 * it will be stored here.
	 *
	 * Use this (overwrite_datastore()) to do things like an options page,
	 * which saves data to the current user and things like that.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore
	 */
	protected $forced_datastore;

	/**
	 * Creates a basic location from a given type.
	 *
	 * @since 3.0
	 *
	 * @param string $type The type ofthe location.
	 * @return mixed
	 */
	protected static function _get_location_class( $type ) {
		$type = strtolower( $type );

		/**
		 * Allows the class, used for a location to be generated externally.
		 *
		 * @since 3.0
		 *
		 * @param string $class_name The name of the class.
		 * @param string $type       The location type (ex. 'post_type').
		 * @return string
		 */
		$class_name = apply_filters( 'uf.location.class', null, $type );

		if( is_null( $class_name ) ) {
			$class_name = ultimate_fields()->generate_class_name( "Location/$type" );
		}

		return $class_name;
	}

	/**
	 * Creates a new location object based on a string type.
	 *
	 * @since 3.0
	 *
	 * @param  string $type The type of location (ex. post_type).
	 * @return Location
	 */
	public static function create( $type ) {
		$class_name = self::_get_location_class( $type );

		if( ! class_exists( $class_name ) ) {
			Helper\Missing_Features::instance()->report( $class_name, 'location' );
			return new Helper\Dummy_Class;
		}

		$args       = func_get_args();
		$type       = array_shift( $args );
		$reflection = new ReflectionClass( $class_name );
		$location   = $reflection->newInstanceArgs( $args );

		return $location;
	}

	/**
	 * Holds the fields, which would be exposed through the WordPress API
	 * by the controller.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $api_fields = array();

	/**
	 * Creates a new basic location.
	 *
	 * @since 3.0
	 *
	 * @param array $args Arguments (optional).
	 */
	public function __construct( $args ) {
		$this->set_and_unset( $args, array(
			'context'            => 'set_context',
			'priority'           => 'set_priority'
		));

		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
	}

	/**
	 * Returns a new datastore, which the controller should work with.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The object to generate a datastore for (optional).
	 * @return Ultimate_Fields\Datastore.
	 */
	public function get_datastore( $object = null ) {
		if( ! is_null( $this->forced_datastore ) ) {
			return $this->forced_datastore;
		} else {
			return $this->create_datastore( $object );
		}
	}

	/**
	 * Returns a datastore, associated with the object, if it works.
	 *
	 * @param object $object Always a properly-typed object, except widgets and stuff.
	 * @return mixed Either a datastore or a boolean false, if the object is not supported.
	 */
	/*acstract*/ protected function create_datastore( $object ) {}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	/*abstract*/ public function works_with( $item ) {}

	/**
	 * Goes through a list of arguments and calls the
	 * appropriate functions for them.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to parse.
	 */
	protected function parse_arguments( $args ) {
		foreach( $args as $key => $value ) {
			$this->__set( $key, $value );
		}
	}

	/**
	 * Checks an array of arguments for properties, proxies them
	 * to setters and removes the property from the original array.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]  $args  An array of arguments.
	 * @param string[] $pairs A set of property => method pairs.
	 */
	protected function set_and_unset( & $args, $pairs ) {
		foreach( $pairs as $property => $method ) {
			if( ! isset( $args[ $property ] ) )
				continue;

			$this->$method( $args[ $property ] );
			unset( $args[ $property ] );
		}
	}

	/**
	 * Allows values to be set externally.
	 *
	 * @since 3.0
	 *
	 * @param string $key   The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function __set( $key, $value ) {
		$method_name = 'set_' . $key;

		if( method_exists( $this, $method_name ) ) {
			$this->$method_name( $value );
		} else {
			wp_die( "$key seems not to be a valid property for " . get_class( $this ) );
		}
	}

	/**
	 * Returns a rule if existing.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the rule to retrieve.
	 * @return mixed[]
	 */
	public function __get( $name ) {
		if( property_exists( $this, $name ) ) {
			# Marke sure to parse first
			$this->parse_arguments( $this->arguments );

			return $this->$name;
		}
	}

	/**
	 * Parses a value to a show and hide combination.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $value The value(s) to to parse.
	 * @return mixed[] An array with 'show' and 'hide' objects.
	 */
	public function extract_value( $rules ) {
		$visible = array();
		$hidden = array();

		if( is_string( $rules ) && false !== strpos( $rules, ',' ) ) {
			# A single string can contain multiple rules, splitted by commas.
			$rules = explode( ',', $rules );
		} elseif( is_scalar( $rules ) ) {
			# Convert it into an array
			$rules = array( $rules );
		} elseif( is_array( $rules ) ) {
			$basic = array();

			foreach( $rules as $key => $rule ) {
				if( ! $rule ) {
					continue;
				}

				if( is_null( $rule ) ) {
					continue;
				}

				if( (array) $rule !== $rule ) {
					$rule = (array) $rule;
				}

				if( 'visible' === $key ) {
					$visible = array_merge( $visible, $rule );
				} elseif( 'hidden' === $key ) {
					$hidden = array_merge( $hidden, $rule );
				} else {
					$basic = array_merge( $basic, $rule );
				}
			}

			$rules = $basic;
		}

		# Process
		foreach( $rules as $rule ) {
			if( is_array( $rule ) ) {
				continue; // ToDo: This shouldn't be here
			}

			if( 0 === strpos( $rule, '-' ) ) {
				$hidden[] = substr( $rule, 1 );
			} else {
				$visible[] = $rule;
			}
		}

		return array(
			'visible' => array_filter( $visible ),
			'hidden'  => array_filter( $hidden )
		);
	}

	/**
	 * Processes two visible + hidden arrays, adding or removing values from the first one.
	 *
	 * @since 2.0
	 *
	 * @param string[] $exsiting The existing array of values.
	 * @param string[] $modified The ones that should be modified.
	 * @return string[] The processed values.
	 */
	protected function process_value( $existing, $modified, $parser = false ) {
		if( ! is_array( $existing ) || empty( $existing ) ) {
			$existing = array(
				'visible' => array(),
				'hidden'  => array()
			);
		}

		if( ! isset( $modified['visible'] ) ) {
			$modified['visible'] = array();
		}

		if( ! isset( $modified['hidden'] ) ) {
			$modified['hidden'] = array();
		}

		# Prepare a dictionary
		$dictionary = array_unique( array_merge( $modified[ 'visible' ], $modified[ 'hidden' ] ) );
		$dictionary = array_combine( $dictionary, $dictionary );
		if( $parser ) {
			$dictionary = call_user_func( $parser, $dictionary );
		}

		# Add new rules
		foreach( $modified[ 'visible' ] as $value ) {
			$value = $dictionary[ $value ];

			if( ! in_array( $value, $existing[ 'visible' ] ) ) {
				$existing[ 'visible' ][] = $value;
			}
		}

		# Remove rules
		foreach( $modified[ 'hidden' ] as $value ) {
			$value = $dictionary[ $value ];

			# Add to the hidden array
			if( ! in_array( $value, $existing[ 'hidden' ] ) ) {
				$existing[ 'hidden' ][] = $value;
			}

			# Remove from the visible array
			if( false !== ( $index = array_search( $value, $existing[ 'visible' ] ) ) ) {
				unset( $existing[ $index ] );
			}
		}

		return $existing;
	}

	/**
	 * Converts any type of parameter (single or array, existing or not and etc.)
	 *
	 * @since 3.0
	 *
	 * @param  mixed    $existing An array of existing rules.
	 * @param  mixed    $value    The new rules to parse.
	 * @param  callable $parser   A parser to call for every item once prepared (Optional).
	 * @return mixed[]            A properly formatted array with visible and hidden sub-arrays.
	 */
	protected function handle_value( $existing, $value, $parser = false ) {
		$rules = $this->extract_value( $value );
		return $this->process_value( $existing, $rules, $parser );
	}

	/**
	 * Checks a single value based on rules.
	 *
	 * @since 3.0
	 *
	 * @param mixed   $value The value to check for.
	 * @param mixed[] $rules The rules to check upon.
	 * @return bool
	 */
	public function check_single_value( $value, $rules ) {
		if( ! empty( $rules[ 'hidden' ] ) && in_array( $value, $rules[ 'hidden' ] ) ) {
			return false;
		}

		if( empty( $rules[ 'visible' ] ) ) {
			return true;
		}

		return in_array( $value, $rules[ 'visible' ] );
	}

	/**
	 * Checks multiple values based on rules.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $values The values to check for.
	 * @param mixed[] $rules  The rules to check upon.
	 * @return bool
	 */
	public function check_multiple_values( $values, $rules ) {
		$hidden  = false;
		$visible = empty( $rules[ 'visible' ] );

		foreach( $values as $value ) {
			if( ! empty( $rules[ 'hidden' ] ) && in_array( $value, $rules[ 'hidden' ] ) )	 {
				$hidden = true;
			}

			if( in_array( $value, $rules[ 'visible' ] ) ) {
				$visible = true;
			}
		}

		return ! $hidden && $visible;
	}

	/**
	 * Creates a location from an array.
	 *
	 * @since 3.0
	 *
	 * @var mixed[] $arguments The arguments as exported in JSON.
	 * @return Location
	 */
	public static function create_from_array( $args ) {
		$class_name = self::_get_location_class( $args['type'] );

		if( ! class_exists( $class_name ) ) {
			Helper\Missing_Features::instance()->report( $class_name, 'location' );
			return new Helper\Dummy_Class;
		}

		$location = new $class_name;
		$location->import( $args );

		return $location;
	}

	/**
	 * Allows the default datastore for the location to be overwritten.
	 *
	 * The datastore that is used must be already set up (have an object/id) to work with.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore, which is to be used.
	 * @return Location The instance of the class.
	 */
	public function overwrite_datastore( Datastore $datastore ) {
		$this->forced_datastore = $datastore;

		return $this;
	}

	/**
	 * Exports the location to a PHP/JSON-compatible format.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = array(
			'type' => ultimate_fields()->basename( $this )
		);

		return $settings;
	}

	/**
	 * Exports a rule from the location.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The already generated data for the export.
	 * @param mixed[] $rule An array that has sub-arrays for visible and hidden.
	 */
	protected function export_rule( &$data, $rule_name ) {
		if( ! isset( $this->$rule_name ) ) {
			return;
		}

		$rule = $this->$rule_name;
		$all = $this->export_values_for_rule( $rule );

		if( ! empty( $all ) ) {
			$data[ $rule_name ] = $all;
		}
	}

	/**
	 * Exports the values of a rule.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $rule An array that has sub-arrays for visible and hidden rules.
	 * @return mixed[]
	 */
	protected function export_values_for_rule( $rule ) {
		$all = array();

		if( isset( $rule[ 'visible' ] ) ) {
			foreach( $rule[ 'visible' ] as $property ) $all[] = $property;
		}

		if( isset( $rule[ 'hidden' ] ) ) {
			foreach( $rule[ 'hidden' ] as $property ) $all[] = '-' . $property;
		}

		return $all;
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
		return false;
	}

	/**
	 * Sets certain fields as exposable to the API.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $fields The fields to expose.
	 * @return Location
	 */
	public function expose_api_fields( $fields ) {
		$this->api_fields = array_merge( $this->api_fields, (array) $fields );

		return $this;
	}

	/**
	 * Returns the fields, which will be exposed to the API.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_api_fields() {
		return $this->api_fields;
	}

	/**
	 * Changes an array of exportable (JSON) information to include REST data.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $settings The settings where to include data.
	 */
	public function export_rest_data( & $settings ) {
		if( empty( $this->api_fields ) ) {
			return;
		}

		$fields = array();
		foreach( $this->api_fields as $field => $type ) {
			$fields[ $field ] = WP_REST_Server::EDITABLE == $type;
		}
		$settings[ 'api_fields' ] = $fields;
	}

	/**
	 * Imports REST API data (mainly from JSON).
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The data to check for column.
	 */
	protected function import_rest_data( $args ) {
		if( ! isset( $args[ 'api_fields' ] ) ) {
			return;
		}

		$fields = array();
		foreach( $args[ 'api_fields' ] as $field => $editable ) {
			$fields[ $field ] = $editable
				? WP_REST_Server::EDITABLE
				: WP_REST_Server::READABLE;
		}
		$this->expose_api_fields( $fields );
	}
}
