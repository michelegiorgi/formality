<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\UI\Container_Helper;

/**
 * Handles the migration from version 1 to 2.
 *
 * @since 3.0
 */
class Migration {
	/**
	 * Returns a new instance of the class.
	 *
	 * @since 3.0
	 * @return Migration
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Holds the key, which indicates if the migration is needed.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $option_key = 'uf_v1_to_v2';

	/**
	 * Holds the current state.
	 *
	 * @since 3.0
	 * @var mixed
	 */
	protected $state;

	/**
	 * Indicates that the migration is pending.
	 *
	 * @var string
	 */
	const STATE_PENDING = 'pending';

	/**
	 * Indicates that the user has chosen not to migrate containers.
	 *
	 * @var string
	 */
	const STATE_CANCELLED = 'cancelled';

	/**
	 * Indicates that the migration has been done (or is simply not needed).
	 * @var string
	 */
	const STATE_DONE = 'done';

	/**
	 * Performs initial checks and adds listeners.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		switch( $this->state = get_option( $this->option_key ) ) {
			case self::STATE_PENDING:
				add_action( 'uf.enqueue_scripts', array( $this, 'enqueue_styles' ) );
				add_action( 'admin_notices', array( $this, 'normal_notification' ) );
				break;

			case self::STATE_CANCELLED:
			case self::STATE_DONE:
				# Nothing to do, wee
				break;

			default:
				# Nothing has been done yet, check
				add_action( 'init', array( $this, 'check' ), 100 );
		}
	}

	/**
	 * Returns the current status.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_state() {
		return $this->state;
	}

	/**
	 * Enqueues the styles for Ultimate Fields.
	 *
	 * @since 3.0
	 */
	public function enqueue_styles() {
		if( is_admin() ) {
			wp_enqueue_style( 'ultimate-fields-css' );
		}
	}

	/**
	 * Checks if a migration is needed.
	 *
	 * @since 3.0
	 */
	public function check() {
		$slug     = 'ultimatefields';
		$existing = get_posts( 'posts_per_page=-1&post_type=' . $slug );

		if( empty( $existing ) ) {
			update_option( $this->option_key, self::STATE_DONE );
		} else {
			update_option( $this->option_key, self::STATE_PENDING );
			update_option( $this->option_key . '_pending', count( $existing ) );
		}
	}

	/**
	 * Displays a notification that a migration is needed.
	 *
	 * @since 3.0
	 */
	public function normal_notification() {
		# Check if the settings page is being displayed
		if(
			isset( $_GET[ 'post_type' ] ) && isset( $_GET[ 'page' ] )
			&& 'ultimate-fields' == $_GET[ 'post_type' ] && 'settings' == $_GET[ 'page' ]
		) {
			return;
		}

		$count = intval( get_option( $this->option_key . '_pending' ) );
		$text  = __( 'Thank you for updating to Ultimate Fields 2!', 'ultimate-fields' );

		$text .= ' ' . sprintf(
		    _n(
				'There is %s container that needs to be migrated from Ultimate Fields version 1 to version 2.',
				'There are %s containers that need to be migrated from Ultimate Fields version 1 to version 2.',
				$count, 'ultimate-fields'
			),
		    $count
		);

		$text .= "\n\n" . __( 'Those containers will not be active until you migrate them, as the structure of Ultimate Fields 2 is completely different and they cannot be loaded.', 'ultimate-fields' );

		$text .= "\n\n" . sprintf(
			'<a href="%s" class="button-secondary uf-button"><span class="dashicons dashicons-hammer uf-button-icon"></span> %s</a>',
			admin_url( 'edit.php?post_type=ultimate-fields&amp;page=settings&amp;screen=migration' ),
			__( 'Go to the migration page', 'ultimate-fields' )
		);

		echo '<div class="notice notice-info">';
			echo wpautop( $text );
		echo '</div>';
	}

	/**
	 * Migrates a set of containers.
	 *
	 * @since 3.0
	 */
	public function migrate() {
		$containers = get_posts(array(
			'post_type'      => 'ultimatefields',
			'posts_per_page' => -1
		));

		foreach( $containers as $container ) {
			$this->migrate_container( $container );
		}

		update_option( $this->option_key, self::STATE_DONE );
		delete_option( $this->option_key . '_pending' );

		wp_redirect( admin_url( 'edit.php?post_type=ultimate-fields' ) );
		exit;
	}

	/**
	 * Migrates a particular container by its post.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $container The container post.
	 */
	protected function migrate_container( $post ) {
		$data = array();

		$keys = array( 'uf_title', 'uf_description', 'uf_type', 'uf_options_page_type', 'uf_options_parent_page', 'uf_options_page_parent_slug', 'uf_options_page_slug', 'uf_options_icon', 'uf_options_menu_position', 'uf_postmeta_posttype', 'uf_postmeta_templates', 'uf_postmeta_levels', 'fields' );
		foreach( $keys as $key ) {
			$data[ $key ] = get_post_meta( $post->ID, $key, true );
		}

		$container = uf_setup_container( $data );
		$helper = new Container_Helper;
		$helper->import_container( $container );
		$helper->save( $container->export() );
	}
}
