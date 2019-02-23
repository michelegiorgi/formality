<?php
namespace Ultimate_Fields\Dependency;

/**
 * Handles a single dependency within a group.
 *
 * @since 3.0
 */
class Rule {
	/**
	 * The name of a field, the rule is based on.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $field;

	/**
	 * Holds the value that the field is compared to.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Holds the comparator, by default - equals.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $compare = '==';

	/**
	 * Creates a new dependency.
	 *
	 * @since 3.0
	 *
	 * @param array $rule {
	 *     All the basic data about the dependency.
	 *
	 *     @type string $field   The name fo the field the dependency works with.
	 *     @type mixed  $value   A value to compare to.
	 *     @type string $compare The operator that is used for comparisons.
	 * }
	 * @return Dependency
	 */
	public static function create( $rule ) {
		return new self( $rule );
	}

	/**
	 * Constructor of the class.
	 *
	 * @since 3.0
	 * @see Rule::create
	 * @param array $rule The same as above.
	 */
	public function __construct( $rule ) {
		$this->field   = $rule[ 'field' ];
		$this->value   = $rule[ 'value' ];
		$this->compare = $rule[ 'compare' ];
	}

	/**
	 * Checks the state of the dependency within a datastore.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore that contains values to check upon.
	 * @return bool                    A boolean that indicates if the rule is satisfied.
	 */
	public function check( $datastore ) {
		$up = 0;
		$regex = '~^..\s*/\s*~';
		$field = $this->field;

		while( preg_match( $regex, $field ) ) {
			$up++;
			$field = preg_replace( $regex, '', $field );
		}

		while( $up-- ) {
			$datastore = $datastore->parent;

			if( ! $datastore ) {
				break;
			}
		}

		# No datastore, no value
		if( ! $datastore )
			return false;

		# Get the value from the datastore
		$value = $datastore->get( $field );

		# Check the value
		return $this->check_value( $value, $this->value, $this->compare );
	}

	/**
	 * Checks if a value works with the internal settings.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to check upon.
	 * @param mixed $needed The value to look for.
	 * @param string $compare The comparator to use.
	 * @return bool
	 */
	public function check_value( $value, $needed, $compare ) {
		$valid = false;

		$numeric = array( '>=', '<=', '<', '>', '=', '==' );

		// Convert the value into an array for some comparators
		if( is_array( $value ) && in_array( $compare, $numeric ) ) {
			$value = count( $value );
		} elseif( is_null( $value ) ) {
			$value = false;
		}

		switch( strtoupper( $compare ) ) {
			case '>=':
				if( is_numeric( $needed ) ) {
					$valid = $value >= floatval( $needed );
				} else {
					$valid = count( $value ) >= intval( $needed );
				}
				break;

			case '<=':
				if( is_numeric( $needed ) ) {
					$valid = $value <= floatval( $needed );
				} else {
					$valid = count( $value ) <= intval( $needed );
				}
				break;

			case '<':
				if( is_numeric( $needed ) ) {
					$valid = $value < floatval( $needed );
				} else {
					$valid = count( $value ) < intval( $needed );
				}
				break;

			case '>':
				if( is_numeric( $needed ) ) {
					$valid = $value > floatval( $needed );
				} else {
					$valid = count( $value ) > intval( $needed );
				}
				break;

			case '!=':
				$valid = $value != $needed;
				break;

			case 'NOT_NULL':
				$valid = ! (
					false === $value
					|| is_null( $value )
					|| ( is_string( $value ) && 'false' === $value )
					|| ( is_array( $value ) && empty( $value ) )
				);
				break;

			case 'NULL':
				if( is_array( $value ) ) {
					$valid = ! empty( $value );
				} else {
					$valid = ! $value;
				}
				break;

			case 'IN':
				if( ! $value ) {
					$valid = false;
				} else {
					if( is_string( $value ) && false !== strpos( $value, ',' ) ) {
						$parts = explode( ',', $value );
						$valid = false;
						foreach( $parts as $part ) {
							if( in_array( $part, $needed ) ) {
								$valid = true;
							}
						}
					} else {
						$valid = in_array( $value, $needed );
					}
				}
				break;

			case 'NOT_IN':
				if( is_string( $value ) ) {
					$parts = explode( ',', $value );
					$valid = true;
					foreach( $parts as $part ) {
						if( in_array( $part, $needed ) ) {
							$valid = false;
						}
					}
				} else {
					$valid = in_array( $value, $needed );
				}
				break;

			case 'CONTAINS':
				if( is_array( $needed ) ) {
					$valid = false;
					foreach( $needed as $sub ) {
						if( in_array( $sub, $value ) ) {
							$valid = true;
							break;
						}
					}
				} else {
					$valid = is_array( $value ) && in_array( $needed, $value );
				}
				break;

			case 'DOES_NOT_CONTAIN':
				if( is_array( $needed ) ) {
					$valid = true;
					foreach( $needed as $sub ) {
						if( in_array( $sub, $value ) ) {
							$valid = false;
							break;
						}
					}
				} else {
					$valid = ! is_array( $value ) && ! in_array( $needed, $value );
				}
				break;

			case 'CONTAINS_GROUP':
				$valid = false;
				if( is_array( $value ) ) {
					foreach( $value as $row ) {
						if( is_array( $row ) && isset( $row[ '__type' ] ) && $needed == $row[ '__type' ] ) {
							$valid = true;
							break;
						} elseif( is_array( $row ) && isset( $row[ 0 ] ) && isset( $row[ 0 ][ '__type' ] ) ) {
							foreach( $row as $group ) {
								if( $group[ '__type' ] === $needed ) {
									$valid = true;
								}
							}
						}
					}
				}
				break;

			case 'DOES_NOT_CONTAIN_GROUP':
				$valid = true;
				if( is_array( $value ) ) {
					foreach( $value as $row ) {
						if( is_array( $row ) && isset( $row[ '__type' ] ) && $needed == $row[ '__type' ] ) {
							$valid = false;
							break;
						} elseif( is_array( $row ) && isset( $row[ 0 ] ) && isset( $row[ 0 ][ '__type' ] ) ) {
							foreach( $row as $group ) {
								if( $group[ '__type' ] === $needed ) {
									$valid = false;
								}
							}
						}
					}
				}
				break;

			default:
			case '=':
			case '==':
				if( is_string( $needed ) && is_array( $value ) ) {
					$valid = in_array( $needed, $value );
				} else {
					$valid = $value == $needed;
				}
				break;
		}

		return $valid;
	}

    /**
     * Exports the dependency for JS.
     *
     * @return mixed[]
     */
    public function export() {
        return array(
            'field'   => $this->field,
            'value'   => $this->value,
            'compare' => $this->compare
        );
    }
}
