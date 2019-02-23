<?php
namespace Ultimate_Fields\UI\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the field for creating fields (sub-fields).
 *
 * @since 3.0
 */
class Fields extends Field {
	/**
	 * Adds the neccessary scripts and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		Template::add( 'fields-field', 'ui/fields-field' );

		# Add localizations
		ultimate_fields()
			->localize( 'cancel',                __( 'Cancel', 'ultimate-fields' ) )
			->localize( 'field-save',             _x( 'Save', 'field', 'ultimate-fields' ) )
			->localize( 'field-id',               __( 'Field Name', 'ultimate-fields' ) )
			->localize( 'edit-field',             __( 'Edit Field', 'ultimate-fields' ) )
			->localize( 'new-field',              __( 'New Field', 'ultimate-fields' ) )
			->localize( 'add-field',              __( 'Add field', 'ultimate-fields' ) )
			->localize( 'delete-field',           __( 'Delete field', 'ultimate-fields' ) )
			->localize( 'confirm-field-deletion', __( 'Are you sure that you want to delete this field?', 'ultimate-fields' ) );
	}
}
