<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Exportable;
use Ultimate_Fields\Helper\Importable;
use Ultimate_Fields\Dependency\Set as Dependency_Set;
use Ultimate_Fields\Dependency\Group as Dependency_Group;
use Ultimate_Fields\Dependency\Rule as Dependency_Rule;

/**
 * Works as a base for fields.
 *
 * @since 3.0
 */
abstract class Field {
	use Exportable, Importable;

	/**
	 * Holds the name of the field.
	 * This name will be used for stroing and retrieving data to and from the DB.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $name;

	/**
	 * This is the label of the field, as will be displayed to the user.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $label;

	/**
	 * Holds the datastore for the field.
	 *
	 * Datastores are adapters that connect the field with the database throgh
	 * the appropriate meta/option functions. The value of the field will only
	 * be stored within this datastore, as the field has no direct DB access.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore
	 */
	protected $datastore;

	/**
	 * Holds groups of dependencies.
	 *
	 * Dependencies are rules, which apply to are matched to other values
	 * within the same datastore. They allow a field to appear based on those values.
	 *
	 * @see Ultimate_Fields\Dependency\Rule
	 * @var Ultimate_Fields\Dependency\Set
	 */
	protected $dependencies;

	/**
	 * Holds the name of the tab the field belongs to, if any.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $tab = '';

	/**
	 * Holds the default value of the field, which will be used if nothing
	 * is stored in the database.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $default_value = '';

	/**
	 * Indicates if the label of the field should be visible.
	 *
	 * @since 3.0
	 * @var boolean
	 */
	protected $hide_label = false;

	/**
	 * This is the width of the field in percent. Will only be applied within
	 * containers with grid layouts.
	 *
	 * @since 3.0
	 * @var integer
	 */
	protected $field_width = 100;

	/**
	 * The description of the field can be used for instructions, which will be
	 * shown to the user either below the field or after its label.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * Holds attributes, which will be applied to the fields' wrapper element.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $html_attributes = array();

	/**
	 * Indicates if the field is required or not.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Holds a regex validation rule for the field.
	 *
	 * By default, there are no rules and when a field is required, it's
	 * simply required to have a non-false value. However, you can enter
	 * a custom validation rule, which will not only be used when validation
	 * a required field, but even if the field is not required, whenever there is a value.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $validation_rule;

	/**
	 * This is the validation message for the field.
	 *
	 * JavaScript generates a normal error message, but you can use
	 * this to overwrite it and display something custom/more suitable.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $validation_message;

	/**
	 * Holds a validation callback if there is one.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $validation_callback;

	/**
	 * Holds a sanitization callback, if one is needed.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $sanitization_callback;

	/**
	 * Creates a new field based on type, name and eventually label.
	 *
	 * @param  string $type  The lowercase basename of the field (ex. 'text')
	 * @param  string $name  The name of the field, as it will be stored in the DB.
	 * @param  string $label The label of the field. Generated fromt he name by default. (Optional)
	 * @return Field     The newly generated field.
	 */
	public static function create( $type, $name, $label = null ) {
		/**
		 * Allows the class name that is used for a field type to be changed.
		 *
		 * @since 3.0
		 *
		 * @param string $class_name The class name to use, initially null.
		 * @param string $type       The requested type (ex. `text`).
		 * @return string
		 */
		$class_name = apply_filters( 'uf.field.class', null, $type );

		if( is_null( $class_name ) ) {
			$class_name = ultimate_fields()->generate_class_name( "Field/$type" );
		}

		if( ! class_exists( $class_name ) ) {
			Helper\Missing_Features::instance()->report( $class_name, 'field' );
			return new Helper\Dummy_Class;
		}

		return new $class_name( $name, $label );
	}

	/**
	 * Generates a new field based on an array.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field The field data/array.
	 * @return Ultimate_Fields\Field
	 */
	public static function create_from_array( $data ) {
		$field = Field::create( $data[ 'type' ], $data[ 'name' ], $data[ 'label' ] );
		$field->import( $data );

		return $field;
	}

	/**
	 * Creates a field instance.
	 *
	 * This method will save some basic properties and allow children to
	 * gather and set their own data up.
	 *
	 * @param string $name  The name of the field, which will be used for saving data.
	 * @param string $label A label for the field. If omitted, a label will be generated automatically.
	 */
	public function __construct( $name, $label = null ) {
		$this->name = $name;
		$this->label = ultimate_fields()->generate_title( $name, $label );

		# Let inheriting classes set themselves up
		if( method_exists( $this, '__constructed' ) ) {
			$this->__constructed();
		}
	}

	public function set_datastore( Datastore $datastore ) {
		$this->datastore = $datastore;
	}

