<?php
namespace Ultimate_Fields\Field;

/**
 * ToDo: Don't forget to add a default value if nothing is selected.
 */

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

class Select extends Field {
	/**
	 * Holds the display type of the field.
	 *
	 * The options include 'select' and 'radio'.
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $input_type = 'select';

	/**
	 * If the type is set to radio, it could be displayed horizontally and vertically.
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $orientation = 'vertical';

	/**
	 * Holds the type of options the select will use.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $options_type = 'manual';

	/**
	 * If $options_type is set to 'posts', this holds the post type.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $post_type;

	/**
	 * Holds the pre-defined options for the select.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $options = array();

	/**
	 * Holds a callback, which would generate options.
	 *
	 * @since 3.0
	 * @var callbale
	 */
	protected $options_callback;

	/**
	 * Controls if the field uses the select2 library.
	 *
	 * @since 3.0
	 * @var boolean.
	 */
	protected $use_select2 = false;

	/**
	 * Holds the type of data that will be outputted when using the_value
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_type = 'value';

	/**
	 * Adds an option to the select.
	 *
	 * @since 3.0
	 *
	 * @param scalar $key The key for the option.
	 * @param string $value The value/label for the option.
	 * @return UF_Field_Select The instance of the field, useful for chaining.
	 */
	public function add_option( $key, $value ) {
		$this->options[ $key ] = $value;

		return $this;
	}

	/**
	 * Removes an option from the field.
	 *
	 * @since 3.0
	 *
	 * @param scalar $key The key of the value.
	 * @return UF_Field_Select The insance of the field, useful for chaining.
	 */
	public function remove_option( $key ) {
		# Remove the option.
		if( isset( $this->options[ $key ] ) ) {
			unset( $this->options[ $key ] );
		}

		return $this;
	}

	/**
	 * Adds a batch of options.
	 *
	 * @since 3.0
	 *
	 * @param string[] $options The options that should be added to the field.
	 * @return UF_Field_Select
	 */
	public function add_options( array $options ) {
		foreach( $options as $key => $value ) {
			$this->add_option( $key, $value );
		}

		return $this;
	}

	/**
	 * Lets the select work exclusively with posts.
	 *
	 * @since 3.0
	 *
	 * @param string $post_type The post type to load.
	 * @return Ultimate_Fields\Field\Select
	 */
	public function add_posts( $post_type = 'post' ) {
		$this->options_type = 'posts';
		$this->post_type = $post_type;

		return $this;
	}

	/**
	 * Returns all existing options.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_options() {
		if( ! is_null( $this->options_callback ) ) {
			$this->options = call_user_func( $this->options_callback, $this );
			$this->options_callback = null;
		} elseif( 'posts' == $this->options_type ) {
			$args = array(
				'posts_per_page' => -1,
				'post_type'      => $this->post_type
			);

			if( is_post_type_hierarchical( $this->post_type ) ) {
				$args[ 'orderby' ] = array(
					'menu_order' => 'ASC',
					'post_title' => 'ASC'
				);
			}

			$options = array();
			foreach( get_posts( $args ) as $post ) {
				$options[ $post->ID ] = esc_html( $post->post_title );
			}

			$this->options = $options;
		}

		return $this->options;
	}

	/**
	 * Accepts a callback, which generates options only when needed.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to use.
	 * @return Ultimate_Fields\Field\Select  The instance of the field.
	 */
	public function set_options_callback( $callback ) {
		$this->options_callback = $callback;

		return $this;
	}

	/**
	 * Toggles the use of select2 as a jQuery enchancement.
	 *
	 * @since 3.0
	 *
	 * @param boolean $use Wether to useit or not.
	 * @return UF_Field_Select The instance of the field, useful for chaining.
	 */
	public function fancy( $yes = true ) {
		$this->use_select2 = $yes;

		return $this;
	}

