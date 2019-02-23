<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Options_Page;
use Ultimate_Fields\Controller\Options as Controller;
use Ultimate_Fields\Datastore\Options as Datastore;
use Ultimate_Fields\Datastore\Network_Options as Network_Datastore;
use Ultimate_Fields\Form_Object\Options as Form_Object;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Works as a location definition within options pages.
 *
 * @since 3.0
 */
class Options extends Location {
	use Customizable;
	
	/**
	 * Holds the admin page for the location.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $page;

	/**
	 * Holds the context of the meta box.
	 *
	 * @since 3.0
	 * @var string
	 */
	 protected $context = 'normal';

	/**
	 * Holds the priority of the meta box.
	 *
	 * @since 3.0
	 * @var string
	 */
	 protected $priority = 'high';

	 /**
	  * Indicates if the page was "locally" created.
	  *
	  * @since 3.0
	  * @var bool
	  */
	 protected $local_page = false;

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed   $page The page that will be used by the location. Can be a string ID or an Options_page.
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $page = null, $args = array() ) {
		$this->page = $this->prepare_page( $page );
		$this->check_args_for_customizer( $args );

		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
		parent::__construct( $args );
	}

	/**
	 * Prepares the page for the location.
	 *
	 * @param mixed $page Either an existing Options_Page object or its slug.
	 * 					  If the page does not already exist, it will be automatically created.
	 * @return Options_Page
	 */
	protected function prepare_page( $page ) {
		if( is_string( $page ) ) {
			// We got an ID, look for the page
			return Options_Page::get( $page );
		} elseif( is_array( $page ) && isset( $page[ 'id' ] ) && $existing = Options_Page::get( $page[ 'id' ] ) ) {
			// If a page with the ID has already been created, use it
			$this->local_page = true;
			return $existing;
		} elseif( is_array( $page ) ) {
			// Create a new page based on an array
			$this->local_page = true;
			return Options_Page::create( $page );
		} elseif( is_a( $page, Options_Page::class ) ) {
			// Use a proper page
			return $page;
		} else {
			// Nope
			return false;
		}
	}

	/**
	 * Generates a page based only on the current location.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Container $container The location the page is to be attached to.
	 * @return Ultimate_Fields\Options_Page         The newly created page.
	 */
	public function generate_page( $container ) {
		if( $existing = $this->prepare_page( $container->get_id() ) ) {
			return $this->page = $existing;
		} else {
			$this->local_page = true;

			return $this->page = Options_Page::create(array(
				'id'    => $container->get_id(),
				'title' => $container->get_title(),
			));
		}
	}

	/**
	 * Indicates if the pag ehas been created within the location or not.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_created_locally() {
		return $this->local_page;
	}

	/**
	 * Returns the options page for the location.
	 *
	 * @since 3.0
	 *
	 * @return Options_Page
	 */
	public function get_page() {
		if( is_string( $this->page ) ) {
			$this->page = Options_Page::get( $this->page );
		}

		return $this->page;
	}

	/**
	 * Returns an instance of the controller, which controls the location (posts).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Options
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Returns the context for the location/meta box.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Allows the context of the meta box.
	 *
	 * @since 3.0
	 *
	 * @param string $context The context.
	 * @return Ultimate_Fields\Location\Post_Meta
	 */
	public function set_context( $context ) {
		$this->context = $context;

		return $this;
	}

	/**
	 * Returns the priority for the location/meta box.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Allows the context of the meta box.
	 *
	 * @since 3.0
	 *
	 * @param string $priority The priority.
	 * @return Ultimate_Fields\Location\Post_Meta
	 */
	public function set_priority( $priority ) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Generates a datastore.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Datastore\Options
	 */
	public function create_datastore( $object = null ) {
		return $this->get_page() && 'network' == $this->get_page()->get_type()
			? new Network_Datastore
			: new Datastore;
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
		if( is_a( $source, Options_Page::class ) ) {
			return 'option';
		}

		if( ! is_a( $source, Data_Source::class ) ) {
			return false;
		}

		return 'option' == $source->type
			? 'option'
			: false;
	}

	/**
	 * Checks if the location works with a front-end forms object.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Form_Object $object The object to check.
	 * @return bool
	 */
	public function works_with_object( $object ) {
		return is_a( $object, Form_Object::class );
	}

	/**
	 * Checks if the location should be displayed in the customizer based on the current page.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function customizer_active_callback() {
		return true;
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

		if( ! $this->page ) {
			return $settings;
		}

		if( $this->local_page ) {
			$settings[ 'page' ] = $this->page->export();
		} else {
			$settings[ 'page' ] = $this->page->get_id();
		}

		# Export REST data
		$this->export_rest_data( $settings );

		# Export customizable data
		$this->export_customizable_data( $settings );

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
		if( isset( $args[ 'page' ] ) ) {
			$this->page = $this->prepare_page( $args[ 'page' ] );
		}
		
		# Check for the customizer
		$this->import_customizable_data( $args );
	}
}
