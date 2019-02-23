<?php
namespace Ultimate_Fields\UI\Settings;

use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\UI\Container_Helper;

/**
 * Handles the export settings screen of the plugin.
 *
 * @since 3.0
 */
class Screen_Import_Export extends Screen {
	/**
	 * Returns the ID of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_id() {
		return 'export';
	}

	/**
	 * Returns the title of the screen.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Import / Export', 'ultimate-fields' );
	}

	/**
	 * Loads the screen (and performs actions if neeeded).
	 *
	 * @since 3.0
	 */
	public function load() {
		if( isset( $_POST[ 'uf_export_id' ] ) ) {
			$this->export();
		}

		if( isset( $_POST[ 'uf_import' ] ) && isset( $_FILES[ 'uf-import-file' ] ) ) {
			$this->import();
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Exports containers.
	 *
	 * @since 3.0
	 */
	protected function export() {
		$ids        = isset( $_POST[ 'uf_export_id' ] )         ? $_POST[ 'uf_export_id' ]         : array();
		$type       = isset( $_POST[ 'uf_export_type' ] )       ? $_POST[ 'uf_export_type' ]       : 'json';
		$textdomain = isset( $_POST[ 'uf_export_textdomain' ] ) ? $_POST[ 'uf_export_textdomain' ] : false;
		$nonce      = isset( $_POST[ '_wpnonce' ] )             ? $_POST[ '_wpnonce' ]             : false;

		if( ! wp_verify_nonce( $nonce, 'uf-export' ) ) {
			return;
		}

		$posts = get_posts( array(
			'post_type'      => Post_Type::instance()->get_slug(),
			'posts_per_page' => -1,
			'post__in'       => array_map( 'intval', $ids ),
			'order'          => array( 'DESC', 'ASC' ),
			'orderby'        => array( 'menu_order', 'title' )
		));

		$output = 'json' == $type
			? $this->export_json( $posts, $ids )
			: $this->export_php( $posts, $ids, $textdomain );

		# Output
		$filename = sprintf(
			'ultimate-fields-export-%s-%s.%s',
			$type,
			date_i18n( 'Y-m-d-H-i' ),
			$type
		);

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		echo $output;
		exit;
	}

	/**
	 * Generates the content of an exportable JSON file.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $posts The posts that contain the data that should be exported.
	 */
	protected function export_json( $posts, $ids ) {
		$data      = array();
		$post_type = Post_Type::instance();

		foreach( $posts as $post ) {
			# Load
			$container = new Container_Helper;
			$container->import_from_post( $post );
			$container->register();

			# Export
			$exported = $container->get_exported_settings();
			$exported[ 'type' ] = 'container';
			$exported[ 'hash' ] = $post_type->get_container_hash( $post );
			if( $order = get_post_meta( $post->ID, 'container_order', true ) ) {
				$exported[ 'order' ] = intval( $order );
			}
			$data[] = $exported;
		}

		/**
		 * Allow extensions to add data to the export.
		 *
		 * @since 3.0
		 *
		 * @param mixed[] $data The data that is ready to be exported.
		 * @param int[]   $ids  The IDs of the items that are supposed to be exported.
		 * @return mixed[]
		 */
		$data = apply_filters( 'uf.ui.export_data', $data, $ids );

		if( defined( 'JSON_PRETTY_PRINT' ) ) {
			$encode_args = JSON_PRETTY_PRINT;
		} else {
			$encode_args = 0;
		}

		return json_encode( $data, $encode_args );
	}

	/**
	 * Generates the content of an exportable PHP file.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $posts The posts that contain the data that should be exported.
	 */
	protected function export_php( $posts, $ids, $textdomain ) {
		$output    = "<?php\n";
		$data      = array();
		$post_type = Post_Type::instance();

		foreach( $posts as $post ) {
			# Load
			$container = new Container_Helper;
			$container->import_from_post( $post );
			$container->register();

			# Export
			$exported = $container->get_exported_settings();
			$exported[ 'type' ] = 'container';
			$exported[ 'hash' ] = $post_type->get_container_hash( $post );

			if( $order = get_post_meta( $post->ID, 'container_order', true ) ) {
				$exported[ 'order' ] = intval( $order );
			}

			$data[] = $exported;
		}

		/**
		 * Allow extensions to add data to the export.
		 *
		 * @since 3.0
		 *
		 * @param mixed[] $data The data that is ready to be exported.
		 * @param int[]   $ids  The IDs of the items that are supposed to be exported.
		 * @return mixed[]
		 */
		$data = apply_filters( 'uf.ui.export_code', $data, $ids );

		# Prepare actions for before and afters of the export
		$args = array(
			'data'          => $data,
			'textdomain'    => $textdomain,
			'function_name' => 'uf_fields_' . round( microtime( true ) )
		);

		$before_callback  = new Callback(
			array( $this, 'before_php_callback' ),
			array( 'ids' => $ids, 'args' => $args )
		);

		$after_callback = new Callback(
			array( $this, 'after_php_callback' ),
			array( 'ids' => $ids, 'args' => $args )
		);

		$args[ 'before' ] = $before_callback;
		$args[ 'after' ]  = $after_callback;

		$engine = Template::instance();
		$output = $engine->include_template( 'php-export', $args, false );

		return $output;
	}

	/**
	 * Allows data to be inserted in the beginning of the PHP export.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The callback that is being used.
	 */
	public function before_php_callback( $callback ) {
		/**
		 * Allows data to be inserted in the beginning of a PHP export.
		 *
		 * @since 3.0
		 *
		 * @param int[]   $ids  The IDs that should be exported.
		 * @param mixed[] $args The same args that are available in the export.
		 */
		do_action( 'uf.ui.export_before', $callback[ 'ids' ], $callback[ 'args' ] );
	}

	/**
	 * Allows data to be inserted at the end of the PHP export.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The callback that is being used.
	 */
	public function after_php_callback( $callback ) {
		/**
		 * Allows data to be inserted in the beginning of a PHP export.
		 *
		 * @since 3.0
		 *
		 * @param int[]   $ids  The IDs that should be exported.
		 * @param mixed[] $args The same args that are available in the export.
		 */
		do_action( 'uf.ui.export_after', $callback[ 'ids' ], $callback[ 'args' ] );
	}

	/**
	 * Dies with some JSON, containing a message.
	 *
	 * @since 3.0
	 *
	 * @param string   $message The message to return.
	 * @param string[] $errors  An array of specific errors (optional)
	 */
	protected function import_error( $message, $errors = array() ) {
		$response = array(
			'OK'      => true,true,
			'success' => false,
			'message' => $message
		);

		if( $errors ) {
			$response[ 'errors' ] = $errors;
		}

		echo json_encode( $response );
		exit;
	}

	/**
	 * Imports a file.
	 *
	 * @since 3.0
	 */
	protected function import() {
		$file = $_FILES[ 'uf-import-file' ];

		if( 0 != $file[ 'error' ] ) {
			$this->import_error( __( 'The file could not be uploaded.', 'ultimate-fields' ) );
		}

		$json = file_get_contents( $_FILES[ 'uf-import-file' ][ 'tmp_name' ] );
		if( empty( $json ) ) {
			$this->import_error( __( 'The file contains invalid JSON.', 'ultimate-fields' ) );
		}

		$data = json_decode( $json, true );
		if( ! $data ) {
			$this->import_error( __( 'The file contains invalid JSON.', 'ultimate-fields' ) );
		}

		# Check if everything is alright with the data
		$check = $this->check_json( $data );
		if( count( $check[ 'errors' ] ) > 0 ) {
			$this->import_error( __( 'Some issues prevented your data from being imported:', 'ultimate-fields' ), $check[ 'errors' ] );
		}

		define( 'UF_UI_IMPORTING', true );

		# Go through each container and import it
		$errors   = 0;
		$imported = 0;

		foreach( $data as $item ) {
			if( $this->import_item( $item ) ) {
				$imported++;
			} else {
				$errors++;
			}
		}

		if( $errors ) {
			echo json_encode(array(
				'OK'      => true, // The upload was successfull, as far as plupload cares.
				'success' => false,
				'message' => __( 'There was an unknown issue that prevented at least on of the items in your JSON file from being imported.', 'ultimate-fields' ),
				'errors'  => $check[ 'errors' ]
			));

			exit;
		}

		$url = admin_url( 'edit.php?post_type=' . Post_Type::instance()->get_slug() );
		$url .= '&uf-imported=' . $imported;

		$response = array(
			'OK'       => true,
			'success'  => true,
			'redirect' => $url
		);

		echo json_encode( $response ); exit;
	}

	/**
	 * Enqueues the scripts for the screen.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-ui-import' );
	}

	/**
	 * Displays the content of the screen.
	 *
	 * @since 3.0
	 */
	public function display() {
		$engine = Template::instance();

		# Prepare a list of the available containers
		$pt = Post_Type::instance();

		$containers = array();
		foreach( $pt->get_existing( false ) as $container ) {
			$containers[ $container->ID ] = $container->post_title;
		}

		# Prepare the groups of exportable things
		$groups = array();

		# Add containers to the list if any
		if( ! empty( $containers ) ) {
			$groups[] = array(
				'label'   => __( 'Containers', 'ultimate-fields' ),
				'options' => $containers
			);
		}

		/**
		 * Allow extensions to include or change the export options.
		 *
		 * @since 3.0
		 *
		 * @param mixed $groups The existing export groups.
		 * @return mixed[]
		 */
		$groups = apply_filters( 'uf.ui.export_groups', $groups, $this );

		$engine->include_template( 'settings/import-export', array(
			'screen'     => $this,
			'groups'     => $groups,
			'containers' => $containers
		));
	}

	/**
	 * Check if the uploaded JSON file can be parsed.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data that should be checked.
	 * @return mixed[] An array that contains 'total' (int) and 'errors' (string[])
	 */
	protected function check_json( $data ) {
		# Check for existing containers and hashes
		$post_type = Post_Type::instance();
		$existing  = array();

		$args = array(
			'post_type'      => $post_type->get_slug(),
			'posts_per_page' => -1
		);

		# Collect all existing hashes
		foreach( get_posts( $args ) as $container ) {
			$existing[] = $post_type->get_container_hash( $container );
		}

		/**
		 * Allows the array of existing hashes (identifiers) to be modified before
		 * checking their existance in the JSON that is beingimported.
		 *
		 * @since 3.0
		 *
		 * @param string[] $existing The existing core hashes.
		 * @return string[]
		 */
		$existing = apply_filters( 'uf.ui.existing_items', $existing );

		# Check the new containers
		$total   = 0;
		$invalid = array();

		/**
		 * Allows the types of importable items to be modified.
		 *
		 * @since 3.0
		 *
		 * @param string[] $types The strings with types of data that can be imported.
		 * @param mixed    $data  The data that is being imported, if needed.
		 * @return string[]
		 */
		$types = apply_filters( 'uf.ui.importable_types', array( 'container' ), $data );

		foreach( array_values( $data ) as $i => $item ) {
			$total++;

			# Check if basic data is available
			if( ! isset( $item[ 'hash' ] ) || ! isset( $item[ 'type' ] ) || ! isset( $item[ 'title' ] ) ) {
				$invalid[] = sprintf( __( 'Item #%d contains no hash, type or title.', 'ultimate-fields' ), $i + 1 );
				continue;
			}

			# Check if the container already exists
			if( in_array( $item[ 'hash' ], $existing ) ) {
				$invalid[] = sprintf( __( 'Item #%d, "%s", already exists.', 'ultimate-fields' ), $i + 1, $item[ 'title' ] );
				continue;
			}

			# Check if the type is valid
			if( ! in_array( $item[ 'type' ], $types ) ) {
				$invalid[] = sprintf( __( 'The type of %s, %s, is not supported.', 'ultimate-fields' ), $item[ 'title' ], $item[ 'type' ] );
				continue;
			}
		}

		return array(
			'total'  => $total,
			'errors' => $invalid
		);
	}

	/**
	 * Imports an item.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $item The item to import.
	 * @return bool   Indicates if the item got imported.
	 */
	protected function import_item( $item ) {
		/**
		 * Allows the import of an item to be short-circuited.
		 *
		 * Use this hook to import data to/through extensions.
		 *
		 * @param mixed $imported The result from the import.
		 * @param mixed $item     The item that is being imported.
		 * @return mixed
		 */
		$imported = apply_filters( 'uf.ui.import_item', null, $item );

		if( ! is_null( $imported ) ) {
			return $imported;
		}

		$container = new Container_Helper;
		$container->import_from_json( $item );
		return $container->save( $item );
	}
}
