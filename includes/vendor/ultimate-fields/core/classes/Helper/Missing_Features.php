<?php
namespace Ultimate_Fields\Helper;

/**
 * Handles the indication of missing features.
 *
 * @since 3.0
 */
class Missing_Features {
	/**
	 * Holds the class names of all missing features.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $features = array();

	/**
	 * Creates an instance of the class.
	 *
	 * @since 3.0
	 * @return Missing_Features
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Adds the necessary handlers for the class.
	 *
	 * @since 3.0
	 */
	private function __construct() {
		add_action( 'admin_notices', array( $this, 'display_notices') );
	}

	/**
	 * Use this method to indicate that a feature is missing.
	 *
	 * @since 3.0
	 *
	 * @param  string $class_name The name of the missing class.
	 * @param  string $context    The context (ex. location).
	 */
	public function report( $class_name, $context ) {
		$this->features[ $class_name ] = compact( 'class_name', 'context' );
	}

	/**
	 * Displays all necessary notices.
	 *
	 * @since 3.0
	 */
	public function display_notices() {
		$generic = array();
		$pro     = array();

		foreach( $this->features as $class_name => $feature ) {
			if( $this->is_pro_feature( $class_name ) ) {
				$pro[] = $feature;
			} else {
				$generic[] = $feature;
			}
		}

		echo '<div class="notice error">';

		// Display a heading
		$heading = __( 'Ultimate Fields: Missing Features', 'ultimate-fields' );
		echo '<p><strong>' . esc_html( $heading ) . '</strong></p>';

		if( ! empty( $pro ) ) {
			echo wpautop( __( 'Those features are only available in Ultimate Fields Pro. Please install and activate the plugin in order to enable them.', 'showcase' ) );
			$this->list( $pro );
		}

		if( ! empty( $generic ) ) {
			echo wpautop( __( 'The following features are not a part of Ultimate Fields or Ultimate Fields Pro, meaning that an extension is required in order to enable them:', 'showcase' ) );
			$this->list( $generic );
		}

		echo '</div>';
	}

	/**
	 * Checks if a certain class belongs to UF Pro.
	 *
	 * @since 3.0
	 *
	 * @param  string  $class_name The name of the class.
	 * @return boolean
	 */
	public function is_pro_feature( $class_name ) {
		static $features;

		if( is_null( $features ) ) {
			$features = array(
				'Ultimate_Fields\\Field\\Color',
				'Ultimate_Fields\\Field\\Audio',
				'Ultimate_Fields\\Field\\Video',
				'Ultimate_Fields\\Field\\Gallery',
				'Ultimate_Fields\\Field\\Date',
				'Ultimate_Fields\\Field\\Time',
				'Ultimate_Fields\\Field\\DateTime',
				'Ultimate_Fields\\Field\\Font',
				'Ultimate_Fields\\Field\\Icon',
				'Ultimate_Fields\\Field\\Map',
				'Ultimate_Fields\\Field\\Embed',
				'Ultimate_Fields\\Field\\Sidebar',
				'Ultimate_Fields\\Field\\Layout',

				'Ultimate_Fields\\Location\\Comment',
				'Ultimate_Fields\\Location\\User',
				'Ultimate_Fields\\Location\\Attachment',
				'Ultimate_Fields\\Location\\Menu_Item',
				'Ultimate_Fields\\Location\\Shortcode',
				'Ultimate_Fields\\Location\\Widget',
				'Ultimate_Fields\\Location\\Taxonomy',
				'Ultimate_Fields\\Location\\Customizer',
			);
		}

		return in_array( $class_name, $features );
	}

	/**
	 * Lists the classes of a specific context.
	 *
	 * @since 3.0
	 *
	 * @param mixed $features The features to print.
	 */
	protected function list( $features ) {
		$items = array();

		foreach( $features as $feature ) {
			switch( $feature['context'] ) {
				case 'field':
					$context = __( 'Field', 'ultimate-fields' );
					break;

				case 'location':
					$context = __( 'Location', 'ultimate-fields' );
					break;

				default:
					$context = __( 'Generic feature', 'ultimate-fields' );
			}

			$class_name = $feature['class_name'];
			$class_name = str_replace( 'Ultimate_Fields\\', '', $class_name );
			$class_name = str_replace( '\\', ' / ', $class_name );

			$items[] = '<li>- ' . $class_name . ' (' . $context . ')</li>';
		}

		echo '<ul>' . implode( $items, "\n" ) . '</ul>';
	}
}
