<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Location;
use Ultimate_Fields\Container;
use Ultimate_Fields\Datastore\Gutenberg_Block as Datastore;
use Ultimate_Fields\Location\Gutenberg_Block as Block_Location;

/**
 * Controls the addition of Gutenberg blocks to Gutenberg.
 *
 * @since 3.0
 */
class Gutenberg_Block extends Controller {
    /**
     * Adds all necessary in order to connect to Gutenberg.
     *
     * @since 3.0
     */
    protected function __construct() {
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_scripts' ) );
        add_filter( 'gutenberg_can_edit_post_type', array( $this, 'do_ajax' ), 10, 2 );
    }

    /**
     * Attaches a container-location pair to the controller, while creating a new block type.
     *
     * @since 3.0
     *
     * @param  Container $container The container that is being attached.
     * @param  Location  $location  The particular location of the container.
     */
    public function attach( Container $container, Location $location ) {
        if( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // Check if the container already has a block location
        foreach( $container->get_locations() as $existing ) {
            if( $existing === $location || ! is_a( $existing, Block_Location::class ) ) {
                continue;
            }

            throw new \Exception( "Only a single Gutenberg Block location may be associated
                with a container, in order to avoid duplicate IDs." );
        }

        // Do the normal attaching
        parent::attach( $container, $location );

        // Create a container-specific rendering callback and register the block
        $callback = new Callback( array( $this, 'render' ), compact( 'container', 'location' ) );
        register_block_type( 'ultimate-fields/' . $this->transform_id( $container->get_id() ), array(
            'render_callback' => $callback->get_callback()
        ));
    }

    /**
     * Enqueues the scripts for blocks.
     *
     * @since 3.0
     * @return [type] [description]
     */
    public function enqueue_scripts() {
        $handle = 'uf-container-block';

        wp_enqueue_script( $handle );
        wp_localize_script( $handle, 'ultimate_fields_gutenberg_blocks', $this->generate_json() );

        ultimate_fields()->l10n()->enqueue();
    }

    /**
     * Transforms the ID of a container to a usable block ID.
     *
     * @since 3.0
     *
     * @param  string $id The ID of the container.
     * @return string
     */
    public function transform_id( $id ) {
        return preg_replace( '~_~', '-', $id );
    }

    /**
     * Generates the JSON, which contains the settings for all blocks.
     *
     * @since 3.0
     *
     * @return array
     */
    public function generate_json() {
        $json = array();

        foreach( $this->combinations as $combination ) {
            $container = $combination[ 'container' ];

            $container->set_datastore( new Datastore );
            $container->enqueue_scripts();

            $settings = $container->export_settings();

            $settings['block_id'] = $this->transform_id( $container->get_id() );

			foreach( $combination['locations'] as $location ) {
				$settings['icon']     = str_replace( 'dashicons-', '', $location->get_icon() );
				$settings['category'] = $location->get_category();
			}

            $json[] = array(
                'settings'  => $settings,
    			'data'      => $container->export_data()
            );
        }

        return $json;
    }

    /**
     * Handles the rendering of the block (in the front-end).
     *
     * @since 3.0
     *
     * @param  Callback $callback   A callback, which contains the container and location.
     * @param  array    $attributes The attributes of the generated block.
     * @return string
     */
    public function render( $callback, $attributes ) {
        $datastore = Datastore::set_current_block( $attributes );

        if( ! $callback['location']->get_callback() ) {
            return '';
        }

        return call_user_func( $callback['location']->get_callback(), $datastore->get_all() );
    }

	/**
	 * Allows AJAX actions to be performed.
	 *
	 * @since 3.0
	 *
	 * @param string  $post_type The post type that is being edited.
	 * @param WP_Post $post      The post type that is being edited.
	 */
	public function do_ajax( $can_edit, $post_type ) {
        if( 'post' == get_current_screen()->base && $can_edit ) {
            ultimate_fields()->ajax( $GLOBALS['post'] );
        }

        return $can_edit;
	}
}
