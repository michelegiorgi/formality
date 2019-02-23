<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Widget as Controller;
use Ultimate_Fields\Datastore\Widget as Datastore;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Works as a location definition for containers within widgets.
 *
 * @since 3.0
 */
class Widget extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'widget';

	/**
	 * The class names of widgets, which the location is associated with.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $widgets = array();

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param string[] $widgets The list of widgets to use the location with. All widgets when blank.
	 * @param mixed[]  $args    Additional arguments for the location.
	 */
	public function __construct( $widgets = array(), $args = array() ) {
		# Send all arguments to the appropriate setter.
		$this->arguments = $args;

		# Save the widgets
		$this->add_widget( $widgets );
	}

	/**
	 * Returns an instance of the controller, which controls the location (menu items).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Widget
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Adds a widget to the location.
	 *
	 * @since 3.0
	 *
	 * @param string $widget The class name(s) of the widget.
	 * @return Location\Widget
	 */
	public function add_widget( $widget ) {
		$this->widgets = array_merge( $this->widgets, (array) $widget );

		return $this;
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		if( ! empty( $this->widgets ) ) {
			$settings[ 'widgets' ] = $this->widgets;
		}

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param  [mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		if( isset( $args[ 'widgets' ] ) && ! empty( $args[ 'widgets' ] ) ) {
			$this->add_widget( $args[ 'widgets' ] );
		}

		$this->arguments = $args;
	}

	/**
	 * Returns the widgets, which the location is associated with.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public function get_widgets() {
		return $this->widgets;
	}

	/**
	 * Generates a datastore based on an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The post to create a datastore for.
	 * @return mixed
	 */
	public function create_datastore( $object ) {
		# This method will only be called in the front-end and
		# will apply to the current widget, so we can directly
		# use the static datastore method for the current widget.
		return Datastore::get_current_datastore();
	}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	public function works_with( $source ) {
		# Check for the attachment type
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		return is_a( $source, Data_Source::class ) && 'widget' == $source->type;
	}
}
