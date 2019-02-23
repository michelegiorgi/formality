<?php
namespace Ultimate_Fields\UI\Settings;

/**
 * Handles a single screen on the settings page of UF.
 *
 * @since 3.0
 */
abstract class Screen {
	/**
	 * Holds the URL of the screen.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $url;

	/**
	 * Indicates if the screen is currently active.
	 *
	 * @since 3.0
	 * @var bool
	 */
	public $active = false;

	/**
	 * Checks if the screen is available.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_available() {
		return true;
	}
}
