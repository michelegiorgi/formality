<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;

/**
 * Handles the input for the text field.
 *
 * @since 3.0
 */
class Text extends Field {
	/**
	 * Holds autocomplete suggestions.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $suggestions = array();

	/**
	 * This is the value, which would be displayed as a placeholder within the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $placeholder;

	/**
	 * The prefix is a value, which gets displayed before the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $prefix;

	/**
	 * The suffix is a value, which gets displayed after the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $suffix;

	/**
	 * Holds the output format(ter): none or html.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_format = 'html';

	/**
	 * Holds a validation rule for emails.
	 *
	 * @since 3.0
	 * @var string
	 */
	const VALIDATION_RULE_EMAIL = '~^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$~';

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( ! empty( $this->suggestions ) ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
		}

		wp_enqueue_script( 'uf-field-text' );
	}

	/**
	 * Adds an autocomplete suggestion.
	 *
	 * @since 3.0
	 *
	 * @param string $suggestion The suggested text.
	 * @return Ultimate_Fields\Field\Text THe instance of the field.
	 */
	public function add_suggestion( $suggestion ) {
		if( $suggestion ) {
			$this->suggestions[] = (string) $suggestion;
		}

		return $this;
	}

	/**
	 * Mass-sets suggestions.
	 *
	 * @since 3.0
	 *
	 * @param string $suggestions[] An array of suggestions for the field.
	 * @return Ultimate_Fields\Field\Text The instance of the field.
	 */
	public function add_suggestions( $suggestions ) {
		if( ! is_array( $suggestions ) ) {
			foreach( explode( "\n", $suggestions ) as $suggestion ) {
				$suggestion = (string) $suggestion;
				if( $suggestion ) {
					$this->suggestions[] = $suggestion;
				}
			}
		} else {
			$this->suggestions = $suggestions;
		}

		return $this;
	}

	/**
	 * Returns the available suggestions.
	 *
	 * @since 3.0
	 *
	 * @return string $suggestions.
	 */
	public function get_suggestions() {
		return $this->suggestions;
	}

	/**
	 * Sets the prefix is of the field, which gets displayed before it.
	 *
	 * @since 3.0
	 *
	 * @param string $prefix The prefix.
	 * @return Ultimate_Fields\Field\Text The field.
	 */
	public function set_prefix( $prefix  ) {
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Returns the prefix of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Sets the suffix is of the field, which gets displayed after it.
	 *
	 * @since 3.0
	 *
	 * @param string $suffix The suffix.
	 * @return Ultimate_Fields\Field\Text The field.
	 */
	public function set_suffix( $suffix  ) {
		$this->suffix = $suffix;

		return $this;
	}

	/**
	 * Returns the suffix of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_suffix() {
		return $this->suffix;
	}

	/**
	 * Allow a custom placeholder to be used for the fields' input.
	 *
	 * @since 3.0
	 *
	 * @param string $placeholder The placeholder to use.
	 * @return Ultimate_Fields\Field\Text The instance of the field.
	 */
	public function set_placeholder( $text ) {
		$this->placeholder = $text;

		return $this;
	}

	/**
	 * Changes the output format for the_value and get_the_value().
	 *
	 * @since 3.0
	 *
	 * @param string $formatter The formatter (false, 'html').
	 * @return Ultimate_Fields\Field\Text
	 */
	public function set_output_format( $format ) {
		$this->output_format = $format;

		return $this;
	}

	/**
	 * Processes a value before it's displayed.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return string
	 */
	public function process( $value ) {
		if( ! is_string( $value ) ) {
			return '';
		}

		if( 'html' == $this->output_format ) {
			$value = esc_html( $value );
		}

		return $value;
	}

	/**
	 * Exports the field's settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		if( $this->prefix ) {
			$settings[ 'prefix' ] = $this->prefix;
		}

		if( $this->suffix ) {
			$settings[ 'suffix' ] = $this->suffix;
		}

		if( $this->placeholder ) {
			$settings[ 'placeholder' ] = $this->placeholder;
		}

		if( ! empty( $this->suggestions ) ) {
			$settings[ 'suggestions' ] = $this->suggestions;
		}

		return $settings;
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
			'suggestions'   => 'add_suggestions',
			'placeholder'   => 'set_placeholder',
			'prefix'        => 'set_prefix',
			'suffix'        => 'set_suffix',
			'output_format' => 'set_output_format'
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
			'suggestions'   => array( 'suggestions', array() ),
			'placeholder'   => array( 'placeholder', null ),
			'prefix'        => array( 'prefix', null ),
			'suffix'        => array( 'suffix', null ),
			'output_format' => array( 'output_format', 'html' )
		));

		return $settings;
	}
}
