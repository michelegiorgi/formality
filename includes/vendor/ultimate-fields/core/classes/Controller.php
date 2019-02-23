<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Container;
use Ultimate_Fields\Location;
use Ultimate_Fields\Template;

/**
 * Works as a base for containers and their locations.
 *
 * @since 3.0
 */
abstract class Controller {
	/**
	 * Holds all combinations of containers and locations
	 * which are already added to the container.
	 *
	 * @since 3.0
	 */
	protected $combinations = array();

	/**
	 * Indicates if unique names have been checked.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $names_checked = false;

	/**
	 * Creates an instance of the called class.
	 *
	 * @since 3.0
	 *
	 * @return Controller
	 */
	public static function instance() {
		static $instances;

		if( is_null( $instances ) ) {
			$instances = array();
		}

		$class_name = get_called_class();

		if( ! isset( $instances[ $class_name ] ) ) {
			$instances[ $class_name ] = new $class_name;
		}

		return $instances[ $class_name ];
	}

	/**
	 * Adds a combination of a container and a location to the controller.
	 *
	 * @since 3.0
	 *
	 * @param Container $container The container that will be controlled.
	 * @param Location  $Location  The location where the container should be displayed.
	 */
	public function attach( Container $container, Location $location ) {
		$id = $container->get_id();

		# Add to an existing combination
		if( isset( $this->combinations[ $id ] ) ) {
			$this->combinations[ $id ][ 'locations' ][] = $location;
			return;
		}

		# Create a new combination
		$this->combinations[ $id ] = array(
			'container' => $container,
			'locations' => array( $location )
		);
	}

	/**
	 * Generates the error HTML for validation.
	 *
	 * @since 3.0
	 *
	 * @param string[] $errors The errors that should be displayed/generated.
	 * @return string
	 */
	public static function generate_error_html( $errors ) {
		$message = sprintf(
			'<h3>%s</h3>',
			__( 'Your data could not be saved because of the following reasons:', 'ultimate-fields' )
		);

		$message .= '<ul>';
		foreach( $errors as $error ) {
			$message .= '<li>' . wpautop( $error ) . '</li>';
		}
		$message .= '</ul>';

		return $message;
	}

	/**
	 * Combines all error messages and displays them as an error.
	 *
	 * @since 3.0
	 *
	 * @param string[] $errors The errors that should be displayed.
	 */
	protected function error( $errors ) {
		wp_die( self::generate_error_html( $errors ) );
	}

	/**
	 * Unboxes a boxed container.
	 *
	 * Call this method immediately within the meta box to remove it.
	 *
	 * @since 3.0
	 */
	protected function unbox() {
		Template::instance()->include_template( 'container/seamless-unboxer' );
	}

	/**
	 * Returns the HTML that would be used for displaying a no-js message.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_no_js_message() {
		return Template::instance()->include_template( 'container/no-js', array(), false );
	}

	/**
	 * Ensures unique field names.
	 *
	 * This method should be called before retrieving fields, to assure no conflicts.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $combinations The combinations to check (Optional).
	 * @param bool    $force        Whether to force the checks, even if they already happened.
	 */
	protected function ensure_unique_field_names( $combinations = false, $force = false ) {
		if( $this->names_checked && ! $force ) {
			return;
		}

		$names        = array();
		$combinations = $combinations ? $combinations : $this->combinations;

		foreach( $combinations as $combo ) {
			$container = $combo[ 'container' ];

			# Verify field names so far
			$container->reserve_names( $names );

			foreach( $container->get_fields() as $field ) {
				$names[] = $field->get_name();
			}
		}

		$this->names_checked = true;
	}
}
