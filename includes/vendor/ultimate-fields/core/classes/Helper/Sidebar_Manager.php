<?php
namespace Ultimate_Fields\Helper;

/**
 * Allows global sidebar settings based on field names.
 *
 * @since 3.0
 */
class Sidebar_Manager {
	/**
	 * Holds all field names, which should be managed.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $fields = array();

	/**
	 * Holds a queue of the fields, which need their data.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Field\Sidebar
	 */
	protected $queue = array();

	/**
	 * Holds the names of all registered custom sidebars.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $registered = array();

	/**
	 * Returns an instance of the manager.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Helper\Sidebar_Manager
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initializes the class by adding the appropriate listeners.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'uf.l10n.before_enqueue', array( $this, 'output_sidebars_as_translations' ) );
		add_action( 'wp_ajax_uf_manage_sidebars', array( $this, 'ajax' ) );
	}

	/**
	 * Associates a field with the manager.
	 *
	 * IMPORTANT: Fields are associated with their names, meaning that if you add multiple Fields
	 * with the same name (ex. 'custom_sidebar'), the settings for the sidebar will be retrieved
	 * from the last one.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field\Sidebar The field for the sidebar.
	 * @return mixed[]
	 */
	public function add_field( $field ) {
		$this->fields[ $field->get_name() ] = $field;
	}

	/**
	 * Adds a field to the queue for data output.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field\Sidebar $field The field which needs data.
	 * @return Ultimate_Fields\Helper\Sidebar_Manager
	 */
	public function add_to_queue( $field ) {
		$this->queue[] = $field;

		return $this;
	}

	/**
	 * Registers custom sidebars.
	 *
	 * @since 3.0
	 */
	public function register_sidebars() {
		foreach( $this->fields as $field ) {
			$option = get_option( 'uf_sidebars_' . $field->get_name() );

			if( ! is_array( $option ) || empty( $option ) ) {
				continue;
			}

			foreach( $option as $sidebar ) {
				$args = $field->get_sidebar_args();

				$args[ 'id' ]          = sanitize_title( $sidebar[ 'name' ] );
				$args[ 'name' ]        = $sidebar[ 'name' ];
				$args[ 'description' ] = $sidebar[ 'description' ];

				register_sidebar( $args );

				$this->registered[] = $args[ 'name' ];
			}
		}
	}

	/**
	 * Outputs a global collectionf or sidebars.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Helper\L10N $l10n The internationalization object.
	 */
	public function output_sidebars_as_translations( $l10n ) {
		global $wp_registered_sidebars;

		$collections = array();
		$names       = array();

		foreach( $this->queue as $field ) {
			$names[ $field->get_name() ] = true;
		}

		$normal = array();
		foreach( $wp_registered_sidebars as $sidebar ) {
			# Only add non-uf sidebars here
			if( in_array( $sidebar[ 'name' ], $this->registered ) )
				continue;

			$normal[] = array(
				'id'          => $sidebar[ 'id' ],
				'name'        => $sidebar[ 'name' ],
				'description' => $sidebar[ 'description' ],
				'_builtin'    => true
			);
		}

		foreach( $names as $name => $flag ) {
			$option = get_option( 'uf_sidebars_' . $name );

			if( ! $option ) {
				$option = array();
			}

			$collections[ $name ] = array(
				'nonce'    => wp_create_nonce( 'uf_sidebars_' . $name ),
				'sidebars' => $normal
			);

			foreach( $option as $sidebar ) {
				$collections[ $name ][ 'sidebars' ][] = array(
					'id'          => sanitize_title( $sidebar[ 'name' ] ),
					'name'        => $sidebar[ 'name' ],
					'description' => $sidebar[ 'description' ],
				);
			}
		}

		$l10n->translate( 'uf-sidebars', $collections );
	}

	/**
	 * Handles AJAX calls for managing sidebars.
	 *
	 * @since 3.0
	 */
	public function ajax() {
		if(
			! is_user_logged_in()
			|| ! isset( $_POST[ 'field' ] )
			|| ! ( $name = $_POST[ 'field' ] )
			|| ! isset( $_POST[ 'nonce' ] )
			|| ! wp_verify_nonce( $_POST[ 'nonce' ], 'uf_sidebars_' . $name )
			|| ! isset( $_POST[ 'sidebars' ] )
			|| ! is_array( $_POST[ 'sidebars' ] )
		) {
			exit;
		}

		# Check if the field should be managed
		$managed = false;

		foreach( $this->fields as $field ) {
			if( $field->get_name() == $name ) {
				$managed = true;
				break;
			}
		}

		if( ! $managed ) {
			wp_die( 'Unknown field' );
		}

		# Check and collect
		$raw      = $_POST[ 'sidebars' ];
		$sidebars = array();

		foreach( $raw as $item ) {
			if( ! isset( $item[ 'name' ] ) || ( isset( $item[ '_builtin' ] ) && 'true' == $item[ '_builtin' ] ) )
				continue;

			$sidebars[] = array(
				'name'        => $item[ 'name' ],
				'description' => isset( $item[ 'description' ] ) ? $item[ 'description' ] : ''
			);
		}

		# Save
		update_option( 'uf_sidebars_' . $name, $sidebars );

		exit;
	}
}
