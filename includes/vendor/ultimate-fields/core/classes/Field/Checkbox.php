<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

class Checkbox extends Field {
	/**
	 * Holds the text that will be displayed next to the checkbox.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $text = '';

	/**
	 * Controls if the field should be displayed in a fancy way.
	 *
	 * @since 3.0
	 * @var boolean.
	 */
	protected $fancy = false;

	/**
	 * Changes the text, shown next to the checkbox.
	 *
	 * @since 3.0
	 *
	 * @param string $text The text to show.
	 * @return Ultimate_Fields\Field\Checkbox
	 */
	public function set_text( $text = '' ) {
		$this->text = $text;

		return $this;
	}

	/**
	 * Makes the field look nice.
	 *
	 * @since 3.0
	 *
	 * @param boolean $use Wether to useit or not.
	 * @return Ultimate_Fields\Field\Checkbox The instance of the field, useful for chaining.
	 */
	public function fancy( $yes = true ) {
		$this->fancy = $yes;

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

		$settings[ 'fancy' ] = $this->fancy;
		$settings[ 'text' ]  = $this->text;

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
		if( $this->datastore ) {
			$value = $this->datastore->get( $this->name );

			if( is_null( $value ) ) {
				$value = $this->default_value;
			}
		} else {
			$value = false;
		}

		return array(
			$this->name => $value
		);
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
		$this->datastore->set( $this->name, isset( $source[ $this->name ] ) && $source[ $this->name ] );
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-checkbox' );
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
			'checkbox_text'  => 'set_text',
			'fancy_checkbox' => 'fancy'
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
			'text'  => array( 'checkbox_text', '' ),
			'fancy' => array( 'fancy_checkbox', false )
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
		return (bool) parent::handle( $value, $source );
	}
}
