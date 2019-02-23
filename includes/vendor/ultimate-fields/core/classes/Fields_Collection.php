<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Field;
use ArrayAccess;
use Iterator;
use Countable;

/**
 * Handles collections of fields as extended arrays.
 *
 * @since 3.0
 */
class Fields_Collection implements ArrayAccess, Iterator, Countable {
	/**
	 * Holds all fields, which are stored in the collection.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Field[]
	 */
	protected $fields = array();

	/**
	 * Holds the current index of the iterator.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $i;

	/**
	 * Holds a map, which allows quick access to fields by name.
	 *
	 * @since 3.0
	 *
	 * @var Ultimate_Fields\Field[]
	 */
	protected $map = array();

	/**
	 * Initializes the collection with elements.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field[] $fields A basic array of fields.
	 */
	public function __construct( $fields = array() ) {
		foreach( $fields as $field ) {
			# Allow false fields to be added, but ignored (inline ifs)
			if( ! $field || is_a( $field, Helper\Dummy_Class::class ) ) {
				continue;
			}

			if( is_a( $field, get_class() ) ) {
				foreach( $field->export() as $sub_field ) {
					$this->fields[] = $sub_field;
					$this->map[ $sub_field->get_name() ] = $sub_field;
				}
			} else {
				$this->fields[] = $field;
				$this->map[ $field->get_name() ] = $field;
			}
		}
	}

	/**
	 * Adds a new field to the collection.
	 *
	 * @since 3.0
	 *
	 * @param string    $name  The name of the field.
	 * @param Ultimate_Fields\Field $field The field that is being added.
	 */
	public function offsetSet( $name, $field ) {
		$name = $field->get_name();

		if( isset( $this->map[ $name ] ) ) {
			$existing = $this->map[ $name ];

			$this->fields[ array_search( $existing, $this->fields ) ] = $field;
			$this->map[ $name ] = $field;
		} else {
			$this->fields[]     = $field;
			$this->map[ $name ] = $field;
		}
	}

	/**
	 * Checks if a certain offset exists.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the field that is needed.
	 * @return bool
	 */
	public function offsetExists( $name ) {
		return isset( $this->map[ $name ] );
	}

	/**
	 * Unsets an offset.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the field which is to be removed from the array.
	 */
	public function offsetUnset( $name ) {
		$field = $this->map[ $name ];

		unset( $this->map[ $name ] );
		unset( $this->fields[ array_search( $field, $this->fields ) ] );
		$this->fields = array_values( $this->fields );
	}

	/**
	 * Returns the field with a specific name.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the field.
	 * @return Ultimate_Fields\Field
	 */
	public function offsetGet( $name ) {
		return isset( $this->map[ $name ] ) ? $this->map[ $name ] : null;
	}

	/**
	 * Exports the linear array of fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	public function export() {
		return $this->fields;
	}

	/**
	 * Rewinds the collection to it's beginning.
	 *
	 * @since 3.0
	 */
	public function rewind() {
	   $this->i = 0;
   }

	/**
	 * Returns the current position.
	 *
	 * @since 3.0
	 *
	 * @return Field
	 */
	public function current() {
		return $this->fields[ $this->i ];
	}

	/**
	 * Returns the current iterator key.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function key() {
		return $this->i;
	}

	/**
	 * Goes to the next iterator position.
	 *
	 * @since 3.0
	 */
	public function next() {
		++$this->i;
	}

	/**
	 * Checks if there is a field at the current position.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function valid() {
		return isset( $this->fields[ $this->i ] );
	}

	/**
	 * Returns debug information as in just fields.
	 *
	 * @since 3.0
	 *
	 * @return Field[]
	 */
	public function __debugInfo() {
		return $this->fields;
	}

	/**
	 * Merges the array with more fields.
	 *
	 * @since 3.0
	 *
	 * @param array $fields The fields to be merged.
	 */
	public function merge_with( $fields ) {
		foreach( $fields as $field ) {
			$this[] = $field;
		}
	}

	/**
	 * Filters the collection based on a callback.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to filter with.
	 * @param bool     $replace  Whether to replace the internal fields or just return a new collection.
	 * @return Ultimate_Fields\Fields_Collection
	 */
	public function filter( $callback, $replace = false ) {
		$fields = array();

		foreach( $this->fields as $field ) {
			$keep = call_user_func( $callback, $field );

			if( $keep && ! $replace )
				$fields[] = $field;

			if( ! $keep && $replace )
				$this->offsetUnset( $field->get_name() );
		}

		return $replace ? $this : new self( $fields );
	}

	/**
	 * Rewind when cloning.
	 *
	 * @since 3.0
	 */
	public function __clone() {
		$this->rewind();
	}

	/**
	 * Adds a field to the beginning of the collection.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field to add.
	 */
	public function unshift( $field ) {
		$this->map[ $field->get_name() ] = $field;
		array_unshift( $this->fields, $field );

		return $this;
	}

	/**
	 * Appends a field to the collection.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field to add.
	 */
	public function push( $field ) {
		$this[] = $field;
		return $this;
	}

	/**
	 * Returns the count of fields in the collection.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->fields );
	}
}
