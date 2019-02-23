<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Template;
use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\UI\Container_Helper;

/**
 * Allows the JSON synchronisation to be set up.
 *
 * @since 3.0
 */
class Screen_JSON_Sync extends Screen {
	/**
	 * Returns the ID of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_id() {
		return 'json';
	}

	/**
	 * Returns the title of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_title() {
		return __( 'JSON Management', 'ultimate-fields' );
	}

	/**
	 * Loads the screen when needed.
	 *
	 * For this screen, the method will create an options page and add a container to it.
	 *
	 * @since 3.0
	 */
	public function load() {
		add_filter( 'screen_options_show_screen', '__return_false' );

		if( isset( $_REQUEST[ 'json-action' ] ) ) {
			$this->action( $_REQUEST[ 'json-action' ] );
		}
	}

	/**
	 * Displays the screen.
	 *
	 * @since 3.0
	 */
	public function display() {
		$engine = Template::instance();

		# Start by checking if JSON synchronization is enabled at all
		$manager = ultimate_fields()->get_json_manager();
		$args   = array(
			'enabled'   => $manager->is_enabled(),
			'directory' => $manager->get_path(),
			'show_list' => false,
			'show_path' => true,
			'url'       => $this->url
		);

		if( $args[ 'enabled' ] ) {
			$args[ 'writeable' ] = $manager->is_writeable();

			if( $args[ 'writeable' ] ) {
				$args[ 'show_list' ] = true;
				$args[ 'show_path' ] = false;
			}
		}

		if( $args[ 'show_list' ] ) {
			$containers = array();
			$post_type  = Post_Type::instance();

			foreach( $post_type->get_existing() as $c ) {
				$sync_url = sprintf(
					"%s&json-action=sync&json-nonce=%s&json-item=%s",
					$this->url,
					wp_create_nonce( 'uf-json-sync-' . $c->ID ),
					$c->ID
				);

				$containers[ $hash = $post_type->get_container_hash( $c ) ] = array(
					'ID'        => $c->ID,
					'hash'      => $hash,
					'title'     => sprintf( '<a href="%s">%s</a>', get_edit_post_link( $c->ID ), esc_html( $c->post_title ) ),
					'raw_title' => $c->post_title,
					'database'  => true,
					'json'      => false,
					'modified'  => strtotime( $c->post_modified_gmt ),
					'actions'   => array(
						'edit'        => sprintf( '<a href="%s">%s</a>', get_edit_post_link( $c->ID ), __( 'Edit', 'ultimate-fields' ) ),
						'synchronize' => sprintf( '<a href="%s">%s</a>', $sync_url, __( 'Synchronize', 'ultimate-fields' ) ),
						'trash'       => sprintf( '<a href="%s">%s</a>', get_delete_post_link( $c->ID ), __( 'Delete container', 'ultimate-fields' ) ),
					)
				);
			}

			foreach( $manager->get_containers() as $hash => $container ) {
				if( isset( $containers[ $hash ] ) ) {

					$containers[ $hash ][ 'json' ] = true;

					if( $container[ 'modified' ] != $containers[ $hash ][ 'modified' ] ) {
						$message = $container[ 'modified' ] > $containers[ $hash ][ 'modified' ]
						? __( 'The JSON file is %s newer.', 'ultimate-fields' )
						: __( 'The JSON file is %s older.', 'ultimate-fields' );

						$diff = sprintf(
							$message,
							human_time_diff( $container[ 'modified' ] ),
							$containers[ $hash ][ 'modified' ]
						);
						$containers[ $hash ][ 'diff' ] = $diff;
					}
				} else {
					$containers[ $hash ] = array(
						'hash'      => $hash,
						'title'     => $container[ 'title' ],
						'raw_title' => $container[ 'title' ],
						'database'  => false,
						'json'      => true,
						'modified'  => $container[ 'modified' ],
						'actions'   => array(
							'import' => sprintf(
								'<a href="%s&json-action=import&json-nonce=%s&json-item=%s">%s</a>',
								$this->url,
								wp_create_nonce( 'uf-json-import-' . $hash ),
								$hash,
								__( 'Import', 'ultimate-fields' )
							),
							'trash'  => sprintf(
								'<a href="%s&json-action=delete&json-nonce=%s&json-item=%s">%s</a>',
								$this->url,
								wp_create_nonce( 'uf-json-delete-' . $hash ),
								$hash,
								__( 'Delete JSON', 'ultimate-fields' )
							)
						)
					);
				}
			}

			uasort( $containers, array( $this, 'sort_containers' ) );
			$args[ 'containers' ] = $containers;

			$args[ 'bulk_actions' ] = array(
				'synchronize' => __( 'Synchronize', 'ultimate-fields' )
			);
		}

		$engine->include_template( 'settings/json', $args );
	}

