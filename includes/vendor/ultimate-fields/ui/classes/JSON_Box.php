<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Util;

/**
 * Displays information about the synchronization state of an item.
 *
 * @since 3.0
 */
class JSON_Box {
	/**
	 * Holds the post type that the box works with.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $post_type;

	/**
	 * Holds the labels for the box.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $labels = array();

	/**
	 * Creates a new box.
	 *
	 * @since 3.0
	 *
	 * @param string[] $labels The labels to use.
	 */
	public function __construct( $post_type, $labels = array() ) {
		$this->post_type = $post_type;
		$this->labels    = $labels;

		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
	}

	/**
	 * Returns the default labels if needed.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	protected function get_default_labels() {
		return array(
			'disabled-heading'      => __( 'JSON synchronization is currently <strong>inactive</strong>.', 'ultimate-fields' ),
			'disabled-text'         => __( 'To improve loading times and allow containers to be versioned, please create a folder named <code>uf-json</code> in your theme.', 'ultimate-fields' ),
			'good-heading'          => __( 'Current status: *Synchronized*', 'ultimate-fields' ),
			'good-text'             => __( 'This container is loaded from a local JSON file instead of the database.', 'ultimate-fields' ),
			'not-saved-heading'     => __( 'Current status: *Not existing*', 'ultimate-fields' ),
			'not-saved-text'        => __( 'The settings of the container will be saved in the local JSON folder once you update it.', 'ultimate-fields' ),
			'should-import-heading' => __( 'Current status: *Out of sync*', 'ultimate-fields' ),
			'should-import-text'    => __( "The JSON file associated with this container contains newer data.\n\nPlease synchronize first in order to avoid losing data when saving!", 'ultimate-fields' ),
			'should-update-heading' => __( 'Current status: *Out of sync*', 'ultimate-fields' ),
			'should-update-text'    => __( "The container is loaded from an older JSON file, which needs to be updated!\n\nPlease perform a save to let the container overwrite the old file.", 'ultimate-fields' ),
		);
	}

	/**
	 * Returns the label for a specific action.
	 *
	 * @since 3.0
	 *
	 * @param string $key The key for the text.
	 * @return string
	 */
	protected function get_label( $key ) {
		static $defaults;

		if( isset( $this->labels[ $key ] ) ) {
			$label = $this->labels[ $key ];
		} else {
			if( is_null( $defaults ) ) {
				$defaults = $this->get_default_labels();
			}

			$label = $defaults[ $key ];
		}

		return Util::stars_to_bold( $label );
	}

	/**
	 * Adds a box to the UI.
	 *
	 * @since 3.0
	 */
	public function add_box() {
		if( ! isset( $_GET[ 'post' ] ) ) {
			return;
		}

		add_meta_box(
			$id       = 'uf-json-state-' . $this->post_type,
			$title    = __( 'JSON Synchronization', 'ultimate-fields' ),
			$callback = array( $this, 'display' ),
			$screen   = $this->post_type,
			$context  = 'side',
			$priority = 'low'
		);
	}

	/**
	 * Displays the content of the box.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post to use.
	 */
	public function display( $post ) {
		if( 'publish' != $post->post_status ) {
			return false;
		}

		# Only display a message if the json folder does not exist
		if( ! ultimate_fields()->is_json_enabled() ) {
			Template::instance()->include_template( 'json-box/disabled', array(
				'heading' => $this->get_label( 'disabled-heading' ),
				'text'    => $this->get_label( 'disabled-text' )
			));
			return;
		}

		$hash   = \Ultimate_Fields\UI\Post_Type::instance()->get_container_hash( $post );
		$state  = ultimate_fields()->get_json_manager()->get_container_json_state( $hash );
		$engine = Template::instance();

		# No file exists
		if( false === $state ) {
			return $engine->include_template( 'json-box/not-saved', array(
				'heading' => $this->get_label( 'not-saved-heading' ),
				'text'    => $this->get_label( 'not-saved-text' )
			));
		}

		$file_state = intval( $state[ 'modified' ] );
		$post_state = strtotime( $post->post_modified );

		# Everything is fine
		if( $post_state == $file_state ) {
			return $engine->include_template( 'json-box/good', array(
				'heading' => $this->get_label( 'good-heading' ),
				'text'    => $this->get_label( 'good-text' )
			));
		}

		$edit_url = get_edit_post_link( $post->ID );
		$nonce    = wp_create_nonce( 'uf-sync-' . $post->ID );
		$sync_url = add_query_arg( 'synchronize', $nonce, $edit_url );

		if( $post_state > $file_state ) {
			$engine->include_template( 'json-box/should-update', array(
				'sync_url' => $sync_url,
				'heading'  => $this->get_label( 'should-update-heading' ),
				'text'     => $this->get_label( 'should-update-text' )
			));
		} elseif( $post_state < $file_state ) {
			$engine->include_template( 'json-box/should-import', array(
				'sync_url' => $sync_url,
				'heading'  => $this->get_label( 'should-import-heading' ),
				'text'     => $this->get_label( 'should-import-text' )
			));
		}
	}
}
