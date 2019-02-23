<?php
namespace Ultimate_Fields\Helper;

use ArrayAccess;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Handles the array that is associated with a group's values.
 *
 * @since 3.0
 */
class Group_Values implements ArrayAccess {
	/**
	 * Holds the actual values of the group.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $values;

	/**
	 * Holds the group container, which would process those values.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $group;

	/**
	 * Creates an instance of the class.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $values The values to work with.
	 * @param Ultimate_Fields\Container\Group The group that will work with the values.
	 */
	public function __construct( $values, $group ) {
		$this->values = $values;
		$this->group = $group;
	}

	/**
	 * Checks if a key has a value within the group.
	 *
	 * First this checks if there is a normal value, if not, checks for a field
	 * with the same name, as the default value of that field would be usable.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The needed key.
	 */
	public function offsetExists( $offset ) {
		if( isset( $this->values[ $offset ] ) ) {
			return true;
		}

		if( $this->group->get_field( $offset ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the group can handle a certain key.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the needed value.
	 * @return string      The actual name of the field.
	 */
	public function can_handle( $name ) {
		$source = Data_Source::parse( null, $name );
		return $this->group->can_handle( $source );
	}

	/**
	 * Retrieves a value from the group.
	 *
	 * @since 3.0
	 *
	 * @param mixed $offset The key of the needed value.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		$value = false;

		if( isset( $this->values[ $offset ] ) ) {
			$value = $this->values[ $offset ];
		} else {
			$value = $this->group->get_field( $offset )->get_default_value();
		}

		# Handle the basic value
		if( $field = $this->group->get_field( $offset ) ) {
			$value = $field->handle( $value );
		}

		return $value;
	}

	/**
	 * Returns a processed value when needed.
	 *
	 * Works the same way as offsetGet, but also lets the field
	 * process the value based on it's internal format.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The offset to look for a value at.
	 * @return mixed
	 */
	public function get_processed( $offset, $source = null ) {
		if( ! $this->offsetExists( $offset ) ) {
			return false;
		}

		$value = $this->offsetGet( $offset );

		# Process
		if( $field = $this->group->get_field( $offset ) ) {
			$value = $field->process( $value, $source );
		}

		return $value;
	}

	/**
	 * Changes a value from within the group.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The key that should be changed.
	 * @param mixed  $value  The new value,
	 */
	public function offsetSet( $offset, $value ) {
		$this->values[ $offset ] = $value;
	}

	/**
	 * Deletes an element from the group.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The key to delete.
	 */
	public function offsetUnset( $offset ) {
		if( isset( $this->values[ $offset ] ) ) {
			unset( $this->values[ $offset ] );
		}
	}

	/**
	 * Returns all value of the group in a processed format.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$data = array(
			'__type' => $this->values[ '__type' ]
		);

		foreach( $this->group->get_fields() as $field ) {
			$value = $this->offsetGet( $field->get_name() );
			$value = $field->process( $value );
			$data[ $field->get_name() ] = $value;
		}

		return $data;
	}

	/**
	 * Returns the simple internal values for debugging purposes.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function __debugInfo() {
		return $this->values;
	}

	/**
	 * Returns the simple values in a serializable way.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_raw_values() {
		return $this->values;
	}

	/**
	 * Returns the type of the group, if any.
	 *
	 * @since 3.0
	 *
	 * @return string|bool Either the type or a boolean false.
	 */
	public function get_type() {
		if( isset( $this->values[ '__type' ] ) ) {
			return $this->values[ '__type' ];
		}

		if( isset( $this->group ) ) {
			return $this->group->get_id();
		}

		return false;
	}

	/**
	 * Returns the group, associated with the values.
	 *
	 * @since 3.0
	 *
	 * @return Group
	 */
	public function get_group() {
		return $this->group;
	}
}
