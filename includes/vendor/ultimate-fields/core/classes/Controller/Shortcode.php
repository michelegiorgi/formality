<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Datastore\Shortcode as Datastore;
use Ultimate_Fields\Location\Shortcode as Shortcode_Location;

/**
 * Handles the shortcode location.
 *
 * @since 3.0
 */
class Shortcode extends Controller {
	/**
	 * Add the appropriate hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		if( ! is_admin() ) {
			add_filter( 'pre_do_shortcode_tag', array( $this, 'cache_shortcode' ), 10, 4 );
			return;
		}

		add_action( 'after_wp_tiny_mce', array( $this, 'output' ) );
		add_action( 'wp_enqueue_editor', array( $this, 'enqueue_editor_scripts' ) );
		add_filter( 'mce_css', array( $this, 'editor_style' ) );
		add_action( 'admin_menu', array( $this, 'do_ajax' ) );
		add_action( 'template_redirect', array( $this, 'do_ajax' ) );
	}

	/**
	 * Outputs the settings of each container.
	 *
	 * @since 3.0
	 */
	public function output() {
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];
			$settings  = $container->export_settings();
			$settings[ 'tag' ] = null;

			foreach( $combination[ 'locations' ] as $location ) {
				if( is_null( $settings[ 'tag' ] ) ) {
					$settings[ 'tag' ] = $location->get_tag();
				}

				$settings[ 'template' ] = $location->get_template();
			}

			if( is_null( $settings[ 'tag' ] ) ) {
				$settings[ 'tag' ] = $container->get_id();
			}

			printf(
				'<script type="text/json" class="uf-shortcode-container">%s</script>',
				json_encode( $settings )
			);
		}

		# Register the neccessary templates
		Template::add( 'shortcode-editor', 'container/shortcode-editor' );
		Template::add( 'overlay-wrapper', 'overlay-wrapper' );

		# Force template output, as the normal footer hook is already missed
		Template::instance()->output_templates();
	}

	/**
	 * Enqueues the required editor scripts.
	 *
	 * @since 3.0
	 */
	 public function enqueue_editor_scripts() {
		 # Enqueue the normal script
 		wp_enqueue_script( 'uf-container-shortcode' );
		wp_enqueue_style( 'ultimate-fields-css' );

 		# Enqueue containers' scripts
 		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue( 'uf-container-shortcode' );
 	}

	/**
	 * Adds a custom stylesheet to the TinyMCE editor.
	 *
	 * @since 3.0
	 *
	 * @param string $styles Comma-separated URLs of stylesheets.
	 * @return string
	 */
	function editor_style( $mce_stylesheets ) {
		$stylesheet = ULTIMATE_FIELDS_URL . 'assets/css/editor.css';
		$mce_stylesheets .= ', ' . $stylesheet;

	    return $mce_stylesheets;
	}

	/**
	 * When a shortcode is being used, this method will cache it's data.
	 * This way you can use get_value( '[value_name]', 'shortcode' ) within the callback.
	 *
	 * @since 3.0
	 *
	 * @param bool|string $return      Short-circuit return value. Either false or the value to replace the shortcode with.
	 * @param string      $tag         Shortcode name.
	 * @param array       $attr        Shortcode attributes array,
	 * @param array       $m           Regular expression match array.
	 */
	public function cache_shortcode( $return, $tag, $attr, $m ) {
		$check = false;

		# Check if the shortcode should be processed
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			foreach( $combination[ 'locations' ] as $location ) {
				$id = $location->get_tag();

				if( $tag === $id ) {
					$check = true;
					break;
				}
			}

			if( $check ) {
				break;
			}

			if( $container->get_id() == $tag ) {
				$check = true;
				break;
			}
		}

		if( ! $check ) {
			return $return;
		}

		# Try parsing the content if any
		if( $m[5] ) {
			$str = str_replace( '&#8220;', '"', $m[ 5 ] );
			$str = str_replace( '&#8221;', '"', $str );

			if( $json = json_decode( $str, true ) ) {
				foreach( $json as $key => $value ) {
					if( ! isset( $attr[ $key ] ) ) {
						$attr[ $key ] = $value;
					}
				}
			}
		}

		# Let the datastore know what the current shortcode is
		Datastore::set_current_shortcode( $attr );

		return $return;
	}

	/**
	 * Check if any AJAX should be performed.
	 *
	 * @since 3.0
	 */
	public function do_ajax() {
		if( is_user_logged_in() && user_can_richedit() ) {
			ultimate_fields()->ajax( Shortcode_Location::WORKS_WITH_KEYWORD );
		}
	}
}
