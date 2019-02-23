<?php
namespace Ultimate_Fields\UI\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the field for conditional logic in the UI.
 *
 * @since 3.0
 */
class Conditional_Logic extends Field {
	/**
	 * Adds the neccessary scripts and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		Template::add( 'conditional-logic', 'ui/conditional-logic' );
		Template::add( 'conditional-logic-empty', 'ui/conditional-logic-empty' );
		Template::add( 'conditional-logic-group', 'ui/conditional-logic-group' );
		Template::add( 'conditional-logic-rule', 'ui/conditional-logic-rule' );
	}
}
