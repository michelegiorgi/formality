<?php
namespace Ultimate_Fields;

/**
 * Handles the boot of Ultimate Fields within Composer.
 *
 * @since 3.0
 */
class Composer {
	/**
	 * Boots Ultimate Fields.
	 *
	 * @since 3.0
	 *
	 * @link https://www.ultimate-fields.com/docs/quick-start/administration-interface/
	 *
	 * @param bool $ui Whether to include the user interface.
	 */
	public static function boot( $ui = true ) {
		Core::instance();

		if( $ui ) {
			UI\UI::instance();
		}
	}
}
