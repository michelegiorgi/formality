<?php
namespace Ultimate_Fields\Helper;

/**
 * Handles localization for JavaScript.
 *
 * @since 3.0
 */
class JS_L10N {
	/**
	 * Holds all localized strings.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $strings;

	/**
	 * Creates an intance of the class.
	 *
	 * @since 3.0
	 *
	 * @return JS_L10N
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Adds the necessary hooks.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'uf.register_scripts', array( $this, 'enqueue' ), 100 );
	}

	/**
	 * Adds the translations to JS.
	 *
	 * @since 3.0
	 */
	public function enqueue( $script = 'uf-initialize' ) {
		/**
		 * Fires before translations are sent to WordPress's script translation
		 * functionality, allowing to add some last-minute details.
		 *
		 * @since 3.0
		 *
		 * @param Ultimate_Fields\Helper\JS_L10N $l10n The instance of the class.
		 */
		do_action( 'uf.l10n.before_enqueue', $this );

		wp_localize_script( $script, 'uf_l10n', $this->strings );
	}

	/**
	 * Adds a translation to the queue.
	 *
	 * @since 3.0
	 *
	 * @param string $key The key to use.
	 * @param string $string The translation.
	 * @return JS_L10N The instance of the class for daisy-chaining.
	 */
	public function translate( $key, $string ) {
		$this->strings[ $key ] = $string;

		return $this;
	}
}
