<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Field;
use Ultimate_Fields\Data_API;
use Ultimate_Fields\Controller\Customizer;
use Ultimate_Fields\Helper\Exportable;
use Ultimate_Fields\Helper\Importable;
use Ultimate_Fields\Location\Customizable;
use Ultimate_Fields\Fields_Collection;
use Ultimate_Fields\Template;
use ReflectionClass;

/**
 * Servers as a base for containers.
 *
 * @since 3.0
 */
class Container {
	use Exportable, Importable;

	/**
	 * This will hold an array of all registered containers.
	 *
	 * @since 3.0
	 * @var Container[]
	 */
	protected static $registered_containers = array();

	/**
	 * Holds the ID of the container.
	 *
	 * The ID of a container should be sanitized, including only characters that
	 * can be used as (x)HTML attributes, included in URLs and etc.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $id = '';

	/**
	 * Holds the title of the container.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * Holds the description of the container.
	 *
	 * The description of a container may appear on various places in the admin,
	 * but it will always be positioned before the fields, allowing the user to know
	 * what the fields in the container will be about.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * Holds the fields of the container.
	 *
	 * The fields here must extend the UF_Field class.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Field[]
	 */
	protected $fields;

	/**
	 * Holds the datastore that will be used when saving the values of this container.
	 *
	 * @see class Ultimate_Fields\Datastore
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore
	 */
	protected $datastore = null;

	/**
	 * Holds the description position of the container (input|label).
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $description_position = 'input';

	/**
	 * This indicates the layout of the container. Either 'rows' or 'grid'.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $layout = 'rows';

	/**
	 * Holds the style of the container.
	 *
	 * Can be one of the following:
	 * - 'boxed' for boxed layout.
	 * - 'seamless' for non-boxed layout.
	 * - 'auto' for automatic location-based default.
	 *
	 * This is a recommended setting, meaning that it might be ignored by containers.
	 * For example, the Widget container cannot be seamless and the Shortcode cannot be boxed.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $style = 'auto';

	/**
	 * For performance reasons, some container might not have their
	 * fields generated until a later stage, when they're actually needed.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $fields_callback;

	/**
	 * Holds all locations, where the container is added to.
	 *
	 * @since 3.0
	 * @var Location[]
	 */
	protected $locations = array();

	/**
	 * This array will contain field names, which are reserved within some of the contexts of the container.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $reserved_names = array();

	/**
	 * Holds the roles, which are required to display the container.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $roles = array();

	/**
	 * Holds the capabilities, which are required for the container to work.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $capabilities = array();

	/**
	 * Creates a new container.
	 *
	 * @since 3.0
	 *
	 * @param string  $id   The ID of the container.
	 * @param mixed[] $args Additional arguments for the container.
	 *
	 * @return Container A new cotainer instance.
	 */
	public static function create( $id, $args = array() ) {
		$class_name = get_called_class();
		return new $class_name( $id, $args );
	}

	/**
	 * Returns an array of all registered containers.
	 *
	 * @since 3.0
	 *
	 * @return Container[]
	 */
	public static function get_registered() {
		return self::$registered_containers;
	}

	/**
	 * Initializes basic class properties.
	 *
	 * @since 3.0
	 *
	 * @param string  $id   The ID of the container.
	 * @param mixed[] $args All arguments, needed for the container.
	 */
	public function __construct( $id, $args = array() ) {
		$this->id = sanitize_title( $id );

		if( isset( $args[ 'title' ] ) ) {
			$this->title = ultimate_fields()->generate_title( $id, $args[ 'title' ] );
		} else{
			$this->title = ultimate_fields()->generate_title( $id );
		}

		$this->fields = new Fields_Collection;

		if( ! is_a( $this, Container\Group::class ) ) {
			self::$registered_containers[ $this->id ] = $this;
		}

		if( isset( $args[ 'layout' ] ) ) {
			$this->set_layout( $args[ 'layout' ] );
		}

		if( isset( $args[ 'description' ] ) ) {
			$this->set_description( $args[ 'description' ] );
		}

		if( isset( $args[ 'description_position' ] ) ) {
			$this->set_description_position( $args[ 'description_position' ] );
		}
	}

