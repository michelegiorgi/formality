<?php
namespace Ultimate_Fields\UI\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the field for selecting other fields.
 *
 * @since 3.0
 */
class Field_Selector extends Field {
	/**
	 * Holds the type of fields that are allowed for selection.
	 *
	 * @since 3.0.2
	 * @var string[]
	 */
	protected $types = array();

	/**
	 * Allows a type to be selected in the field.
	 *
	 * @since 3.0.2
	 *
	 * @param string $type The field type.
	 * @return Field_Selector
	 */
	public function add_type( $type ) {
		$this->types[] = $type;
		return $this;
	}

	/**
	 * Removes a type from the selection.
	 *
	 * @since 3.0.2
	 *
	 * @param string $type The type to remove.
	 * @return Field_Selector
	 */
	public function remove_type( $type ) {
		$found = array_search( $type, $this->types );

		if( false !== $found ) {
			unset( $this->types[ $found ] );
			$this->types = array_values( $this->types );
		}

		return $this;
	}

	/**
	 * Exports the settings of the field for usage in JavaScript.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function export_field() {
		$data = parent::export_field();

		$data[ 'types' ] = $this->types;

		return $data;
	}
}
