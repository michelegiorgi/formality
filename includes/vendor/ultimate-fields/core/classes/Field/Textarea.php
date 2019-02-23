<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;

/**
 * Handles the input for the textarea field.
 *
 * @since 3.0
 */
class Textarea extends Field {
	/**
	 * Holds the number of the textarea's rows.
	 *
	 * @since 3.0
	 * @var int.
	 */
	protected $rows = 8;

	/**
	 * Indivates if the_content will be applied to the value of the field.
	 *
	 * @since 3.0
	 *
	 * @var bool
	 */
	protected $the_content = false;

	/**
	 * Indivates if shortcodes will be applied to the value of the field.
	 *
	 * @since 3.0
	 *
	 * @var bool
	 */
	protected $shortcodes = false;

	/**
	 * Indivates if wpautop will be applied to the value of the field.
	 *
	 * @since 3.0
	 *
	 * @var bool
	 */
	protected $paragraphs = false;

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-textarea' );
	}

	/**
	 * Sets the number of visible rows for the field.
	 *
	 * @since 3.0
	 * @see the $rows varible above.
	 *
	 * @param int $rows The new rows count.
	 * @return Ultimate_Fields\Field\Textarea The instance of the current field, useful for chaining.
	 */
	public function set_rows( $rows ) {
		$this->rows = intval( $rows );

		return $this;
	}

	/**
	 * Returns the number of rows for the textarea.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_rows() {
		return $this->rows;
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

		$settings[ 'rows' ] = $this->rows;

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
			'rows'              => 'set_rows',
			'apply_the_content' => 'apply_the_content',
			'apply_shortcodes'  => 'do_shortcodes',
			'apply_wpautop'     => 'add_paragraphs'
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
			'rows'        => array( 'rows', 8 ),
			'the_content' => array( 'apply_the_content', false ),
			'shortcodes'  => array( 'apply_shortcodes', false ),
			'paragraphs'  => array( 'apply_wpautop', false )
		));

		return $settings;
	}

	/**
	 * Applies the "the_content" filter to the value of the field.
	 *
	 * @since 3.0
	 *
	 * @param bool $flag A flag that indicates if the functionality should be added or removed.
	 * @return Ultimate_Fields\Field\Textarea The instance of the field.
	 */
	public function apply_the_content( $flag = true ) {
		$this->the_content = $flag;

		return $this;
	}

	public function should_apply_the_content() {
		return $this->the_content;
	}

	/**
	 * Ensures that the shortcodes within the fields' value will be parsed.
	 *
	 * @since 3.0
	 *
	 * @param bool $flag A flag that indicates if the functionality should be added or removed.
	 * @return Ultimate_Fields\Field\Textarea The instance of the field.
	 */
	public function do_shortcodes( $flag = true ) {
		$this->shortcodes = $flag;

		return $this;
	}

	/**
	 * Returns the flag, which indicates whether shortcodes should be applied.
	 *
	 * @since 3.0
	 */
	public function get_shortcodes() {
		return $this->shortcodes;
	}

	/**
	 * Automatically adds paragraphs to the value of the field.
	 *
	 * @since 3.0
	 *
	 * @param bool $flag A flag that indicates if the functionality should be added or removed.
	 * @return Ultimate_Fields\Field\Textarea The instance of the field.
	 */
	public function add_paragraphs( $flag = true ) {
		$this->paragraphs = $flag;

		return $this;
	}

	/**
	 * Returns the flag, which indicates whether paragraphs should be added.
	 *
	 * @since 3.0
	 */
	public function get_paragraphs() {
		return $this->paragraphs;
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
		if( $this->the_content ) {
			return apply_filters( 'the_content', $value );
		} else {
			if( $this->shortcodes ) {
				$value = do_shortcode( $value );
			}

			if( $this->paragraphs ) {
				$value = wpautop( $value );
			}

			return $value;
		}
	}
}
