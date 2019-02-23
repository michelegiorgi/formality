<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the input for an embed field, which saves Embed code based on a URL.
 *
 * @since 3.0
 */
class Embed extends Field {
	/**
	 * Whenever a new embed field is created, this index will be used
	 * in order to have various fields have various nonces, hooks and checks.
	 *
	 * @since 3.0
	 * @var int
	 */
	static $last_index = 0;

	/**
	 * Holds the actual index of the embed field, see above.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $index = -1;

	/**
	 * Adds additional actions and hooks.
	 *
	 * @since 3.0
	 */
	protected function __constructed() {
		$this->index = ++self::$last_index;
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-embed' );

		Template::add( 'embed', 'field/embed' );
	}

	/**
	 * Exports the fields' data.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$data = parent::export_data();

		if( $url = $data[ $this->name ] ) {
			if( $url = filter_var( $url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) ) {
				$data[ $this->name . '_embed_code' ] = $this->generate_preview( $url );
			}
		}

		return $data;
	}

	/**
	 * Modifies the data, which gets sent to the JS for the fields' settings
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'field_index' ] = $this->index;
		$settings[ 'nonce' ]       = wp_create_nonce( $this->get_nonce_action() );

		return $settings;
	}

	/**
	 * Returns the action for a nonce field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'uf_embed_url_preview_' . $this->index;
	}

	/**
	 * Generates a preview based on a URL>
	 *
	 * @since 3.0
	 *
	 * @param string $url The URL to process.
	 * @return mixed
	 */
	public function generate_preview( $url ) {
		$transient_key = 'ULTIMATE_FIELDS_embed_' . md5( $url );

		$start = microtime(1);
		if( $cached = get_transient( 'a'. $transient_key ) ) {
			$embed = $cached;
		} else {
			$embed = wp_oembed_get( $url, array(
				'width' => 750
			));

			set_transient( $transient_key, $embed, 24 * 60 * 60 );
		}

		return $embed;
	}

	/**
	 * Generates a preview through AJAX
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		if( 'get_embed_' . $this->name != $action ) {
			return;
		}

		if(
			! isset( $_POST[ 'embed_url' ] )
			|| ! isset( $_POST[ 'nonce' ] )
		 	|| ! wp_verify_nonce( $_POST[ 'nonce' ], $this->get_nonce_action() )
		) {
			exit;
		}

		$url = $_POST[ 'embed_url' ];

		if( $url = filter_var( $url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) ) {
			$code = $this->generate_preview( $url );

			echo json_encode(array(
				'url'  => $url,
				'code' => $code
			));
			exit;
		} else {
			status_header( 500 );
		}
	}

	/**
	 * Handles the value by converting it to a proper format.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to handle.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source the value is coming from.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value = parent::handle( $value, $source );

		return filter_var( $value, FILTER_VALIDATE_URL ) === false
			? false
			: esc_url( $value );
	}
}
