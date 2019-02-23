<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

class Section extends Field {
	protected $icon = '';

	/**
	 * Holds the color that will be used as a background of the section.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $color;

	public function export_data() {
		return array();
	}

	/**
	 * Changes the icon of the tab.
	 *
	 * @since 3.0
	 *
	 * @param string $icon The CSS class for the icon element.
	 * @return Section The instance of the field.
	 */
	public function set_icon( $icon ) {
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Changes the background color of the section.
	 *
	 * @since 3.0
	 *
	 * @param string $color The CSS class for the color ('white', 'blue', 'red', 'green').
	 * @return Ultimate_Fields\Field\Section
	 */
	public function set_color( $color ) {
		$this->color = $color;

		return $this;
	}

	/**
	 * Adds more details to the fields' settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		if( $this->icon ) {
			$icon = $this->icon;

			if( preg_match( '~^dashicons[^\s]~i', $icon ) ) {
				$icon = 'dashicons ' . $icon;
			}

			$settings[ 'icon' ] = $icon;
		}

		if( $this->color ) {
			$settings[ 'color' ] = $this->color;
		}

		return $settings;
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-section' );

		Template::add( 'section', 'section' );
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
			'section_icon'  => 'set_icon',
			'section_color' => 'set_color'
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
			'icon'  => array( 'section_icon', null ),
			'color' => array( 'section_color', null )
		));

		return $settings;
	}

	/**
	 * Ensures that unlike normal fields, no values are saved for sections.
	 *
	 * @since 3.0.2
	 *
	 * @param mixed[] $source The source which the value of the field would be available in.
	 */
	public function save( $source ) {
		// Nothing to do here really...
	}
}
