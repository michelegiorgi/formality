<?php
namespace Ultimate_Fields\Helper;

use ArrayAccess;

/**
 * Allows callback to be assigned with arguments.
 *
 * @since 3.0
 */
class Callback implements ArrayAccess {
	/**
	 * Holds the real callback, which will be executed.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $callback;

	/**
	 * Holds all settings for the callback.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $settings;

	/**
	 * Creates the callback class.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to be executed.
	 */
	public function __construct( $callback, $settings = array() ) {
		$this->callback = $callback;
		$this->settings = $settings;
	}

	/**
	 * This function should be executed instead of the classical callback.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function callback() {
		# Push this class in the beginning of the arguments
		$args = func_get_args();
		array_unshift( $args, $this );

		# Call the internal callback and return its result
		return call_user_func_array( $this->callback, $args );
	}

	/**
	 * Sets a value at a certain offset.
	 *
	 * @since 3.0
	 *
	 * @param scalar $offset The offset for the value.
	 * @param mixed  $value  The value to set.
	 */
	public function offsetSet( $offset, $value ) {
		if( is_null( $offset ) ) {
			$this->settings[] = $value;
		} else {
			$this->settings[ $offset ] = $value;
		}
	}

	/**
	 * Checks if there is a value at a certain offset.
	 *
	 * @since 3.0
	 *
	 * @param sclar $offset The offset to check.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->settings[ $offset ] );
	}

	/**
	 * Unsets the value at a certain offset.
	 *
	 * @since 3.0
	 *
	 * @param sclar $offset The offset to check.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->settings[ $offset ] );
	}

	/**
	 * Returns the value at a specific offset.
	 *
	 * @since 3.0
	 *
	 * @param scalar $offset The offset of the value to retrieve.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return isset( $this->settings[ $offset ] )
			? $this->settings[ $offset ]
			: null;
	}

	/**
	 * Returns a handle to the externally-accessible callback.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_callback() {
		return array( $this, 'callback' );
	}
}
