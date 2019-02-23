<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;
use Ultimate_Fields\Fields_Collection;
use Ultimate_Fields\UI\Field_Container;
use Ultimate_Fields\UI\Field_Helper;

/**
 * Handles field editors.
 *
 * @since 3.0
 */
class Field_Editor {
	/**
	 * Those are the groups, which will be used for optgroups in the type dropdown.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $groups = array();

	/**
	 * Holds all available fields within groups.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $types = array();

	/**
	 * Holds enqueue_script callbacks.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $enqueue_callbacks = array();

	/**
	 * Returns an instance of the editor.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\UI\Field_Editor
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initializes the editor.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->add_group( 'text',       __( 'Text Fields',     'ultimate-fields' ) );
		$this->add_group( 'choices',    __( 'Choices',         'ultimate-fields' ) );
		$this->add_group( 'files',      __( 'Files',           'ultimate-fields' ) );
		$this->add_group( 'relational', __( 'Relational',      'ultimate-fields' ) );
		$this->add_group( 'others',     __( 'Others',          'ultimate-fields' ) );
		$this->add_group( 'advanced',   __( 'Advanced Fields', 'ultimate-fields' ) );

		$this->add_type( 'text',       'Text',         Field_Helper\Text::class );
		$this->add_type( 'text',       'Textarea',     Field_Helper\Textarea::class );
		$this->add_type( 'text',       'WYSIWYG',      Field_Helper\WYSIWYG::class );
		$this->add_type( 'text',       'Password',     Field_Helper\Password::class );
		$this->add_type( 'choices',    'Checkbox',     Field_Helper\Checkbox::class );
		$this->add_type( 'choices',    'Select',       Field_Helper\Select::class );
		$this->add_type( 'choices',    'Multiselect',  Field_Helper\Multiselect::class );
		$this->add_type( 'choices',    'Image_Select', Field_Helper\Image_Select::class );

		$this->add_type( 'files',      'File',         Field_Helper\File::class );
		$this->add_type( 'files',      'Image', 	   Field_Helper\Image::class );
		$this->add_type( 'files',      'Audio', 	   Field_Helper\Audio::class );
		$this->add_type( 'files',      'Gallery',      Field_Helper\Gallery::class );
		$this->add_type( 'files',      'Video', 	   Field_Helper\Video::class );
		$this->add_type( 'files',      'Embed', 	   Field_Helper\Embed::class );
		

		$this->add_type( 'relational', 'WP_Object',    Field_Helper\WP_Object::class );
		$this->add_type( 'relational', 'WP_Objects',   Field_Helper\WP_Objects::class );
		$this->add_type( 'relational', 'Link', 	       Field_Helper\Link::class );

		$this->add_type( 'others',     'Number',       Field_Helper\Number::class );
		$this->add_type( 'others',     'Color',        Field_Helper\Color::class );
		$this->add_type( 'others',     'Date',         Field_Helper\Date::class );
		$this->add_type( 'others',     'Time',         Field_Helper\Time::class );
		$this->add_type( 'others',     'Datetime',     Field_Helper\DateTime::class );
		$this->add_type( 'others',     'Font',         Field_Helper\Font::class );
		$this->add_type( 'others',     'Icon',         Field_Helper\Icon::class );
		$this->add_type( 'others',     'Map',          Field_Helper\Map::class );
		$this->add_type( 'others',     'Sidebar',      Field_Helper\Sidebar::class );
		
		$this->add_type( 'advanced',   'Tab',          Field_Helper\Tab::class );
		$this->add_type( 'advanced',   'Section',      Field_Helper\Section::class );
		$this->add_type( 'advanced',   'Message',      Field_Helper\Message::class );
		$this->add_type( 'advanced',   'Complex',      Field_Helper\Complex::class );
		$this->add_type( 'advanced',   'Repeater',     Field_Helper\Repeater::class );
		$this->add_type( 'advanced',   'Layout',       Field_Helper\Layout::class );

		/**
		 * Allows the fields from the UI to be changed.
		 *
		 * @since 3.0
		 *
		 * @param Field_Editor $editor The editor that fields are added to.
		 */
		do_action( 'uf.ui.fields', $this );

