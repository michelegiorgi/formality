<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Data_API;
use Ultimate_Fields\Datastore\Comment_Meta as Datastore;
use Ultimate_Fields\Location\Comment as Comment_Location;
use Ultimate_Fields\Controller\REST_API;

/**
 * Handles comment locations.
 *
 * @since 3.0
 */
class Comment extends Controller {
	use REST_API;

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'load-comment.php', array( $this, 'do_ajax' ), 8, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'edit_comment', array( $this, 'save_comment' ) );
		add_action( 'wp_insert_comment', array( $this, 'save_comment' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		$this->rest();
	}

	/**
	 * Attaches metaboxes when neccessary.
	 *
	 * @since 3.0
	 */
	public function add_meta_boxes() {
		foreach( $this->combinations as $combination ) {
			$this->handle_container( $combination );
		}
	}

	/**
	 * Handles a specific container-locations combination.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $combination The processable combination.
	 */
	protected function handle_container( $combination ) {
		$container = $combination[ 'container' ];
		$locations = $combination[ 'locations' ];
		$priority  = $default_priority = 'default';

		# Go through each combination and extract contexts and priorities.
		foreach( $locations as $location ) {
			if( ( $location_priority = $location->get_priority() ) != $default_priority ) {
				$priority = $location_priority;
			}
		}

		# Create a custom callback that will include the conainer.
		$callback = new Callback( array( $this, 'display' ) );
		$callback[ 'container' ] = $container;
		$callback[ 'locations' ] = $locations;

		# Add the meta box
		add_meta_box(
			$container->get_id(),
			$container->get_title(),
			$callback->get_callback(),
			'comment',
			'normal',
			$priority
		);
	}

	/**
	 * Displays a container.
	 *
	 * @since 3.0
	 *
	 * @param Callback   $callback The callback that is being called.
	 * @param WP_Comment $comment  The comment that is being displayed.
	 */
	public function display( $callback, $comment ) {
		$container = $callback[ 'container' ];

		$datastore = new Datastore;
		$datastore->set_id( $comment->comment_ID );
		$container->set_datastore( $datastore );

		$json = array(
			'type'      => 'Comment',
			'settings'  => $container->export_settings(),
			'data'      => $container->export_data()
		);

		$json[ 'settings' ][ 'locations' ] = array();
		foreach( $callback[ 'locations' ] as $location ) {
			$json[ 'settings' ][ 'locations' ][] = $location->export_settings();
		}

        echo sprintf(
            '<div class="uf-container" data-type="%s">
				<script type="text/json">%s</script>
				' . $this->get_no_js_message() . '
				<span class="spinner hide-if-no-js"></span>
			</div>',
            'Comment',
            json_encode( $json )
        );

		if( 'seamless' == $json[ 'settings' ][ 'style' ] ) {
			$this->unbox();
		}

		# Register the neccessary templates
		Template::add( 'comment', 'container/comment' );
		Template::add( 'container-error', 'container/error' );
	}

	/**
	 * Enqueues scripts when needed.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# Post meta containers don't work in the front-end
		if( ! is_admin() || ! function_exists( 'get_current_screen' ) || 'comment' != get_current_screen()->id ) {
			return false;
		}

		# Ensure unique IDs first
		$this->ensure_unique_field_names();

		wp_enqueue_script( 'uf-container-comment' );

		# Enqueue individual scripts
		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();

		return false;
	}

	/**
	 * Handles post saving.
	 *
	 * @since 3.0
	 *
	 * @param int $comment_id The ID of the comment that is being currently created/saved.
	 */
	public function save_comment( $comment_id ) {
		$comment = get_comment( $comment_id );

		# All values will be saved in the same datastore
		$datastore = new Datastore;
		$datastore->set_id( $comment_id );

		# Ensure unique field names
		$this->ensure_unique_field_names();

		# This will hold all messages
		$errors = array();

		# Try saving each of the containers in the queue
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			# Set the datastore to the container and fields
			$container->set_datastore( $datastore );

			# Prepare the data for the container
			$data_key = 'uf_comment_meta_' . $container->get_id();
			$data = isset( $_POST[ $data_key ] )
				? json_decode( stripslashes( $_POST[ $data_key ] ), true )
				: array();

			# Check if errors should be reported
			$messages = $container->save( $data );
			$report   = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with( $comment ) ) {
					$report = true;
					break;
				}
			}

			if( $report ) {
				$errors = array_merge( $errors, $messages );
			}
		}

		# Save or die
		if( empty( $errors ) ) {
			$datastore->commit();
		} else {
			$this->error( $errors );
		}
	}

	/**
	 * Registers a location with the REST API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Location  $location  The location that is being linked.
	 * @param  Ultimate_Fields\Container $container The container the location belongs to.
	 * @param  mixed[]       $fields    The fields that should be exposed.
	 */
	protected function register_rest_location( $location, $container, $fields ) {
		$this->add_fields_to_endpoint( 'comment', $fields, $container );
	}

	/**
	 * Reads out a value from the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is needed.
	 * @param  mixed     $item  The API item (comment).
	 * @return mixed
	 */
	protected function get_api_value( $field, $item ) {
		return Data_API::instance()->get_value(
			$field->get_name(),
			'comment_' . $item[ 'id' ]
		);
	}

	/**
	 * Updates a REST value through the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field  $field The field whose value is being saved.
	 * @param  mixed      $value The value to save.
	 * @param  WP_Comment $item  The item that the value should be associated with.
	 * @return bool
	 */
	protected function save_api_value( $field, $value, $item ) {
		return Data_API::instance()->update_value(
			$field->get_name(),
			$value,
			'comment_' . $item->comment_ID
		);
	}

 	/**
 	 * Performs AJAX for comments items.
 	 *
 	 * @since 3.0
 	 */
 	public function do_ajax() {
 		ultimate_fields()->ajax( Comment_Location::WORKS_WITH_KEYWORD );
 	}
}
