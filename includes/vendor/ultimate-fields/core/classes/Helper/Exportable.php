<?php
namespace Ultimate_Fields\Helper;

/**
 * Contains the functionality, necessary for exporting objects to JSON/PHP.
 *
 * @since 3.0
 */
trait Exportable {
	/**
	 * Exports the internal properties of the object.
	 *
	 * @since 3.0
	 *
	 * @param  mixed[] $json       A JSON-ready array where data should be dumped.
	 * @param  mixed[] $properties The properties to export as internal => [ external, default ]
	 */
	protected function export_properties( &$json, $properties )  {
		foreach( $properties as $property => $target ) {
			$export_name   = $target[ 0 ];
			$default_value = isset( $target[ 1 ] ) ? $target[ 1 ] : null; 

			if( $this->$property != $default_value ) {
				$json[ $export_name ] = $this->$property;
			}
		}
	}
}
