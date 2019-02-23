<?php
namespace Ultimate_Fields\Helper;

/**
 * Handles the neccessary functionality for the value of complex fields in array mode.
 *
 * @since 3.0
 */
class Complex_Values extends Group_Values {
	/**
	 * Generates an appropriate iterator in order to let have_groups() work with the complex field.
	 *
	 * @since 3.0
	 *
	 * @return Groups_Iterator_Loop
	 */
	public function loop_mode() {
		return new Groups_Iterator_Loop( array( $this ) );
	}
}
