<?php
namespace Ultimate_Fields\Helper;

use Walker_Nav_Menu_Edit;

class Menu_Walker extends Walker_Nav_Menu_Edit {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		# Create the normal string
		$string = '';
		parent::start_el( $string, $item, $depth, $args, $id );

		# Add the needed code
		$after = '<fieldset';

		/**
		 * Allows additional fields to be added to the menu item.
		 *
		 * @since 2.0
		 *
		 * @param string  $html The HTML to add to the menu item.
		 * @param mixed   $item The item that is being added.
		 * @param mixed[] $args The arguments for the menu.
		 * @return string
		 */
		$replace_with = apply_filters( 'uf.menu_item_output', '', $item, $args );

		$string = str_replace( $after, $replace_with . $after, $string );

		# Merge the output
		$output .= $string;
	}
}
