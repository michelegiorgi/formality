<?php
namespace Ultimate_Fields\UI;

use WP_Post;
use Ultimate_Fields\UI\Field_Editor;
use Ultimate_Fields\UI\Container_Helper;
use Ultimate_Fields\UI\JSON_Box;
use Ultimate_Fields\Template;

/**
 * Handles the post type registration and boxes.
 *
 * @since 3.0
 */
class Post_Type {
	/**
	 * Creates an instance of the class.
	 *
	 * @since 3.0
	 *
	 * @return Post_Type
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Make the constructor private.
	 *
	 * @since 3.0
	 */
	protected function __construct() {}

	/**
	 * Adds the necessary hooks when the UI is active.
	 *
	 * @since 3.0
	 */
	public function hook_in() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'admin_head', array( $this, 'output_css' ) );
		add_action( 'edit_form_after_editor', array( $this, 'display_fields_editor' ) );
		add_action( 'save_post', array( $this, 'container_saved' ), 100 );
		add_action( 'before_delete_post', array( $this, 'delete_json' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'manage_' . $this->get_slug() . '_posts_custom_column', array( $this, 'column' ), 10, 2 );
		add_action( 'manage_' . $this->get_slug() . '_posts_columns', array( $this, 'change_columns' ) );
		add_filter( 'page_row_actions', array( $this, 'change_quick_actions' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		add_action( 'load-post.php', array( $this, 'synchronize' ) );
		add_action( 'add_meta_boxes', array( $this, 'initialize_fields_editor' ), 6 );
		add_action( 'admin_notices', array( $this, 'json_notices' ) );
		add_action( 'uf.ajax.ui_get_container', array( $this, 'load_ajax_container' ) );
		add_action( 'post_updated_messages', array( $this, 'change_updated_message' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pointer_scripts' ) );

		new JSON_Box( $this->get_slug() );
	}

	/**
	 * Retrieves the slug of the post type being used for the UI.
	 *
	 * @since 3.0
	 *
	 * @return string.
	 */
	function get_slug() {
		static $slug;

		if( is_null( $slug ) ) {
			/**
			 * Allows the post type slug of Ultimate Fields to be changed.
			 *
			 * @since 3.0
			 *
			 * @param string $post_type The name of the post type.
			 */
			$slug = apply_filters( 'uf.ui.post_type', 'ultimate-fields' );
		}

		return $slug;
	}

	/**
	 * Retrieves the slug of the post type that will save fields (not containers)
	 * through the UI.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function get_field_type_slug() {
		static $slug;

		if( is_null( $slug ) ) {
			/**
			 * Allows the post type slug of Ultimate Fields to be changed.
			 *
			 * @since 3.0
			 *
			 * @param string $post_type The name of the post type.
			 */
			$slug = apply_filters( 'uf.ui.field_post_type', 'ultimate-field' );
		}

		return $slug;
	}

	/**
	 * Registers the post type of Ultimate Fields.
	 *
	 * @since 3.0
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Containers', 'ultimate-fields' ),
			'singular_name'      => __( 'Container', 'ultimate-fields' ),
			'add_new'            => __( 'Add New', 'ultimate-fields' ),
			'add_new_item'       => __( 'Add Container', 'ultimate-fields' ),
			'edit_item'          => __( 'Edit Container', 'ultimate-fields' ),
			'new_item'           => __( 'New Container', 'ultimate-fields' ),
			'search_items'       => __( 'Search Containers', 'ultimate-fields' ),
			'not_found'          => __( 'No Containers found', 'ultimate-fields' ),
			'delete_posts'       => __( 'Delete Containers', 'ultimate-fields' ),
			'not_found_in_trash' => __( 'No Containers found in Trash', 'ultimate-fields' ),
			'menu_name'          => __( 'Ultimate Fields', 'ultimate-fields' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),
			'menu_position'       => 101,
			'capabilities'        => array(
				'edit_post'              => "manage_options",
				'read_post'              => "manage_options",
				'delete_post'            => "manage_options",
				'edit_posts'             => "manage_options",
				'edit_others_posts'      => "manage_options",
				'publish_posts'          => "manage_options",
				'read_private_posts'     => "manage_options",
				'delete_posts'           => "manage_options",
				'delete_private_posts'   => "manage_options",
				'delete_published_posts' => "manage_options",
				'delete_others_posts'    => "manage_options",
				'edit_private_posts'     => "manage_options",
				'edit_published_posts'   => "manage_options"
			)
		);

		/**
		 * Allows the arguments for the Ultimate Fields post type to be changed.
		 *
		 * @since 3.0
		 *
		 * @param mixed[] $args The arguments for register_post_type.
		 */
		$args = apply_filters( 'uf.ui.post_type_args', $args );

		register_post_type( $this->get_slug(), $args );
	}

	/**
	 * Adds options pages for listing the post type of adding a new one.
	 *
	 * @since 3.0
	 */
	function output_css() {
		$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiI+PHBhdGggZD0iTTE0LjE2MSAxLjk4N2MtMS4xODYgMC4xOTEtMi41OTMgMC41ODQtMy41MyAwLjk2Ni0zLjU1IDEuNDczLTYuNTYzIDQuNzE3LTcuNzUgOC4zMjQtMC41NDUgMS42ODQtMC43MjcgMi43OTQtMC43MjcgNC41MzVzMC4xODIgMi44NTEgMC43MjcgNC41MzVjMS4yODIgMy45MzIgNC43MjYgNy4zNzcgOC42NTkgOC42NTkgMS42ODQgMC41NDUgMi43OTQgMC43MjcgNC41MzUgMC43MjdzMi44NTEtMC4xODIgNC41MzUtMC43MjdjMy45MzItMS4yODIgNy4zNzctNC43MjYgOC42NTktOC42NTkgMC41NDUtMS42ODQgMC43MjctMi43OTQgMC43MjctNC41MzVzLTAuMTgyLTIuODUxLTAuNzI3LTQuNTM1Yy0xLjI4Mi0zLjkyMy00LjczNi03LjM3Ny04LjY0OS04LjY0OS0yLjExNC0wLjY4OS00LjY1LTAuOTQ3LTYuNDU4LTAuNjQxek0xOC41MTQgMy40MjJjMi42MDIgMC41MzYgNC45MjcgMS44MjcgNi43MjYgMy43MjIgMS42NDYgMS43NDEgMi43MjcgMy44MzcgMy4yNDMgNi4yNzYgMC4yMzkgMS4xMjkgMC4yMzkgMy42NTUgMCA0Ljc4NC0xLjA5MSA1LjE0Ny00Ljg3IDguOTI3LTEwLjAxNyAxMC4wMTctMS4xMjkgMC4yMzktMy42NTUgMC4yMzktNC43ODQgMC00LjUzNS0wLjk1Ny04LjE1Mi00LjEyNC05LjU2OC04LjM3Mi0wLjQ4OC0xLjQ4My0wLjYyMi0yLjMwNi0wLjYyMi00LjAzOHMwLjEzNC0yLjU1NSAwLjYyMi00LjAzOGMxLjUwMi00LjQ4NyA1LjU1OS03LjgzNiAxMC4yODUtOC40ODYgMC45MzgtMC4xMzQgMy4yMDUtMC4wNTcgNC4xMTQgMC4xMzR6TTE0LjMyMyA0Ljk2MmMtMC40NTkgMC4wNjctMS4yNTMgMC4yNjgtMS43NyAwLjQ0bC0wLjkyOCAwLjMxNi0wLjAyOSA2LjAyOGMtMC4wMjkgNi45ODQgMCA3LjM0OCAwLjY3IDguNDU4IDAuNTkzIDAuOTc2IDEuNzEzIDEuNTMxIDMuMjgyIDEuNjA3IDIuMzM1IDAuMTI0IDMuODc1LTAuNTE3IDQuNDc4LTEuODU2IDAuNTE3LTEuMTc3IDAuNTQ1LTEuNjE3IDAuNTI2LTguMjA5bC0wLjAyOS02LjAyOC0wLjkxOS0wLjMwNmMtMS42NzQtMC41NzQtMy41MjEtMC43MjctNS4yODEtMC40NXpNNy44NTYgOC41MDJjLTIuMTA1IDIuMzgyLTMuMTA5IDUuNjkzLTIuNjc5IDguODAyIDAuOTE5IDYuNjIxIDcuNTM5IDEwLjkyNiAxMy45NSA5LjA3MCA2LjY3OC0xLjk0MiA5LjkyMi05LjU2OCA2LjY1OS0xNS43MDEtMC40MzEtMC44MTMtMS4yNzMtMi0xLjcwMy0yLjQxMWwtMC4yNDktMC4yMi0wLjAzOCA1LjM4N2MtMC4wMjkgNS4xMjgtMC4wNDggNS40NDQtMC4yMzkgNi4yNTctMC42MzEgMi42NDEtMi4yNzcgNC4yNjctNC45ODUgNC45NTYtMC42OTggMC4xNzItMS4wODEgMC4yMDEtMi41NDUgMC4yMDEtMS41NC0wLjAxMC0xLjgyNy0wLjAzOC0yLjY2LTAuMjU4LTIuNzI3LTAuNzI3LTQuMTUyLTIuMjEtNC43ODQtNC45OTQtMC4xODItMC43OTQtMC4yMDEtMS4yNzMtMC4yNDktNi4yMDlsLTAuMDU3LTUuMzU4LTAuNDIxIDAuNDc4eiIgZmlsbD0iI0ZGRkZGRiI+PC9wYXRoPjwvc3ZnPg==';
		?>

		<style type="text/css">
		#menu-posts-<?php echo $this->get_slug() ?> .wp-menu-image {
			content: '';
			background-image: url(<?php echo $icon ?>) !important;
			background-position: center;
			background-repeat: no-repeat;
			background-size: 60%;
		}
		#menu-posts-<?php echo $this->get_slug() ?> .wp-menu-image:before {
			display: none;
		}
		</style>
		<?php
	}

	/**
	 * Displays the fields editor for the post type.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post that is being edited.
	 */
	public function display_fields_editor( WP_Post $post ) {
		if( $this->get_slug() != $post->post_type )
			return;

		$box = new Fields_Box();
		$box->set_post( $post );
		$box->display();
	}

	/**
	 * Saves the fields of the current container.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The ID of the post that is being saved.
	 */
	public function container_saved( $post_id ) {
		$post = get_post( $post_id );

		if( $this->get_slug() != $post->post_type ) {
			return;
		}

		# Check if the container is being unpublished and delete the JSON
		if( 'publish' != $post->post_status ) {
			$this->delete_json( $post->ID );
			return;
		}

		# Save the simple fields
		$box = new Fields_Box();
		$box->set_post( $post );
		$box->save();

		# If JSON is enabled, setup the container and export the json
		if( ! $json_dir = ultimate_fields()->is_json_enabled() ) {
			return;
		}

		# Setup the container
		$container = new Container_Helper;
		$container->import_from_post( $post );
		$container->register();

		# Export
		$container->dump_json( $post, $this->get_container_json_path( $post ) );
	}

	/**
	 * Checks if there is a json to be deleted.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The ID of the post that is being deleted.
	 */
	public function delete_json( $post_id ) {
		$post = get_post( $post_id );

		if( $this->get_slug() != $post->post_type ) {
			return;
		}

		# Locate the JSON to delete
		if( ! $json_dir = ultimate_fields()->is_json_enabled() ) {
			return;
		}

		$path = $this->get_container_json_path( $post );

		if( file_exists( $path ) ) {
			unlink( $path );
		}
	}

	/**
	 * Generates the JSON path for a container.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $container The container whose json file is needed.
	 * @return string
	 */
	public function get_container_json_path( $container ) {
		$hash = $this->get_container_hash( $container );
		return ultimate_fields()->get_json_manager()->get_path() . 'container-' . $hash . '.json';
	}

	/**
	 * Returns the hash for a container. If one does not exist, creates it.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $container The container whose hash is needed.
	 * @return string
	 */
	public function get_container_hash( $container ) {
		global $wpdb;

		if( $hash = get_post_meta( $container->ID, '_uf_hash', true ) ) {
			return $hash;
		}

		# Get all existing hashes
		$sql = "SELECT DISTINCT meta.meta_value
		FROM $wpdb->postmeta meta
		INNER JOIN $wpdb->posts posts ON posts.ID=meta.post_id
		WHERE meta.meta_key='_uf_hash' AND posts.post_type=%s";

		$sql = $wpdb->prepare( $sql, $this->get_slug() );
		$existing = $wpdb->get_col( $sql );

		# Generate a random string, based on the post ID and site URL
		$string = home_url() . $container->ID;

		do {
			$hash    = substr( md5( $string ), 0, 10 );
			$string .= microtime( true );
		} while( in_array( $hash, $existing ) );

		# Save the key
		update_post_meta( $container->ID, '_uf_hash', $hash );

		return $hash;
	}

	/**
	 * Enqueues scripts for the post type.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( 'ultimate-fields' != get_current_screen()->id )
			return;

		$editor = Field_Editor::instance();
		$editor->enqueue_scripts();

		wp_dequeue_script( 'autosave' );

		# Add a nonce for loading fields
		ultimate_fields()
			->localize( 'ui-container-nonce', wp_create_nonce( 'uf-ui-get-fields' ) )
			->localize( 'invalid-container-title', __( 'Please enter a title!', 'ultimate-fields' ) );
	}

	/**
	 * Returns a list of all existing containers.
	 *
	 * @since 3.0
	 *
	 * @param bool $exclude_current Indicates if the current container should be excluded.
	 * @return mixed[]
	 */
	public function get_existing( $exclude_current = true ) {
		$exclude = array();

		if( function_exists( 'get_current_screen' ) && $this->get_slug() == get_current_screen()->id ) {
			if( isset( $_GET[ 'post' ] ) ) {
				$exclude[] = intval( $_GET[ 'post' ] );
			}
		}

		$existing = get_posts(array(
			'post_type'      => $this->get_slug(),
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'post_title',
			'post__not_in'   => $exclude
		));

		return $existing;
	}

	/**
	 * Changes the columns, which are available for containers.
	 *
	 * @since 3.0
	 *
	 * @param string[] $existing The existing columns.
	 * @return string
	 */
	public function change_columns( $columns ) {
		unset( $columns[ 'date' ] );

		$columns[ 'uf-updated' ]   = __( 'Last updated', 'ultimate-fields' );
		$columns[ 'uf-locations' ] = __( 'Locations', 'ultimate-fields' );
		$columns[ 'uf-fields' ]    = __( 'Fields', 'ultimate-fields' );

		if( ultimate_fields()->is_json_enabled() ) {
			$columns[ 'uf-json' ] = __( 'Synchronisation', 'ultimate-fields' );
		}

		return $columns;
	}

	/**
	 * Outputs the data for columns.
	 *
	 * @since 3.0
	 *
	 * @param string $column  The name of the column.
	 * @param int    $post_id The ID of the post whose columns are being shown.
	 */
	public function column( $column, $post_id ) {
		if( 'uf-updated' == $column ) {
			$time   = strtotime( get_post_field( 'post_modified_gmt', $post_id ) );
			$format = get_option( 'date_format' ) . '\<\b\r\ \/\>' . get_option( 'time_format' );
			echo date_i18n( $format, $time );
		}

		if( 'uf-locations' == $column ) {
			$locations = get_post_meta( $post_id, 'container_locations', true );
			$names     = array();


			if( ! $locations || empty( $locations ) ) {
				_e( 'No locations', 'ultimate-fields' );
				return;
			}

			foreach( $locations as $location ) {
				$type      = $location[ '__type' ];
				$the_class = null;

				foreach( Container_Settings::get_location_classes() as $class_name ) {
					if( $type == call_user_func( array( $class_name, 'get_type' )) ) {
						$the_class = $class_name;
					}
				}

				if( ! $the_class ) {
					continue;
				}

				$names[] = call_user_func( array( $the_class, 'get_name' ) );
			}

			$names = array_unique( $names );

			echo implode( ', ', $names );
		}

		if( 'uf-fields' == $column ) {
			$count = count( get_post_meta( $post_id, '_group_fields', true ) );
			printf( _n( '%s field', '%s fields', $count, 'ultimate-fields' ), $count );
		}

		if( 'uf-json' == $column ) {
			$post_time = strtotime( get_post_field( 'post_modified_gmt', $post_id ) );
			$file_path = $this->get_container_json_path( get_post( $post_id ) );
			$file_time = $this->get_file_modified( $file_path );

			$icon = 'dashicons-no';
			$label = __( 'Not exported', 'ultimate-fields' );

			if( $file_time ) {
				if( $file_time == $post_time ) {
					$icon  = 'dashicons-yes';
					$label = __( 'Synchronized', 'ultimate-fields' );
				} else {
					$icon  = 'dashicons-update';
					$label = __( 'Out of sync', 'ultimate-fields' );
				}
			}

			printf(
				'<span class="dashicons %s" title="%s"></span> %s',
				$icon,
				esc_attr( $label ),
				esc_html( $label )
			);
		}
	}

	/**
	 * Changes the quick actions for the post type.
	 *
	 * @since 3.0
	 */
	public function change_quick_actions( $actions, $post ) {
		if( $this->get_slug() != $post->post_type ) {
			return $actions;
		}

		unset( $actions[ 'inline hide-if-no-js' ] );
		unset( $actions[ 'view' ] );

		if( ultimate_fields()->is_json_enabled() ) {
			$edit_url = get_edit_post_link( $post->ID );
			$nonce    = wp_create_nonce( 'uf-sync-' . $post->ID );
			$sync_url = add_query_arg( 'synchronize', $nonce, $edit_url );

			$actions[ 'uf-synchronize' ] = sprintf(
				'<a href="%s">%s</a>',
				$sync_url,
				__( 'Synchronize', 'ultimate-fields' )
			);
		}

		return $actions;
	}

	/**
	 * Displays notices for containers.
	 *
	 * @since 3.0
	 */
	public function notices() {
		if( ! isset( $_GET[ 'uf-imported' ] ) ) {
			return;
		}

		$imported = intval( $_GET[ 'uf-imported' ] );
		if( $imported < 1 ) {
			return;
		}

		$text = sprintf( _n( '%s item was imported.', '%s items were imported.', $imported, 'ultimate-fields' ), $imported );
		?>
	   <div class="notice notice-success is-dismissible">
		   <p><?php echo $text ?></p>
	   </div>
	   <?php
	}

	/**
	 * Changes the metaboxes for the post type.
	 *
	 * @since 3.0
	 */
	public function metaboxes() {
		remove_meta_box( 'submitdiv', $this->get_slug(), 'side' );
		add_meta_box( 'uf-save', __( 'Publish' ), array( $this, 'publish_box' ), $this->get_slug(), 'side', 'high' );
	}

	/**
	 * Displays the "Publish" box on the post type.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post that is currently being displayed.
	 */
	public function publish_box( $post ) {
		$engine = Template::instance();
		$engine->include_template( 'ui/save-box', compact( 'post' ) );
	}

	/**
	 * Checks when the container within a file was modified.
	 *
	 * @since 3.0
	 *
	 * @param string $path The path to the file.
	 * @return mixed
	 */
	public function get_file_modified( $path ) {
		if( ! file_exists( $path ) )
			return false;

		$json = json_decode( file_get_contents( $path ), true );

		if( ! $json || empty( $json ) )
			return false;

		$container = $json[ 0 ];

		if( isset( $container[ 'modified' ] ) ) {
			return intval( $container[ 'modified' ] );
		} else {
			return false;
		}
	}

	/**
	 * Attempts the synchronization of a container.
	 *
	 * @since 3.0
	 */
	public function synchronize() {
		if(
			! function_exists( 'get_current_screen' )
			|| $this->get_slug() != get_current_screen()->id
			|| ! isset( $_GET[ 'post' ] )
			|| ! isset( $_GET[ 'synchronize' ] )
			|| ! wp_verify_nonce( $_GET[ 'synchronize' ], 'uf-sync-' . $_GET[ 'post' ] )
			|| ! $post = get_post( $_GET[ 'post' ] )
		) {
			return;
		}

		$json_path = $this->get_container_json_path( $post );
		$mode      = 'overwrite';

		# Check if we should "read" the JSON or "overwrite" it.
		if( file_exists( $json_path ) ) {
			$json = json_decode( file_get_contents( $json_path ), true );
			if( $json && ! empty( $json ) ) {
				$container = $json[ 0 ];

				if( isset( $container[ 'modified' ] ) && $container[ 'modified' ] > strtotime( $post->post_modified_gmt ) ) {
					$mode = 'read';
				}
			}
		}

		# This action is not needed if the JSON isn't newer
		if( 'read' == $mode ) {
			$helper = new Container_Helper;
			$helper->import_from_json( $container );
			$helper->save( $container, $post->ID );
		}

		# Redirect to the edit URL
		header( "Location: edit.php?post_type=" . $this->get_slug() );
		exit;
	}

	/**
	 * Display notifications when needed.
	 *
	 * @since 3.0
	 */
	public function json_notices() {
		$manager = ultimate_fields()->get_json_manager();

		if( ! $manager->is_enabled() || 'edit-' . $this->get_slug() != get_current_screen()->id ) {
			return;
		}

		if( $manager->is_writeable() ) {
			return;
		}

		$message = sprintf(
			__( 'The JSON directory in your theme is not writable, which prevents Ultimate Fields from synchronizing containers. Please check the <a href="%s">settings page</a> for more details.', 'ultimate-fields' ),
			'edit.php?post_type=' . $this->get_slug() . '&page=settings&screen=json'
		);

		?>
		<div class="notice notice-error">
			<p><?php echo $message ?></p>
		</div>
		<?php
	}

	/**
	 * Performs AJAX calls when the UI is being edited.
	 *
	 * @since 3.0
	 *
	 * @param string  $post_type The (post) type of thing that is being edited.
	 * @param WP_Post $post      The post with the metaboxes.
	 */
	public function initialize_fields_editor( $post_type ) {
		if( $this->get_slug() != $post_type ) {
			return;
		}

		Field_Editor::instance();
	}

	/**
	 * Loads a container for AJAX, used to preview containers within other fields.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is being displayed/handled.
	 */
	public function load_ajax_container( $item ) {
		if( ! is_object( $item ) || ! is_a( $item, 'WP_Post' ) || $this->get_slug() != $item->post_type ) {
			return;
		}

		$containers = isset( $_POST[ 'containers' ] ) && is_array( $_POST[ 'containers' ] )
			? array_map( 'trim', array_map( 'stripslashes', $_POST[ 'containers' ] ) )
			: false;

		$nonce = isset( $_POST[ 'nonce' ] )
			? trim( stripslashes( $_POST[ 'nonce' ] ) )
			: false;

		if( ! $containers || ! $nonce || ! wp_verify_nonce( $nonce, 'uf-ui-get-fields' ) ) {
			wp_die( 'Cheating, uh?' );
		}

		# Get the container
		$containers = get_posts(array(
			'post_type'      => $this->get_slug(),
			'posts_per_page' => -1,
			'meta_key'       => '_uf_hash',
			'meta_value'     => $containers,
			'meta_compare'   => 'IN'
		));

		if( ! empty( $containers ) ) {
			$generated = array();

			foreach( $containers as $post ) {
				# Get data about the container
				$container = new Container_Helper;
				$container->import_from_post( $post );
				$generated[] = array(
					'id'     => $post->_uf_hash,
					'fields' => $container->prop( 'fields' )
				);
			}

			$output = array(
				'error'      => false,
				'containers' => $generated
			);
		} else {
			$output = array(
				'error' => __( 'The chosen container no longer exists!', 'ultimate-fields' )
			);
		}

		echo json_encode( $output );
		exit;
	}

	/**
	 * When a container is updated, it's message should not be "Post Published".
	 *
	 * @since 1.0
	 *
	 * @param mixed[] $messages The current group of messages.
	 * @return mixed[]
	 */
	public function change_updated_message( $messages ) {
		if( ! isset( $_GET[ 'post' ] ) )
			return $messages;

		$p = get_post( $_GET[ 'post' ] );

		if( ! $p ) {
			return $messages;
		}

		if( $p->post_type != $this->get_slug() ) {
			return $messages;
		}

		$messages[ 'post' ][ 6 ] = __( 'The container was saved.', 'ultimate-fields' );

		return $messages;
	}

	/**
	 * Enqueues the necessary pointers.
	 *
	 * @since 3.0
	 */
	public function enqueue_pointer_scripts() {
		if( ! isset( $_GET[ 'demo' ] ) )
			return;

		# Add the scripts
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'uf-pointers', ULTIMATE_FIELDS_UI_URL . 'js/pointers.js', array( 'uf-core' ), '3.0', true );

		# Add the pointers
		add_action( 'admin_footer', array( $this, 'add_pointers' ) );
	}

	/**
	 * Outputs the necessary pointers on the editor screen.
	 *
	 * @since 3.0
	 */
	public function add_pointers() {
		$pointers = array();

		# Start with the title
		$pointers[ '#title' ] = array(
			'title'    => __( 'Enter a title', 'ultimate-fields' ),
			'text'     => __( 'Enter a title of the container, that will indicate it\'s purpose, for example "Theme Options".', 'ultimate-fields' ),
			'position' => 'left,top',
			'hook'     => 'ready',
			'dismiss'  => 'keyup #title'
		);

		# Add the field pointer
		$pointers[ '.uf-add-field-button' ] = array(
			'title'    => __( 'Add a new field', 'ultimate-fields' ),
			'text'     => __( 'Click here to create your first your first field.', 'ultimate-fields' ),
			'position' => 'left,top',
			'dismiss'  => 'click .uf-add-field-button'
		);

		# When the field is open, give the next pointers
		$pointers[ '.uf-field-name-label input' ] = array(
			'title'    => __( 'Enter a label', 'ultimate-fields' ),
			'text'     => __( 'The label of the field will be displayed next to it, indicating what it does. For this exercise, you can enter "Copyrights Text" here.', 'ultimate-fields' ),
			'position' => 'left,top',
			'dismiss'  => 'keypress'
		);

		# Go to the ID
		$pointers[ '.uf-field-name-name input' ] = array(
			'title'    => __( 'Enter a custom field name', 'ultimate-fields' ),
			'text'     => __( 'The name is used when saving values in the database and when you retrieve them.<br />It is automatically populated when you enter a label, but is still manually customizable.<br /><br />You can change it to "copyrights" for the sake of this example.', 'ultimate-fields' ),
			'position' => 'left,top',
			'dismiss'  => 'keyup'
		);

		# Go to the type
		$pointers[ '.uf-field-name-type select' ] = array(
			'title'    => __( 'Change the field type', 'ultimate-fields' ),
			'text'     => __( 'To make settings easier to grasp for you users, you will need different field types.<br /><br />When the type is changed, you will also be able to fine tune it\'s settings.<br /><br />Please change this to WYSIWYG.', 'ultimate-fields' ),
			'position' => 'left,top',
			'dismiss'  => 'change'
		);

		# Ask the user to save the field
		$pointers[ '.uf-overlay-footer .button-primary' ] = array(
			'title'    => __( 'Save the field', 'ultimate-fields' ),
			'text'     => __( 'When you have entered all needed information about the field, you can save it.', 'ultimate-fields' ),
			'position' => 'left,bottom',
			'dismiss'  => 'click'
		);

		$pointers[ '.uf-repeater-tags-tag:eq(1)' ] = array(
			'title'    => __( 'Change the location type', 'ultimate-fields' ),
			'text'     => __( 'Containers can be displayed in different regions of the admin.<br /><br />Select "Options Page" to display the container as an options page in the administration menu.', 'ultimate-fields' ),
			'position' => 'left,left',
			'dismiss'  => 'click',
			'timeout'  => 100
		);

		$pointers[ '#publish' ] = array(
			'title'    => __( 'Good job!', 'ultimate-fields' ),
			'text'     => __( 'This will save all changes you just made and refresh the page.<br /><br />When the page is refreshed, you will see a "Theme Options" link in the menu on the left.<br /><br />If you need further help, please check the documentation of the plugin on the official website.', 'ultimate-fields' ),
			'position' => 'middle,right',
			'dismiss'  => 'click',
			'scroll'   => 0
		);

		# Process in the correct JS format
		$processed = array();
		foreach( $pointers as $key => $pointer ) {
			$position = explode( ',', $pointer[ 'position' ] );

			$processed[] = array(
				'selector' => $key,
				'hook'     => isset( $pointer[ 'hook' ] ) ? $pointer[ 'hook' ] : false,
				'unhook'   => $pointer[ 'dismiss' ],
				'timeout'  => isset( $pointer[ 'timeout' ] ) ? $pointer[ 'timeout' ] : 0,
				'scroll'   => isset( $pointer[ 'scroll' ] ) ? $pointer[ 'scroll' ] : -1,
				'options'  => array(
					'content'  => '<h3>' . $pointer[ 'title' ] . '</h3>' . wpautop( $pointer[ 'text' ] ),
					'position' => array(
						'edge'  => $position[ 1 ],
						'align' => $position[ 0 ]
					)
				)
			);
		}
		?>
		<script type="text/javascript">
		var uf_pointers = jQuery.parseJSON( '<?php echo addslashes( json_encode( $processed ) ) ?>' );
		</script>
		<?php
	}
}