		add_action( 'uf.ajax', array( $this, 'handle_ajax_calls' ), 10, 2 );
	}

	/**
	 * Adds a group to the editor.
	 *
	 * @since 3.0
	 *
	 * @param string $id   The ID of the group.
	 * @param string $name The name of the group.
	 * @return Ultimate_Fields\UI\Field_Editor
	 */
	public function add_group( $id, $name ) {
		$this->groups[ $id ] = $name;

		return $this;
	}

	/**
	 * Adds a new field type to the editor.
	 *
	 * @since 3.0
	 *
	 * @param string $group      The ID of the group for the field.
	 * @param string $type_slug  The slug for the type, ex. Text, Select and etc.
	 * @param string $class_name The class name of the field's helper.
	 * @return Ultimate_Fields\UI\Field_Editor
	 */
	public function add_type( $group, $type_slug, $class_name ) {
		$this->types[ $type_slug ] = compact( 'group', 'class_name' );

		return $this;
	}

	/**
	 * Enqueues the scripts and templates for fields.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		Template::add( 'ui-field-popup', 'ui/field-popup' );
		Template::add( 'ui-field-preview', 'ui/field-preview' );

		foreach( $this->types as $type ) {
			$cn = $type[ 'class_name' ];

			if( method_exists( $cn, 'enqueue' ) ) {
				call_user_func( array( $cn, 'enqueue' ) );
			}
		}

		$this->fields();
		foreach( $this->enqueue_callbacks as $callback ) {
			if( is_callable( $callback ) ) {
				call_user_func( $callback );
			}
		}
	}

	/**
	 * Generates the needed fields.
	 *
	 * @since 3.0
	 */
	public function fields() {
		static $exported;

		if( ! is_null( $exported ) ) {
			return $exported;
		}

		$special_fields = array( 'Tab', 'Section', 'Message' );

		$tabs = array(
			'general' => array(
				'tab' => Field::create( 'tab', 'general_tab', __( 'General', 'ultimate-fields' ) )
					->set_icon( 'dashicons-admin-generic' ),
				'fields' => false
			),
			'appearance' => array(
				'tab' => Field::create( 'section', 'appearance_tab', __( 'Appearance', 'ultimate-fields' ) )
					->set_icon( 'dashicons-admin-appearance' )
					->set_description( __( 'Adjust the looks and feel of the field in the back-end', 'ultimate-fields' ) ),
				'fields' => new Fields_Collection
			),
			'conditional_logic' => array(
				'tab' => Field::create( 'tab', 'conditional_logic_tab', __( 'Validation and Logic', 'ultimate-fields' ) )
					->set_icon( 'dashicons-admin-settings' ),
				'fields' => false
			),
			'output' => array(
				'tab'    => Field::create( 'tab', 'output_settings_tab', __( 'Output settings', 'ultimate-fields' ) )
					->set_icon( 'dashicons-admin-appearance' ),
				'fields' => new Fields_Collection
			)
		);

		# Add generic fields
		$tabs[ 'general' ][ 'fields' ] = new Fields_Collection( array(
			Field::create( 'text', 'label', __( 'Label', 'ultimate-fields' ) )
				->required(),
			Field::create( 'text', 'name', __( 'Name', 'ultimate-fields' ) )
				->required( '~^[0-9a-z_]{1,190}$~i' )
				->set_description( __( 'You will use this name when retrieving the value of the field.', 'ultimate-fields' ) )
				->set_validation_message( __( 'Names must contain between 1 and 100 characters, which may be digits, letters or underscores.', 'ultimate-fields' ) ),
			$type_field = Field::create( 'select', 'type', __( 'Type', 'ultimate-fields' ) )
		));

		# Prepare the types dropdown
		$types = array();
		foreach( $this->groups as $group_id => $group_name ) {
			$group_fields = array();

			foreach( $this->types as $field_type => $field_data )
				if( $field_data[ 'group' ] == $group_id )
					$group_fields[ $field_type ] = call_user_func( array( $field_data[ 'class_name' ], 'get_title' ) );

			$types[ $group_name ] = $group_fields;
		}
		$type_field->add_options( $types );

		# Appearance fields
		$tabs[ 'appearance' ][ 'extra_fields' ] = new Fields_Collection( array(
			Field::create( 'textarea', 'description', __( 'Description', 'ultimate-fields' ) )
				->set_rows( 3 )
				->set_description( __( 'Use this description to instruct your users about the proper usage of the field.', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'hide_label', __( 'Hide label', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' )
				->fancy()
				->set_text( __( 'Hide the label of the field and only show its input', 'ultimate-fields' ) )
				->set_description( __( 'Perfect for single-field containers.', 'ultimate-fields' ) ),
			Field::create( 'number', 'field_width', __( 'Width', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' )
				->enable_slider( 1, 100 )
				->set_default_value( 100 )
				->set_description( __( 'This is the percentage of the row\'s width, which the field will occupy.', 'ultimate-fields' ) ),
			Field::create( 'complex', 'html_attributes', __( 'HTML Attributes', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' )
				->add_fields(array(
					Field::create( 'text', 'id' )
						->hide_label()
						->set_prefix( __( 'ID', 'ultimate-fields' ) )
					 	->set_width( 25 ),
					Field::create( 'text', 'class' )
						->hide_label()
						->set_prefix( __( 'class', 'ultimate-fields' ) )
						->set_width( 25 ),
					Field::create( 'text', 'style' )
						->hide_label()
						->set_prefix( __( 'style', 'ultimate-fields' ) )
					 	->set_width( 50 )
				))
		));

		# Conditional logic fields
		$tabs[ 'conditional_logic' ][ 'fields' ] = new Fields_Collection(array(
			Field::create( 'section', 'validation_section', __( 'Validation', 'ultimate-fields' ) )
				->set_description( __( 'Required fields might help ensure proper data (formatting), but they can be very intrusive for the user. Please use them with caution.', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' ),
			Field::create( 'checkbox', 'required', __( 'Required?', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Require a value to be entered', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' ),

			Field::create( 'select', 'validation_rule', __( 'Validation rule', 'ultimate-fields' ) )
				->set_input_type( 'radio' )
				->set_default_value( 'none' )
				->add_options(array(
					'not_null' => __( 'True (A non-empty string or boolean <code>true</code>)', 'ultimate-fields' ),
					'null'     => __( 'False (An empty string or boolean <code>false</code>)', 'ultimate-fields' ),
					'value'    => __( 'An explicitly defined value', 'ultimate-fields' ),
					'regex'    => __( 'Match by regular expression', 'ultimate-fields' )
				))
				->set_description( __( 'Please select the type of value that you are expecting. Validation will be performed even if a field is not required, as long as the field has a value.', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' ),
			Field::create( 'text', 'required_expression', __( 'Regular Expression', 'ultimate-fields' ) )
				->add_dependency( 'validation_rule', 'regex' )
				->add_dependency( 'type', $special_fields, 'NOT_IN' )
				->required()
				->set_description( __( 'Please enter a regular expression including modifiers and terminators (slashes). The regular expression should be supported by both JavaScript and PHP.', 'ultimate-fields' ) ),
			Field::create( 'text', 'required_value', __( 'Required Value', 'ultimate-fields' ) )
				->add_dependency( 'validation_rule', 'value' )
				->add_dependency( 'type', $special_fields, 'NOT_IN' )
				->required()
				->set_description( __( 'Please enter the expected value.', 'ultimate-fields' ) ),

			Field::create( 'text', 'validation_message', __( 'Custom validation message', 'ultimate-fields' ) )
				->set_description( __( 'Enter a custom validation message above. If you leave this field empty, Ultimate Fields will automatically generate a generic message.', 'ultimate-fields' ) )
				->add_dependency( 'type', $special_fields, 'NOT_IN' ),
			Field::create( 'section', 'conditional_logic_section', __( 'Conditional Logic', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'enable_conditional_logic', __( 'Use conditional logic', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Use', 'ultimate-fields' ) )
				->set_description( __( 'Conditional logic allows the field to be shown only when needed, based on the values of other fields.', 'ultimate-fields' ) ),
			Field::create( 'conditional_logic', 'conditional_logic', __( 'Conditional Logic', 'ultimate-fields' ) )
				->add_dependency( 'enable_conditional_logic' )
		));

		# Combine all existing fields into a collection for easy access within other fields
		$existing = new Fields_Collection( call_user_func( 'array_merge', wp_list_pluck( $tabs, 'fields' ) ) );

		# Add field data
		foreach( $this->types as $slug => $type ) {
			$fields = call_user_func( array( $type[ 'class_name' ], 'get_fields' ), $existing );

			foreach( $fields as $tab => $tab_fields ) {
				foreach( $tab_fields as $field ) {
					$field->add_dependency( 'type', $slug );

					$tabs[ $tab ][ 'fields' ][] = $field;
					$existing[] = $field;
				}
			}
		}

		# Combine everything
		$fields = new Fields_Collection();
		foreach( $tabs as $tab ) {
			$fields[] = $tab[ 'tab' ];

			foreach( $tab[ 'fields' ]->export() as $field ) {
				if( method_exists( $field, 'templates' ) ) $this->enqueue_callbacks[] = array( $field, 'templates' );
				$this->enqueue_callbacks[] = array( $field, 'enqueue_scripts' );
				$fields[] = $field;
			}

			if( isset( $tab[ 'extra_fields' ] ) ) foreach( $tab[ 'extra_fields' ]->export() as $field ) {
				if( method_exists( $field, 'templates' ) ) $this->enqueue_callbacks[] = array( $field, 'templates' );
				$this->enqueue_callbacks[] = array( $field, 'enqueue_scripts' );
				$fields[] = $field;
			}
		}

		return $exported = $fields->export();
	}

	/**
	 * Creates a new container and populates it with the neccessary fields.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_settings() {
		$this->container = Field_Container::instance();
		$this->container->add_fields( $this->fields() );
		$this->container->enqueue_scripts();
		$this->container->set_description_position( 'label' );

		return $this->container->export_settings();
	}

	/**
	 * Outputs all necessary JSON.
	 *
	 * @since 3.0
	 */
	public function output() {
		$settings = $this->get_settings();

		printf(
			'<script type="text/json" class="uf-field-settings">%s</script>',
			json_encode( $settings )
		);
	}

	/**
	 * Handles AJAX calls within field editors.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that an AJAX call is being performed for.
	 */
	public function handle_ajax_calls( $action, $item ) {
		foreach( $this->fields() as $field ) {
			$field->perform_ajax( $action, $item );
		}
	}
}
