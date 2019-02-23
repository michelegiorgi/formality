<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

class Image_Select extends Field {
	/**
	 * Holds the type of data that will be outputted when using the_value
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_type = 'value';

	/**
	 * Holds all options for the field.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $options = array();

	/**
	 * Adds an option to the select.
	 *
	 * @since 3.0
	 *
	 * @param scalar $key The key for the option.
	 * @param string $value The value/label for the option.
	 * @return Ultimate_Fields\Field\Image_Select The instance of the field, useful for chaining.
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
	 * @return Ultimate_Fields\Field\Image_Select The insance of the field, useful for chaining.
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
	 * @return Ultimate_Fields\Field\Image_Select
	 */
	public function add_options( array $options ) {
		foreach( $options as $key => $value ) {
			$this->add_option( $key, $value );
		}

		return $this;
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

		$settings[ 'options' ]     = $this->options;

		return $settings;
	}

	/**
	 * Enqueues the needed scripts.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-image-select' );

		# Add the basic template
		Template::add( 'image-select', 'field/image-select' );
	}

	/**
	 * Changes the output data type of the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type The type. Either 'value', 'text', 'image' or 'url'.
	 * @return Ultimate_Fields\Field\Image_Select The instance of the field.
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
			'image_select_options'       => 'add_options',
			'image_select_output_format' => 'set_output_type'
		));
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
			'options'     => array( 'image_select_options', array() ),
			'output_type' => array( 'image_select_output_format', 'value' )
		));

		return $settings;
	}

	/**
	 * Processes a value for the_value.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to use.
	 * @return string
	 */
	public function process( $value ) {
		if( ! is_scalar( $value ) || ! isset( $this->options[ $value ] ) ) {
			return '';
		}

		if( 'value' == $this->output_type ) {
			return $value;
		} else {
			$value = $this->options[ $value ];

			switch( $this->output_type ) {
				case 'text':
					return $value[ 'label' ];
				case 'url':
					return $value[ 'image' ];
				case 'image':
					return '<img src="' . $value[ 'image' ] . '" alt="' . esc_attr( $value[ 'label' ] ) . '" />';
			}
		}
	}
}