	/**
	 * Compares two containers for the list.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $a
	 * @param mixed[] $b
	 * @return int
	 */
	public function sort_containers( $a, $b ) {
		if( $a[ 'raw_title' ] == $b[ 'raw_title' ] ) {
	        return 0;
	    }

	    return ( $a[ 'raw_title' ] < $b [ 'raw_title' ] ) ? -1 : 1;
	}

	/**
	 * Performs an action based on GET parameters.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action to perform.
	 */
	protected function action( $action ) {
		$nonce_name   = 'json-nonce';
		$nonce_action = 'uf-json-' . $action;

		if(
			! isset( $_REQUEST[ $nonce_name ] )
			|| ! isset( $_REQUEST[ 'json-item' ])
			|| ! wp_verify_nonce( $_REQUEST[ $nonce_name ], $nonce_action . '-' . $_REQUEST[ 'json-item' ] )
		) {
			return;
		}

		$item   = $_REQUEST[ 'json-item' ];
		$method = 'perform_' . $action;

		if( ! method_exists( $this, $method ) ) {
			wp_die( 'Cheating, huh?' );
		}

		$this->$method( $item );

		wp_redirect( $this->url );
		exit;
	}

	/**
	 * Synchronizes a container based on a hash and post ID.
	 *
	 * @since 3.0
	 *
	 * @param string  $hash The hash to sync.
	 * @param WP_Post $post The post ID of the container (optional, only for updates).
	 */
	protected function sync( $hash, $post = null ) {
		# Get the corresponding JSON file, if any
		$manager = ultimate_fields()->get_json_manager();
		$all     = $manager->get_containers();
		$mode    = 'write';
		$post_type = Post_Type::instance();

		# Check if the JSON file exists
		if( ! $post || ( isset( $all[ $hash ] ) && $all[ $hash ][ 'modified' ] > $post->post_modified_gmt ) ) {
			$mode = 'read';
		}

		if( 'read' == $mode ) {
			$helper = new Container_Helper;
			$helper->import_from_json( $all[ $hash ] );
			$helper->save( $all[ $hash ], $post ? $post->ID : null );
		} else {
			// get the container and save it
			$container = new Container_Helper;
			$container->import_from_post( $post );
			$container->register();

			# Export
			$container->dump_json( $post, $post_type->get_container_json_path( $post ) );
		}
	}

	/**
	 * Handles the synchronization of a container.
	 *
	 * @since 3.0
	 *
	 * @param int $item The container to synchronize.
	 */
	protected function perform_sync( $container_id ) {
		$container_id = intval( $container_id );
		$post_type    = Post_Type::instance();

		if(
			! $container_id
			|| ! ( $post = get_post( $container_id ) )
			|| $post_type->get_slug() != get_post_type( $post )
		) {
			wp_die( 'Cheating, huh?' );
		}

		$hash = $post_type->get_container_hash( $post );
		$this->sync( $hash, $post );
	}

	/**
	 * Imports a JSON file.
	 *
	 * @since 3.0
	 *
	 * @param string $hash The hash of the container to import.
	 */
	protected function perform_import( $hash ) {
		$post_type = Post_Type::instance();
		$all       = ultimate_fields()->get_json_manager()->get_containers();

		if( ! isset( $all[ $hash ] ) ) {
			wp_die( 'Cheating, huh?' );
		}

		# Import
		$this->sync( $hash );
	}

	/**
	 * Deletes the JSON file for a container.
	 *
	 * @since 3.0
	 *
	 * @param string $hash The hash of the container to delete.
	 */
	protected function perform_delete( $hash ) {
		$all  = ultimate_fields()->get_json_manager()->get_containers();

		if( ! isset( $all[ $hash ] ) || ! isset( $all[ $hash ][ 'path' ] ) ) {
			wp_die( 'Cheating, huh?' );
		}

		@unlink( $all[ $hash ][ 'path' ] );
	}

	/**
	 * Performs bulk synchronization.
	 *
	 * @since 3.0
	 */
	protected function perform_bulk() {
		if( ! isset( $_REQUEST[ 'action' ] ) || 'synchronize' != $_REQUEST[ 'action' ] ) {
			return;
		}
		if( ! isset( $_REQUEST[ 'container' ] ) || ! is_array( $_REQUEST[ 'container' ] ) || empty( $_REQUEST[ 'container' ] ) ) {
			return;
		}

		$queue     = $_POST[ 'container' ];
		$manager   = ultimate_fields()->get_json_manager();
		$all       = $manager->get_containers();
		$post_type = Post_Type::instance();

		$existing = array();
		foreach( $post_type->get_existing() as $e ) {
			$existing[ $post_type->get_container_hash( $e ) ] = $e;
		}

		foreach( $queue as $hash ) {
			$post = isset( $existing[ $hash ] )
				? $existing[ $hash ]
				: false;

			$this->sync( $hash, $post );
		}
	}
}