	public function set_description( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Returns the description of the container.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Adds a location to the container.
	 *
	 * @since 3.0
	 *
	 * @param string|Location $type Either a string type or an existing location.
	 * @return Container            The instnace of the container.
	 */
	public function add_location( $type ) {
		# Either create the location or use whats already existing
		if( ! is_a( $type, Location::class ) ) {
			$location = call_user_func_array( array( Location::class, 'create' ), func_get_args() );
		} else {
			$location = $type;
		}

		# save the location locally
		$this->locations[] = $location;

		# Don't attachm if the current user is wrong
		if( ! $this->check_user() ) {
			return $this;
		}

		# Link the location to a controller
		$controller = $location->get_controller();
		$controller->attach( $this, $location );

		# Eventually add to the customizer
		$traits = class_uses( $location );
		if( in_array( Customizable::class, $traits ) && $location->is_shown_in_customizer() ) {
			Customizer::instance()->attach( $this, $location );
		}

		return $this;
	}

	/**
	 * Returns all of the containers' locations.
	 *
	 * @since 3.0
	 *
	 * @return Location[]
	 */
	public function get_locations() {
		return $this->locations;
	}

	/**
	 * Exports the settings for fields.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_fields_settings() {
		$fields      = array();
		$tab         = false;

		# Go through each field and prepare it
		foreach( $this->get_fields() as $field ) {
			if( is_a( $field, Field\Tab::class ) ) {
				$tab = $field->get_name();
			} elseif( $tab ) {
				$field->set_tab( $tab );
			}

			$fields[] = $field->export_field();
		}

		return $fields;
	}

	/**
	 * Exports the settings of the container.
	 */
	public function export_settings() {
		# This is the big array
		$settings = array(
			'id'                   => $this->id,
			'title'                => $this->title,
			'description'          => wpautop( $this->description ),
			'fields'               => $this->export_fields_settings(),
			'layout'               => $this->layout,
			'style'                => $this->style,
			'description_position' => $this->description_position
		);

		return $settings;
	}

	public function export_data() {
		$data   = array();

		# Go through each field and prepare it
		foreach( $this->get_fields() as $field ) {
			$field->set_datastore( $this->datastore );
			$data = array_merge( $data, $field->export_data() );

			# If a tab field, set as the first tab
			if( is_a( $field, Field\Tab::class ) && ! isset( $data[ '__tab' ] ) ) {
				$data[ '__tab' ] = $field->get_name();
			}
		}

		return $data;
	}

	public function add_fields( $fields = array() ) {
		foreach( $fields as $field ) {
			$this->add_field( $field );
		}

		return $this;
	}

	/**
	 * While add_fields() can be called diectly and it might not have
	 * a big performance impact, sometimes the generation process can
	 * be slow, especially if a lot of data is being read out. Providing
	 * a fields callback through this method will avoid heavy computational
	 * work if not needed.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback
	 * @return Container The instance of the container.
	 */
	public function set_fields_callback( $callback ) {
		if( isset( $_REQUEST[ 'uf_action' ] ) ) {
			$this->fields->merge_with( call_user_func( $callback ) );
		} else {
			$this->fields_callback = $callback;
		}

		return $this;
	}

	public function add_field( $field ) {
		// Just go on if a dummy has been supplied
		if( is_a( $field, Helper\Dummy_Class::class ) ) {
			return $this;
		}

		if( ! is_a( $field, Field::class ) ) {
			$field = Field::create_from_array( $field );
		}

		# Check if the field already exists
		if( isset( $this->fields[ $field->get_name() ] ) || in_array( $field->get_name(), $this->reserved_names ) ) {
			$field = $this->get_replacement_field( $field );
		}

		$this->fields[] = $field;

		# If the field is a tab and has a description, add a message field with it
		if( is_a( $field, Field\Tab::class ) && $field->get_description() ) {
			$this->fields[] = Field::create( 'message', $field->get_name() . '_message' )
				->hide_label()
				->set_description( $field->get_description() );
		}

		return $this;
	}

	/**
	 * Generates a replacement field for a field, whose name is already in use.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field we need a replacement for.
	 * @return Ultimate_Fields\Field A replacement field.
	 */
	protected function get_replacement_field( $field ) {
		$replacement_name = $field->get_name();

		while( isset( $this->fields[ $replacement_name ] ) || in_array( $replacement_name, $this->reserved_names ) ) {
			$replacement_name .= '_repl';
		}

		$html = Template::instance()->include_template( 'field/existing-key', array(
			'name'  => $field->get_name(),
			'label' => $field->get_label()
		), false );

		return Field::create( 'message', $replacement_name, $field->get_label() )
			->set_description( $html )
			->set_attr( 'class', 'uf-existing-message' );
	}

	/**
	 * Saves an array of field names, which are not available to use.
	 *
	 * @since 3.0
	 *
	 * @param string $names The names to reserve.
	 */
	public function reserve_names( $names ) {
		$this->reserved_names = array_merge( $this->reserved_names, $names );

		# Go through existing field and check them
		$fields = array();
		foreach( $this->get_fields() as $field ) {
			if( ! in_array( $field->get_name(), $names ) ) {
				$fields[] = $field;
			} else {
				$fields[] = $this->get_replacement_field( $field );
			}
		}

		/**
		 * Todo: Create a replace method within the collection
		 */
		$this->fields = new Fields_Collection( $fields );
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id( $id ) {
		$old_id   = $this->id;
		$this->id = $id;

		if( ! is_a( $this, Container\Group::class ) ) {
			unset( self::$registered_containers[ $old_id ] );
			self::$registered_containers[ $this->id ] = $this;
		}

		return $this;
	}

	public function set_title( $title ) {
		$this->title = $title;
		return $this;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_fields() {
		# If there is a fields callback, use it.
		if( ! is_null( $this->fields_callback ) && is_callable( $this->fields_callback ) ) {
			$this->fields->merge_with( call_user_func( $this->fields_callback ) );

			$this->fields_callback = null;
		}

		return $this->fields;
	}

	public function get_field( $name ) {
		foreach( $this->get_fields() as $field ) {
			if( $name == $field->get_name() ) {
				return $field;
			}
		}

		return false;
	}

	public function set_datastore( $datastore ) {
		$this->datastore = $datastore;

		if( ! $datastore ) {
			return $this;
		}

		foreach( $this->get_fields() as $field ) {
			$field->set_datastore( $datastore );
		}

		return $this;
	}

	public function get_datastore() {
		return $this->datastore;
	}

	public function grid_layout() {
		$this->layout = 'grid';

		return $this;
	}

	public function rows_layout() {
		$this->layout = 'rows';

		return $this;
	}

	public function set_layout( $layout ) {
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Reutrns the current layout of the container.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_layout() {
		return $this->layout;
	}

	/**
	 * Allows the style of the container to be changed.
	 *
	 * @since 3.0
	 * @see The 'style' property above.
	 *
	 * @param string $style The style to use.
	 * @return Container
	 */
	public function set_style( $style ) {
		$this->style = $style;

		return $this;
	}

	/**
	 * Returns the style of the container.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_style() {
		return $this->style;
	}

	/**
	 * Sets the description position within the container.
	 *
	 * @since 3.0
	 *
	 * @param string $position The position, either 'label' or 'input'.
	 * @return Ultimate_Fields\Container The instance of the container.
	 */
	public function set_description_position( $position ) {
		$this->description_position = $position;

		return $this;
	}

	/**
	 * Instruct the container to require a certain role in order to reveal fields.
	 *
	 * @since 3.0
	 *
	 * @param string A single role or an array of roles.
	 * @return Ultimate_Fields\Container The instance of the container.
	 */
	public function require_role( $role ) {
		$this->roles = array_merge(
			$this->roles,
			(array) $role
		);

		return $this;
	}

	/**
	 * Instruct the container to require a certain capability in order to reveal fields.
	 *
	 * @since 3.0
	 *
	 * @param string A single capability or an array of capabilities.
	 * @return Ultimate_Fields\Container The instance of the container.
	 */
	public function require_capability( $capability ) {
		$this->capabilities = array_merge(
			$this->capabilities,
			(array) $capability
		);

		return $this;
	}

	/**
	 * Checks if a user can work with the container.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function check_user( $user = null ) {
		# No requirements means the container is shown in any context, as long as the context itself is shown.
		if( empty( $this->roles ) ) {
			return true;
		}

		# Try working with the current user
		if( ! $user )
			$user = wp_get_current_user();

		# If there is no user (even a logged-in one, bail)
		if( ! $user )
			return false;

		# Check capabilities
		foreach( $this->capabilities as $capability ) {
			if( user_can( $user, $capability ) ) {
				return true;
			}
		}

		# Check roles
		foreach( $this->roles as $role ) {
			if( in_array( $role, $user->roles ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the description position of the container.
	 *
	 * @since 3.0
	 *
	 * @return string The position, either 'label' or 'input'.
	 */
	public function get_description_position() {
		return $this->description_position;
	}

	/**
	 * Checks if the container can handle a certain value.
	 *
	 * @since 3.0
	 *
	 * @param  mixed[] $source The source/context of the value.
	 * @return string          The name of the value to get from the datastore.
	 */
	public function can_handle( $source ) {
		foreach( $this->get_fields() as $field ) {
			# Check the field and see if it needs a different value
			if( $res = $field->can_handle( $source ) ) {
				return true === $res
					? $source->name
					: $res;
			}
		}

		return false;
	}

	/**
	 * Handles a value that was retrieved from the database.
	 *
	 * If the container has a location, compatible with the source, the value will be sent to
	 * the field that works with that value, in order to generate proper output.
	 *
	 * For example, when a repeater is used, this method will change a simple array into a Groups_Iterator.
	 * Also, if the field has a default value, it will be used.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value The value to handle.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source of the value.
	 * @return mixed
	 */
	public function handle( $value, $source ) {
		foreach( $this->get_fields() as $field ) {
			if( ! $field->can_handle( $source ) ) {
				continue;
			}

			$value = $field->handle( $value, $source );
		}

		return $value;
	}

	public function process( $value, $source ) {
		foreach( $this->get_fields() as $field ) {
			if( ! $field->can_handle( $source ) )
				continue;

			$value = $field->process( $value, $source );
		}

		return $value;
	}

	/**
	 * Generates an array, which can be exported to both PHP and JSON.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] The JSON-ready data.
	 */
	public function export() {
		$settings = array(
			'type' => 'container'
		);

		$this->export_properties( $settings, array(
			'id'                   => array( 'id' ),
			'title'                => array( 'title' ),
			'layout'               => array( 'layout', 'auto' ),
			'description'          => array( 'description', '' ),
			'description_position' => array( 'description_position', 'input' ),
			'layout'               => array( 'layout', 'auto' ),
			'style'                => array( 'style', 'boxed' ),
			'roles'                => array( 'roles', array() )
		));

		$settings[ 'locations' ] = array();
		foreach( $this->get_locations() as $location ) {
			$settings[ 'locations' ][] = $location->export();
		}

		$settings[ 'fields' ] = array();
		foreach( $this->get_fields() as $field ) {
			$settings[ 'fields' ][] = $field->export();
		}

		return $settings;
	}

	public static function create_from_array( $data ) {
		$container = self::create( $data[ 'title' ] );
		$container->import( $data );
		return $container;
	}

	public function import( $data ) {
		if( isset( $data[ 'id' ] ) )
			$this->set_id( $data[ 'id' ] );

		if( isset( $data[ 'title' ] ) )
			$this->set_title( $data[ 'title' ] );

		if( isset( $data[ 'description' ] ) )
			$this->set_description( $data[ 'description' ] );

		if( isset( $data[ 'description_position' ] ) )
			$this->set_description_position( $data[ 'description_position' ] );

		if( isset( $data[ 'locations' ] ) ) {
			foreach( $data[ 'locations' ] as $location ) {
				$this->add_location( Location::create_from_array( $location ) );
			}
		}

		if( isset( $data[ 'fields' ] ) ) {
			foreach( $data[ 'fields' ] as $f ) {
				$field = Field::create( $f[ 'type' ], $f[ 'name' ], $f[ 'label' ] );
				$field->import( $f );
				$this->add_field( $field );
			}
		}

		if( isset( $data[ 'layout' ] ) ) {
			$this->set_layout( $data[ 'layout' ] );
		}

		if( isset( $data[ 'style' ] ) ) {
			$this->set_style( $data[ 'style' ] );
		}

		if( isset( $data[ 'roles' ] ) ) {
			$this->require_role( $data[ 'roles' ] );
		}
	}

	public function save( $data ) {
		$errors = array();

		# Set the datastore and force values into it
		foreach( $this->get_fields() as $field ) {
			if( ! isset( $data[ $field->get_name() ] ) ) {
				continue;
			}

			$field->set_datastore( $this->datastore );
			$field->save( $data );
		}

		# Generates an array of tab validities
		$tabs = array();
		$tab  = false;
		foreach( $this->get_fields() as $field ) {
			if( is_a( $field, Field\Tab::class ) ) {
				$tab = $field->get_name();
				$tabs[ $field->get_name() ] = ! $field->is_hidden();
			} elseif( $tab ) {
				$field->set_tab( $tab );
			}
		}

		# Validate fields now that conditional logic is available
		foreach( $this->get_fields() as $field ) {
			if( is_a( $field, Field\Tab::class ) ) {
				continue;
			}

			# Check if the tab of the field field is visible
			$tab = $field->get_tab();
			if( $tab && ( ! isset( $tabs[ $tab ] ) || ! $tabs[ $tab ] ) ) {
				continue;
			}

			$validation_message = $field->validate();

			if( is_string( $validation_message ) ) {
				$errors[ $field->get_name() ] = $validation_message;
			}
		}

		return $errors;
	}

	/**
	 * Enqueues the scripts for the containers' scripts.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		foreach( $this->get_fields() as $field ) {
			if( method_exists( $field, 'enqueue_scripts' ) ) {
				$field->enqueue_scripts();
			}
		}

		ultimate_fields()
			->localize( 'container-issues',        __( 'Your data cannot be saved because it contains the following issues. Please resolve them and try again:', 'ultimate-fields' ) )
			->localize( 'error-corrections',       __( 'Please correct those errors and try saving the field again.', 'ultimate-fields' ) )
			->localize( 'container-issues-title',  __( 'There seem to be some issues with your settings:', 'ultimate-fields' ) )
			->localize( 'invalid-field-message',   __( 'The value of %s is not valid!', 'ultimate-fields' ) )
			;
	}

	/**
	 * Checks if the container works with a certain forms object.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Form_Object $object The object to check for compatability with.
	 * @return bool
	 */
	public function works_with( $object ) {
		foreach( $this->locations as $location ) {
			if( $location->works_with_object( $object ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Tells the existing locations to expose some of their fields.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $fields The fields to expose.
	 */
	public function expose( $fields ) {
		foreach( $this->locations as $location ) {
			$location->expose_api_fields( $fields );
		}

		return $this;
	}

	/**
	 * Lets a field update a value.
	 *
	 * @since 3.0
	 *
	 * @param string $name  The name of the field/value to update.
	 * @param mixed  $value The new value to set.
	 * @return bool
	 */
	public function update_value( $name, $value ) {
		$errors = $this->save(array(
			$name => $value
		));

		if( empty( $errors ) ) {
			$this->datastore->commit();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Performs an AJAX call based on a specific item.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is being changed.
	 */
	public function perform_ajax( $item, $action ) {
		foreach( $this->get_locations() as $location ) {
			$applicable = false;

			if( is_null( $item ) && class_uses( $location, Customizable::class ) && $location->is_shown_in_customizer() && isset( $GLOBALS[ 'wp_customize' ] ) ) {
				$applicable = true;
			}

			if( ! $applicable && $location->works_with( $item ) ) {
				$applicable = true;
			}

			if( ! $applicable ) {
				continue;
			}

			# We have a match!
			foreach( $this->get_fields() as $field ) {
				$field->perform_ajax( $action, $item );
			}
		}
	}

	/**
	 * Containers can be cloned for various purposes (ex. similar functionality in the front-end).
	 *
	 * This will return a new container with the same properties and the same fields,
	 * but in a different collection. Also, all locations will be stripped.
	 *
	 * @since 3.0
	 */
	public function __clone() {
		$this->locations = array();
		$this->fields    = clone $this->fields;
	}

	/**
	 * Allows a location rule to be added directly to the first location of the container.
	 *
	 * @since 3.0
	 *
	 * @param string $rule  The name of the rule to set.
	 * @param mixed  $value The value to apply to the new rule.
	 * @return Container
	 */
	public function add_location_rule( $rule, $value ) {
		if( ! empty( $this->locations ) ) {
			$this->locations[ 0 ]->__set( $rule, $value );
			return $this;
		}

		throw new \Exception( 'Location rules cannot be added before adding a location!' );
	}
}
