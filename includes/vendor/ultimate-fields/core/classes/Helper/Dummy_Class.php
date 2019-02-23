<?php
namespace Ultimate_Fields\Helper;

/**
 * This is a class, which will be returned by some generators
 * from within the plugin, that will indicate something missing and does nothing.
 *
 * @since 3.0
 */
class Dummy_Class {
	public function __set( $key, $value ) {
		// Nothing to set really
	}

	public function __get( $property ) {
		return null;
	}

	public function __call( $method_name, $args ) {
		return $this;
	}
}
