<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

class Multiselect extends Select {
	/**
	 * Controls if the field uses the select2 library.
	 *
	 * @since 3.0
	 * @var boolean.
	 */
	protected $use_select2 = true;

	/**
	 * Holds the display type of the field.
	 *
	 * The options include 'multiselect' and 'checkbox'.
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $input_type = 'multiselect';

	/**
	 * Contains the output format of the field.
	 *
	 * @since 3.0
	 */
	protected $output_format = 'comma';

	/**
	 * Exports the fields' data.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value = $this->get_value( $this->name );

		if( false === $value ) {
			$value = array();
		}

		# Ensure that the value is an array (maybe saved by the select field)
		$value = (array) $value;

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
		# If the type is checkboxes, add the template for it.
		if( 'checkbox' == $this->input_type ) {
			Template::add( 'multiselect-checkboxes', 'field/checkboxes' );
		} else {
			wp_enqueue_script( 'uf-select2' );
			wp_enqueue_style( 'uf-select2-css' );
		}

		wp_enqueue_script( 'uf-field-multiselect' );
	}

	/**
	 * Changes the output format for the front-end.
	 *
	 * @since 3.0
	 *
	 * @param string $format The chosen format ('comma', 'ordered', 'unordered', 'paragraphs').
	 * @return Ultimate_Fields\Field\Multiselect
	 */
	public function set_output_format( $format = 'comma' ) {
		$this->output_format = $format;

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
			'multiselect_input_type'    => 'set_input_type',
			'multiselect_output_format' => 'set_output_format'
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
			'input_type'    => array( 'select_input_type', 'select' ),
			'output_format' => array( 'multiselect_output_format', 'comma' )
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
		$value     = Field::handle( $value, $source );
		$options   = $this->get_options();
		$available = array();

		if( is_array( $value ) ) {
			foreach( $value as $chosen ) {
				if( isset( $options[ $chosen ] ) ) {
					$available[] = $chosen;
				}
			}
		}

		return $available;
	}

	/**
	 * Processes a value to a string, ready to display.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to display.
	 * @return string
	 */
	public function process( $value ) {
		if( false === $value || is_null( $value ) ) {
			return '';
		}

		// Ensure an array
		$value = (array) $value;

		// Load
		$options         = $this->get_options();
		$selected_keys   = array();
		$selected_labels = array();

		foreach( $options as $key => $label ) {
			if( is_array( $label ) ) {
				foreach( $label as $subkey => $sublabel ) {
					if( in_array( $subkey, $value ) ) {
						$selected_keys[]   = $subkey;
						$selected_labels[] = $sublabel;
					}
				}
			} else {
				if( in_array( $key, $value ) ) {
					$selected_keys[]   = $key;
					$selected_labels[] = $label;
				}
			}
		}

		// Load the output based on the format
		$output = 'text' == $this->output_type
			? $selected_labels
			: $selected_keys;

		// If there is nothing, return nothing
		if( empty( $output ) ) {
			return '';
		}

		// Merge
		switch( $this->output_format ) {
			case 'ordered':
				$output = '<ol><li>' . implode( '</li><li>', $output ) . '</li></ol>';
				break;
			case 'unordered':
				$output = '<ul><li>' . implode( '</li><li>', $output ) . '</li></ul>';
				break;
			case 'paragraphs':
				$output = implode( "\n", array_map( 'wpautop', $output ) );
				break;
			case 'comma':
			default:
				$output = implode( ', ', $output );
				break;
		}

		return $output;
	}
}