	/**
	 * Returns the current value of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed Either a value if available, or false.
	 */
	public function get_value() {
		if( $this->datastore ) {
			return $this->datastore->get( $this->name );
		} else {
			return false;
		}
	}

	/**
	 * Exports the field's data (value).
	 *
	 * This method will return everything, which should be sent to the JS UI.
	 * This may include additional values, in case something more than the raw
	 * value of the field is being needed, in order to avoid extra AJAX queries.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] An array, which should be merged with the rest of the
	 *                 values of a container.
	 */
	public function export_data() {
		$value = $this->get_value();

		if( is_null( $value ) ) {
			// $value = $this->default_value;
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Returns all keys, which could be stored in the datastore for the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_datastore_keys() {
		return array( $this->name );
	}

	/**
	 * Exports the settings of the field.
	 *
	 * This method will export data for JavaScript, don't confuse with `export`,
	 * which will export the field for external saving.
	 *
	 * Please return only generic field settings here, don't include any data.
	 * In some instances (ex. widgets), this method will be called only once,
	 * no matter how many widgets exist. The method, which processes the data is
	 * `export_data()` from above.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] The execution settings of the field.
	 */
	public function export_field() {
		$type = method_exists( $this, 'get_type' ) ? $this->get_type() : get_class( $this );
		$type = basename( str_replace( '\\', '/', $type ) );

		$data = array(
			'type'       => $type,
			'name'       => $this->name,
			'label'      => $this->label,
		);

		if( $this->tab )                $data[ 'tab' ]                = $this->tab;
		if( $this->required )           $data[ 'required' ]           = $this->required;
		if( $this->validation_message ) $data[ 'validation_message' ] = $this->validation_message;
		if( $this->validation_rule )    $data[ 'validation_rule' ]    = $this->validation_rule;
		if( $this->hide_label )         $data[ 'hide_label' ]         = true;
		if( $this->field_width != 100 ) $data[ 'field_width' ]        = $this->field_width;
		if( $this->description )        $data[ 'description' ]        = wpautop( $this->description );
		if( $this->default_value )      $data[ 'default_value' ]      = $this->default_value;
		if( $this->html_attributes )    $data[ 'html_attributes' ]    = $this->html_attributes;

		if( ! empty( $this->dependencies ) ) {
			$data[ 'dependencies' ] = $this->export_dependencies();
		}

		Template::add( 'field-wrap', 'field/wrap/normal' );

		if( method_exists( $this, 'templates' ) ) {
			$this->templates();
		}

		return $data;
	}

	public function add_dependency( $field, $value = null, $compare = null, $group = 0 ) {
		# Initialize the local dependencies
		if( is_null( $this->dependencies ) ) {
			$this->add_dependency_group();
		}

		# Create a rule out of the dependency
		$dependency = Dependency_Rule::create( array(
			'field'   => $field,
			'value'   => is_null( $value ) ? true : $value,
			'compare' => is_null( $compare ) ? '=' : $compare
		));

		# Add a group and the rule
		$this->dependencies[ count( $this->dependencies ) - 1 ]->add_rule( $dependency );

		return $this;
	}

	/**
	 * Adds a new dependency group and lets add_dependency() work with it.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Dependency\Group $group A particular group if needed.
	 * @return Ultimate_Fields\Field                   The instance of the field.
	 */
	public function add_dependency_group( $group = null ) {
		if( is_null( $group ) ) {
			$group = new Dependency_Group;
		}

		# Ensure there is a set
		if( is_null( $this->dependencies ) ) {
			$this->dependencies = new Dependency_Set;
		}

		# Add the group to the set
		$this->dependencies[] = $group;

		return $this;
	}

	public function set_dependencies( $raw ) {
		if( empty( $raw ) ) {
			return;
		}

		$dependencies = new Dependency_Set;

		foreach( $raw as $raw_group ) {
			$group = new Dependency_Group;

			foreach( $raw_group as $dependency ) {
				$group->add_rule( $dependency );
			}

			$dependencies[]= $group;
		}

		$this->dependencies = $dependencies;
	}

	/**
	 * Exports the fields' dependencies, in a format that is suitable
	 * for both JavaScript and normal export files.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] All available dependencies.
	 */
	public function export_dependencies() {
		return $this->dependencies ? $this->dependencies->json() : array();
	}

	/**
	 * Sanitizes a value before saving it.
	 *
	 * If you need to add sanitization to your field, please overwrite the `sanitize_value`
	 * method instead of overwriting this one, as here is where the dynamic callback comes to play.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed
	 */
	public function sanitize( $value ) {
		if( is_callable( $this->sanitization_callback ) ) {
			$value = call_user_func( $this->sanitization_callback, $value, $this );
		} else {
			$value = $this->sanitize_value( $value );
		}

		return $value;
	}

	/**
	 * Sanitizes a particular value.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed
	 */
	protected function sanitize_value( $value ) {
		return $value;
	}

	/**
	 * Changes the sanitization callback for the field.
	 * This is not a validation callback, this method should simply sanitize the value.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to use.
	 * @return Ultimate_Fields\Field THe instance of the field.
	 */
	public function set_sanitization_callback( $callback ) {
		$this->sanitization_callback = $callback;

		return $this;
	}

	/**
	 * Retrieves the value of the field from a source and saves it in the current datastore.
	 *
	 * This method should not perform any validation - if something is wrong with
	 * the value of the field, simply don't save it. Validation will be performed
	 * later and will return an error anyway, if the internal value is empty.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The source which the value of the field should be available in.
	 */
	public function save( $source ) {
		// Locate the value
		if( isset( $source[ $this->name ] ) ) {
			$value = $source[ $this->name ];
		} else {
			$value = $this->default_value;
		}

		// Sanitize the value
		$value = $this->sanitize( $value );

		// Finally, save it
		$this->datastore->set( $this->name, $value );
	}

	/**
	 * Performs data validation.
	 *
	 * @since 3.0
	 *
	 * @return return mixed Either a message if there is an error, or false for no errors.
	 */
	public function validate() {
		if( ! $this->required || $this->is_hidden() )
			return false; // No error

		# Get the value to validate
		$value = is_null( $this->datastore )
			? null
			: $this->datastore->get( $this->name );

		# If there is a validation callback, use it first
		if( $this->validation_callback && $message = call_user_func( $this->validation_callback, $value ) ) {
			return $message;
		}

		if( $this->validation_rule ) {
			# Convert to a string in order to compare
			if( is_array( $value ) && empty( $value ) ) {
				$value = '';
			} else {
				$value = (string) $value;
			}

			# Ensure a proper regular expression
			if( false === @preg_match( $this->validation_rule, null ) ) {
				$this->validation_rule = '~^' . preg_quote( $this->validation_rule ) . '$~i';
			}

			# Perform the validation
			if( preg_match( $this->validation_rule, $value ) ) {
				return false;
			}
		} else {
			# Perform basic validation
			if(
				( is_array( $value ) && ! empty( $value ) )
				|| ( ! is_array( $value ) && (bool) $value )
			) {
				return false;
			}
		}

		# Return an error
		if( (bool) $this->validation_message ) {
			return $this->validation_message;
		} else {
			$message = __( 'The value of %s is not valid!', 'ultimate-fields' );
			return sprintf( $message, $this->label );
		}
	}

	/**
	 * Checks if the field is hidden based on conditional logic and
	 * the values of the other fields within the same datastore.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_hidden() {
		# If there are no dependencies, the field must be hidden
		if( empty( $this->dependencies ) ) {
			return false;
		}

		return $this->dependencies->check( $this->datastore );
	}

	/**
	 * Returns the name of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Changes the name of the field.
	 *
	 * NB: Avoid this method during the late stages of WordPress execution. Only use it before adding the field to
	 * a container, as at that point, some hooks will rely on the existing field name.
	 *
	 * @since 3.0
	 *
	 * @param string $name The new name of the field.
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function set_name( $name ) {
		$this->name = $name;

		return $this->name;
	}

	/**
	 * Allows the label of the field to be changed after it is created.
	 *
	 * @since 3.0
	 *
	 * @param string $Label The new label.
	 * @return Field
	 */
	public function set_label( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * Returns the label of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Returns the description of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Lets the field know that it belongs to a tab. To be used with JS/validation.
	 *
	 * @since 3.0
	 *
	 * @param string $tab The name of the tab.
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function set_tab( $tab ) {
		$this->tab = $tab;

		return $this;
	}

	/**
	 * Returns the tab of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_tab() {
		return $this->tab;
	}

	public function required( $regex = true, $validation_message = '' ) {
		if( ! $regex ) {
			$this->required = false;
			return;
		}

		$this->required = true;

		if( true !== $regex ) {
			$this->validation_rule = $regex;
		}

		if( $validation_message ) {
			$this->validation_message = $validation_message;
		}

		return $this;
	}

	/**
	 * Sets a custom validation message for the field.
	 *
	 * @since 3.0
	 *
	 * @param string $message The validation messsage.
	 * @return Ultimate_Fields\Field      The instance of the field.
	 */
	public function set_validation_message( $message ) {
		$this->validation_message = $message;

		return $this;
	}

	/**
	 * Sets a custom validation rule for the field.
	 *
	 * @since 3.0
	 * @see $validation_rule above
	 *
	 * @param string $regex A regular expressin in PHP format.
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function set_validation_rule( $regex ) {
		$this->validation_rule = $regex;

		return $this;
	}

	/**
	 * Sets a validation callback to the field.
	 *
	 * Such a callback would receive a value as an argument and ether return a message
	 * for an invalid value or false if everything is alright.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to use as a validator.
	 *
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function set_validation_callback( $callback ) {
		$this->validation_callback = $callback;

		return $this;
	}

	/**
	 * Hides the label of the field.
	 *
	 * @since 3.0
	 *
	 * @param bool $set Whether to actually set the label state to hidden or not (optional).
	 * @return Ultimate_Fields\Field
	 */
	public function hide_label( $set = true ) {
		$this->hide_label = $set;

		return $this;
	}

	/**
	 * Sets the width of the field in percents.
	 *
	 * This will only affect fields when they are displayed in grid mode, rather than rows.
	 *
	 * @since 3.0
	 *
	 * @param int $width The width to set.
	 * @return Ultimate_Fields\Field
	 */
	public function set_width( $width ) {
		$this->field_width = $width;

		return $this;
	}

	/**
	 * Returns the width of the field.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_field_width() {
		return $this->field_width;
	}

	/**
	 * Changes the description of the field.
	 *
	 * The description is displayed either beneath the label of the field or below it's input (default).
	 * Use it to instruct your users how to use the field.
	 *
	 * @since 3.0
	 *
	 * @param string $description The description to display.
	 * @return Ultimate_Fields\Field
	 */
	public function set_description( $description ) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Sets a default value to the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to use if nothing else is entered.
	 * @return Ultimate_Fields\Field
	 */
	public function set_default_value( $value ) {
		$this->default_value = $value;
		return $this;
	}

	/**
	 * Returns the default value of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_default_value() {
		return $this->default_value;
	}

	/**
	 * Indicates if the field can handle a certain key.
	 *
	 * This function doesn't need to be overwritten anywhere, except the complex field,
	 * since the complex field is the only one that works outside of its key.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Helper\Data_Source $source The source for retrieving the value.
	 * @return bool
	 */
	public function can_handle( $source ) {
		return $this->name == $source->name;
	}

	/**
	 * When the data API is being used, this method will "handle" a value.
	 *
	 * Handling means checking for a default value, sanitization and eventually conversion
	 * to a new object/data type.
	 *
	 * This method will still be called if a "the_" function is used, but will only convert to
	 * the format of the value that would be returned, not the one that would be outputted.
	 *
	 * @param  mixed       $value  The raw value.
	 * @param  Data_Source $source The data source that the value is associated with.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		if( ( false === $value || is_null( $value ) ) && $this->default_value ) {
			$value = $this->default_value;
		}

		return $value;
	}

	/**
	 * Processes an already handled value for the_value, the_sub_value and etc.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return mixed
	 */
	public function process( $value ) {
		return $value;
	}

	/**
	 * Sets an HTML attribute for the wrapper.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the attribute.
	 * @param string $value The value for the attribute.
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function set_attr( $name, $value = null ) {
		if( is_array( $name ) ) {
			foreach( $name as $key => $value ) {
				$this->set_attr( $key, $value );
			}
		} elseif( $value = trim( $value ) ) {
			$this->html_attributes[ $name ] = $value;
		}

		return $this;
	}

	/**
	 * Imports the field from an array/JSON.
	 *
	 * @param  mixed[] $data All the data about the field.
	 * @return Ultimate_Fields\Field The instance of the field.
	 */
	public function import( $data ) {
		$this->proxy_data_to_setters( $data, array(
			'default_value'      => 'set_default_value',
			'hide_label'         => 'hide_label',
			'field_width'        => 'set_width',
			'description'        => 'set_description',
			'dependencies'       => 'set_dependencies',
			'html_attributes'    => 'set_attr',
			'required'           => 'required',
			'validation_message' => 'set_validation_message',
			'validation_rule'    => 'set_validation_rule'
		));

		return $this;
	}

	/**
	 * Exports the fields' settings for JSON.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = array(
			'name'  => $this->name,
			'label' => $this->label,
			'type' => method_exists( $this, 'get_type' )
				? $this->get_type()
				: ultimate_fields()->basename( $this )
		);

		$this->export_properties( $settings, array(
			'required'           => array( 'required', false ),
			'default_value'      => array( 'default_value', '' ),
			'hide_label'         => array( 'hide_label', false ),
			'field_width'        => array( 'field_width', 100 ),
			'description'        => array( 'description', '' ),
			'validation_message' => array( 'validation_message', null ),
			'validation_rule'    => array( 'validation_rule', null ),
			'html_attributes'    => array( 'html_attributes', array() ),
		));

		if( ! empty( $this->dependencies ) ) {
			$settings[ 'dependencies' ] = $this->export_dependencies();
		}

		return $settings;
	}

	/**
	 * Performs AJAX.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		# Do things in child classes if needed
	}
}