	/**
	 * Changes the input type for the field.
	 *
	 * Based on the type, the field will either display a select or a radio.
	 *
	 * @since 3.0
	 * @see the $input_type varible above.
	 *
	 * @param string $input_type The new inpu type.
	 * @return UF_Field_Select The instance of the current field, useful for chaining.
	 */
	public function set_input_type( $input_type ) {
		$this->input_type = $input_type;

		return $this;
	}

	/**
	 * Retrieves the input type of the field.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_input_type() {
		return $this->input_type;
	}

	/**
	 * Changes the orientation of radios for the field.
	 *
	 * Can be either vertical or horizontal.
	 *
	 * @since 3.0
	 * @see the $orientation varible above.
	 *
	 * @param string $orientation The new orientation.
	 * @return UF_Field_Select The instance of the current field, useful for chaining.
	 */
	public function set_orientation( $orientation ) {
		$this->orientation = $orientation;

		return $this;
	}

	/**
	 * Retrieves the orientation of the field.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	public function get_orientation() {
		return $this->orientation;
	}

	/**
	 * Adds additional fields to JavaScript.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'options' ]     = $this->get_options();
		$settings[ 'use_select2' ] = $this->use_select2;
		$settings[ 'input_type' ]  = $this->input_type;
		$settings[ 'orientation' ] = $this->orientation;

		return $settings;
	}

	/**
	 * Exports the fields' data.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value = $this->get_value( $this->name );

		# Locate a default value
		if( is_null( $value ) ) {
			if( $this->default_value ) {
				$value = $this->default_value;
			} else {
				foreach( $this->get_options() as $key => $option ) {
					$value = $key;
					break;
				}
			}
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( $this->use_select2 ) {
			wp_enqueue_script( 'uf-select2' );
			wp_enqueue_style( 'uf-select2-css' );
		}

		wp_enqueue_script( 'uf-field-select' );

		# If the type is radio, add the template for it.
		if( 'radio' == $this->input_type ) {
			Template::add( 'select-radios', 'field/radio' );
		}

		$message = __( 'There are no options available for this field.', 'ultimate-fields' );
		ultimate_fields()->localize( 'select-no-options', $message );
	}

	/**
	 * Changes the output data type of the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type The type. Either 'value' or 'text'.
	 * @return Ultimate_Fields\Field\Select The instance of the field.
	 */
	public function set_output_type( $type ) {
		$this->output_type = $type;

		return $this;
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		$this->proxy_data_to_setters( $data, array(
			'select_input_type'       => 'set_input_type',
			'select_orientation'      => 'set_orientation',
			'use_select2'             => 'fancy',
			'select_options'          => 'add_options',
			'options'                 => 'add_options',
			'select_output_data_type' => 'set_output_type'
		));

		if( isset( $data[ 'select_options_type' ] ) && 'posts' == $data[ 'select_options_type' ] ) {
			$this->add_posts( $data[ 'select_post_type' ] );
		}
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$this->export_properties( $settings, array(
			'input_type'   => array( 'select_input_type', 'select' ),
			'orientation'  => array( 'select_orientation', 'vertical' ),
			'options'      => array( 'select_options', array() ),
			'use_select2'  => array( 'use_select2', false ),
			'output_type'  => array( 'select_output_data_type', 'value' ),
			'options_type' => array( 'select_options_type', 'manual' ),
			'post_type'    => array( 'select_post_type', null )
		));

		return $settings;
	}

	/**
	 * When the data API is being used, this method will "handle" a value.
	 *
	 * @param  mixed       $value  The raw value.
	 * @param  Data_Source $source The data source that the value is associated with.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value   = parent::handle( $value, $source );
		$options = $this->get_options();

		if( ! isset( $options[ $value ] ) ) {
			$value = $this->default_value;
		}

		return $value;
	}

	/**
	 * Proceses a value for get_the_field().
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return mixed
	 */
	public function process( $value ) {
		if( 'text' == $this->output_type ) {
			$options = $this->get_options();

			if( $value && isset( $options[ $value ] ) ) {
				return $options[ $value ];
			} else {
				return $value;
			}
		} else {
			return $value;
		}
	}
}
