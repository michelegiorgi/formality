<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles editors.
 *
 * @since 3.0
 * @see https://github.com/WordPress/gutenberg/issues/3302 for Gubenberg compatability.
 */
class WYSIWYG extends Textarea {
	/**
	 * Enqueue the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# Enqueue the normal script
		wp_enqueue_script( 'uf-field-wysiwyg' );

		# Add the skeleton for the editor.
		$this->skeleton();
	}

	/**
	 * Prepares the skeleton for the field.
	 *
	 * @since 3.0
	 */
	protected function skeleton() {
		static $done;

		# If the skeleton is already in place, don't go forward
		if( ! is_null( $done ) ) {
			return;
		}

		# Make sure media scripts are in place
		if ( ! function_exists( 'media_buttons' ) )
            include( ABSPATH . 'wp-admin/includes/media.php' );

		# Add the template for the editor
		$id = '<%= mceID %>';

		if( user_can_richedit() ) {
			$template = Template::instance()->include_template( 'field/wysiwyg', array(
				'id' => $id
			), false );
		} else {
			$template = Template::instance()->include_template( 'field/wysiwyg-plain', array(
				'id' => $id
			), false  );
		}

		# Fix the ID placeholder, so it can be replaced later
		$template = str_replace( esc_attr( $id ), $id, $template );

		# Allow quick access to the template.
		$template = Template::instance()->add_template( 'field-wysiwyg', $template );

		# Add the action, that will force a standard editor in order to load all scripts.
		add_action( 'admin_footer', array( get_class(), 'dummy_editor' ) );
		if( is_customize_preview() ) {
			add_action( 'customize_controls_print_scripts', array( get_class(), 'dummy_editor' ) );
		} else {
			add_action( 'wp_footer', array( get_class(), 'dummy_editor' ) );
		}

		$done = true;
	}

	/**
	 * Displays a dummy editor in the footer.
	 *
	 * When the editor is displayed like that, all scripts that are actually required for
	 * TinyMCE and etc. are in place.
	 *
	 * @since 3.0
	 * @link http://codex.wordpress.org/Function_Reference/wp_editor
	 */
	public static function dummy_editor() {
		echo '<div style="display:none;">';

		wp_editor( '', 'uf_dummy_editor_id', array(
			'textarea_name' => 'uf_dummy_editor_name'
		));

		echo '</div>';
	}
}
