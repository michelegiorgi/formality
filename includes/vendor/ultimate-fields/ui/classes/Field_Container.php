<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Container;

/**
 * Handles the generation of field data.
 * 
 * @since 3.0
 */
class Field_Container extends Container {
	/**
	 * Creates a new instance of the container.
	 * 
	 * @since 3.0
	 * 
	 * @return Fields_Container
	 */
	public static function instance() {
		return new self( 'fields_editor' );
	}
}