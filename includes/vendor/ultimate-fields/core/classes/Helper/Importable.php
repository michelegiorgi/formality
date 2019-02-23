<?php
namespace Ultimate_Fields\Helper;

/**
 * Contains the functionality, necessary for importing objects from JSON/PHP.
 *
 * @since 3.0
 */
trait Importable {
	/**
	 * Proxies an array of settings to the appropriate setter.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]   $data    The data to proxy.
	 * @return string[] $methods The methods, which should be used for setting the data.
	 */
	public function proxy_data_to_setters( $data, $methods ) {
		foreach( $methods as $property => $method ) {
			if( isset( $data[ $property ] ) ) {
				call_user_func( array( $this, $method ), $data[ $property ] );
			}
		}
	}
}
