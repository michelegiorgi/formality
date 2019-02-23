<?php
namespace Ultimate_Fields\UI\Field_Helper;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Field;

/**
 * Handles the sidebar-field related functionality in the UI.
 *
 * @since 3.0
 */
class Sidebar extends Field_Helper {
	/**
	 * Returns the title of the field, as displayed in the type dropdown.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_title() {
		return __( 'Sidebar', 'ultimate-fields' );
	}

	/**
	 * Returns the fields that allow additional settings.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_fields( $existing ) {
		$fields = array(
			Field::create( 'sidebar', 'default_value_sidebar', __( 'Default Value', 'ultimate-fields' ) ),
			Field::create( 'checkbox', 'sidebar_editable', __( 'Editable', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Allow sidebars to be created and modified within the field', 'ultimate-fields' ) ),
			Field::create( 'complex', 'sidebar_args', __( 'Arguments', 'ultimate-fields' ) )
				->add_dependency( 'sidebar_editable' )
				->add_fields(array(
					Field::create( 'text', 'before_widget', 'before_widget' )
						->set_width( 50 )
						->set_default_value( '<div id="%1$s" class="widget %2$s">' ),
					Field::create( 'text', 'after_widget', 'after_widget' )
						->set_width( 50 )
						->set_default_value( '</div>' ),
					Field::create( 'text', 'before_title', 'before_title' )
						->set_width( 50 )
						->set_default_value( '<h2 class="widgettitle">' ),
					Field::create( 'text', 'after_title', 'after_title' )
						->set_width( 50 )
						->set_default_value( '</h2>' )
				))
		);

		return array(
			'general' => $fields
		);
 	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import_meta( $meta ) {
		parent::import_meta( $meta );


	}
}
