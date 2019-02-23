<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Handles the values of widgets.
 *
 * @since 3.0
 */
class Widget extends Datastore {
	/**
	 * Saves information about the current widget, if any.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore\Widget
	 */
	protected static $current_widget;

	/**
	 * Short-circuits databse connections.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
		return null;
	}

	/**
	 * Returns the internal values of the datastore.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_values() {
		return $this->values;
	}

	/**
	 * Returns the options and keywords for the data API.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_data_api_options() {
		$options = array();

		# This option will select the current widget
		$options[] = array(
			'type'    => 'widget',
			'keyword' => 'widget',
			'item'    => false
		);

		return $options;
	}

	/**
	 * Saves the data about the widget that is currently displayed in the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $instance The instance of the current widget.
	 */
	public static function set_current_widget( $instance ) {
		self::$current_widget = new self( $instance );
	}

	/**
	 * Returns either the datastore for the current widget or a blank one.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Dtastore\Widget;
	 */
	public static function get_current_datastore() {
		return self::$current_widget
			? self::$current_widget
			: new self;
	}
}
